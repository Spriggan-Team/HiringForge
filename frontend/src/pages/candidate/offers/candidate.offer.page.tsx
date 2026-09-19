import React, {
    useCallback,
    useEffect,
    useMemo,
    useRef,
    useState,
} from "react";

import { formatDateSafely } from "../../../utils/format";
import EmploymentOffersQueries from "../../../api/services/employment/queries";
import EmploymentOffersServices from "../../../api/services/employment/command";
import type { CandidateEmploymentOffer, EmploymentOfferStats, OfferFilter } from "../../../features/employment/offer";

import { Pagination } from "../../../layout/components/navigation/pagination/pagination";

import styles from "./CandidateOfferPage.module.css";


// --------------------------------------------------
// Types
// --------------------------------------------------

type CandidateOfferPageProps = {};
type OfferListCache = Map<string, CandidateEmploymentOffer[]>;

type ConfirmAction =
    | "accept"
    | "refuse"
    | null;


// --------------------------------------------------
// Helpers
// --------------------------------------------------

const PAGE_LIMIT = 15;


// --------------------------------------------------
// Component
// --------------------------------------------------

const CandidateOfferPage: React.FC<CandidateOfferPageProps> = () => {
    // --------------------------------------------------
    // Pagination
    // --------------------------------------------------

    const [skip, setSkip] = useState(0);
    const [limit] = useState(PAGE_LIMIT);

    // --------------------------------------------------
    // Filters
    // --------------------------------------------------

    const [currentFilter, setCurrentFilter] =  useState<OfferFilter>("ALL");

    // --------------------------------------------------
    // Data
    // --------------------------------------------------

    const [employmentStats, setEmploymentStats] = useState<EmploymentOfferStats>({
        awaiting: 0,
        accepted: 0,
        completed: 0,
        rejected: 0,
    });

    const [currentTotal, setCurrentTotal] = useState(0);
    const [myEmploymentOffers, setMyEmploymentOffers] =  useState<CandidateEmploymentOffer[]>([]);
    const [currentEmploymentDetails, setCurrentEmploymentDetails] = useState<CandidateEmploymentOffer | null>(null);

    // --------------------------------------------------
    // UI state
    // --------------------------------------------------

    const [loading, setLoading] = useState(false);
    const [actionLoading, setActionLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const [confirmAction, setConfirmAction] = useState<ConfirmAction>(null);

    const [rejectionReason, setRejectionReason] = useState("");

    // --------------------------------------------------
    // Cache
    // --------------------------------------------------

    const employmentOffersCache = useRef<OfferListCache>(new Map());

    const employmentDetailsCache = useRef<Map<string, CandidateEmploymentOffer>>(new Map());

    const statsCache = useRef<EmploymentOfferStats | null>(null);

    // --------------------------------------------------
    // Filter -> backend menu
    // --------------------------------------------------

    const backendMenu = useMemo(() => {
        switch (currentFilter) {
            case "AWAITING":
                return "PENDING";
            case "ACCEPTED":
                return "ACCEPTED";
            case "COMPLETED":
                return "COMPLETED";
            case "REJECTED":
                return "REJECTED";
            case "ALL":
            default:
                return "ALL";
        }
    }, [currentFilter]);

    // --------------------------------------------------
    // Fetch stats
    // --------------------------------------------------

    const fetchStats = useCallback(async () => {
        if (statsCache.current) {
            setEmploymentStats(statsCache.current);
            return;
        }

        const stats = await EmploymentOffersQueries.getCandidateEmploymentOfferStats();
        statsCache.current = stats;

        setEmploymentStats(stats);
    }, []);

    // --------------------------------------------------
    // Fetch offers
    // --------------------------------------------------

    const fetchOffers = useCallback(async () => {
        const cacheKey = JSON.stringify({
            skip,
            limit,
            menu: backendMenu,
        });

        const cached = employmentOffersCache.current.get(cacheKey);

        if (cached) {
            setMyEmploymentOffers(cached);
            setCurrentEmploymentDetails(cached[0] ?? null);
            return;
        }

        setLoading(true);
        setError(null);

        try {
            const response = await EmploymentOffersQueries.getCandidateEmploymentOffers({
                skip,
                limit,
                menu: backendMenu,
            });

            const offers = response?.data ?? response ?? [];

            employmentOffersCache.current.set(cacheKey,offers);
            setMyEmploymentOffers(offers);

            if (offers.length === 0) {
                setCurrentEmploymentDetails(null);
                setCurrentTotal(0);
                return;
            }

            setCurrentEmploymentDetails(offers[0]);

            // Si ton endpoint renvoie total :
            if (
                typeof response === "object" &&
                response !== null &&
                "total" in response
            ) {
                setCurrentTotal(Number(response.total ?? 0));
            }
        }
        catch (error) {
            console.warn("Something went wrong while loading employment offers",error);
            setError("Impossible de charger vos offres d'emploi.");
        }
        finally {
            setLoading(false);
        }
    }, [
        skip,
        limit,
        backendMenu,
    ]);

    // --------------------------------------------------
    // Initial / pagination / filter loading
    // --------------------------------------------------

    useEffect(() => {
        fetchOffers();
    }, [fetchOffers]);

    useEffect(() => {
        fetchStats();
    }, [fetchStats]);

    // --------------------------------------------------
    // Select offer
    // --------------------------------------------------

    const handleSelectOffer = useCallback(
        async (offer: CandidateEmploymentOffer) => {
            const cached = employmentDetailsCache.current.get(offer.id);

            if (cached) {
                setCurrentEmploymentDetails(cached);
                return;
            }

            employmentDetailsCache.current.set(
                offer.id,
                offer
            );
            setCurrentEmploymentDetails(offer);
        },[]
    );

    // --------------------------------------------------
    // Filter
    // --------------------------------------------------

    const handleFilterChange = useCallback(
        (filter: OfferFilter) => {
            setCurrentFilter(filter);
            setSkip(0);
            setCurrentEmploymentDetails(null);
        },
        []
    );

    // --------------------------------------------------
    // Accept
    // --------------------------------------------------

    const handleAccept = useCallback(async () => {
        if (!currentEmploymentDetails?.id) {
            return;
        }

        setActionLoading(true);
        setError(null);

        try {
            await EmploymentOffersServices.handleAcceptEmploymentOffer({  employmentId: currentEmploymentDetails.id, });

            /*
             *  Invalid cache as offer has change status
             */
            employmentOffersCache.current.clear();
            employmentDetailsCache.current.delete(currentEmploymentDetails.id);

            statsCache.current = null;
            setConfirmAction(null);

            await Promise.all([
                fetchOffers(),
                fetchStats(),
            ]);
        }
        catch (error) {
            console.warn("Something went wrong while accepting employment offer", error);
            setError("Impossible d'accepter cette offre.");
        }
        finally {
            setActionLoading(false);
        }
    }, [
        currentEmploymentDetails,
        fetchOffers,
        fetchStats,
    ]);

    // --------------------------------------------------
    // Refuse
    // --------------------------------------------------

    const handleRefusal = useCallback(async () => {
        if (!currentEmploymentDetails?.id) {
            return;
        }
        const reason = rejectionReason.trim();

        if (!reason) {
            setError(
                "Veuillez indiquer un motif de refus."
            );
            return;
        }

        setActionLoading(true);
        setError(null);

        try {
            await EmploymentOffersServices.handleRefuseEmploymentOffer({
                employmentId: currentEmploymentDetails.id,
                rejectionReason: reason,
            });

            employmentOffersCache.current.clear();
            employmentDetailsCache.current.delete(currentEmploymentDetails.id);
            statsCache.current = null;

            setRejectionReason("");
            setConfirmAction(null);

            await Promise.all([
                fetchOffers(),
                fetchStats(),
            ]);
        }
        catch (error) {
            console.warn( "Something went wrong while refusing employment offer",error);
            setError("Impossible de refuser cette offre.");
        }
        finally {
            setActionLoading(false);
        }
    }, [
        currentEmploymentDetails,
        rejectionReason,
        fetchOffers,
        fetchStats,
    ]);

    // --------------------------------------------------
    // Pagination
    // --------------------------------------------------

    const canGoPrevious = skip > 0;
    const canGoNext = skip + limit < currentTotal;

    const handlePreviousPage = () => {
        if (!canGoPrevious) {
            return;
        }

        setSkip(
            Math.max(0, skip - limit)
        );
    };

    const handleNextPage = () => {
        if (!canGoNext) {
            return;
        }

        setSkip(skip + limit);
    };

    // --------------------------------------------------
    // Render
    // --------------------------------------------------

    return (
        <div className={styles.container}>
            {/* ------------------------------------------------ */}
            {/* Header */}
            {/* ------------------------------------------------ */}
            <header className={styles.header}>
                <div>
                    <span className={styles.eyebrow}>
                        Candidatures
                    </span>
                    <h1>
                        Mes offres d'emploi
                    </h1>
                    <p>
                        Retrouvez les propositions reçues
                        et gérez vos offres en cours.
                    </p>
                </div>
            </header>


            {/* ------------------------------------------------ */}
            {/* Stats */}
            {/* ------------------------------------------------ */}
            <section className={styles.statsGrid}>
                <button
                    type="button"
                    className={styles.statCard}
                    onClick={() =>
                        handleFilterChange("AWAITING")
                    }
                >
                    <span className={styles.statValue}>
                        {employmentStats.awaiting}
                    </span>

                    <span className={styles.statLabel}>
                        En attente
                    </span>
                </button>

                <button
                    type="button"
                    className={styles.statCard}
                    onClick={() =>
                        handleFilterChange("ACCEPTED")
                    }
                >
                    <span className={styles.statValue}>
                        {employmentStats.accepted}
                    </span>
                    <span className={styles.statLabel}>
                        Acceptées
                    </span>
                </button>

                <button
                    type="button"
                    className={styles.statCard}
                    onClick={() =>
                        handleFilterChange("COMPLETED")
                    }
                >
                    <span className={styles.statValue}>
                        {employmentStats.completed}
                    </span>

                    <span className={styles.statLabel}>
                        Terminées
                    </span>
                </button>

                <button
                    type="button"
                    className={styles.statCard}
                    onClick={() =>
                        handleFilterChange("REJECTED")
                    }
                >
                    <span className={styles.statValue}>
                        {employmentStats.rejected}
                    </span>

                    <span className={styles.statLabel}>
                        Refusées
                    </span>
                </button>

            </section>


            {/* ------------------------------------------------ */}
            {/* Filters */}
            {/* ------------------------------------------------ */}
            <nav className={styles.filters}>
                {(
                    [
                        ["ALL", "Toutes"],
                        ["AWAITING", "En attente"],
                        ["ACCEPTED", "Acceptées"],
                        ["COMPLETED", "Terminées"],
                        ["REJECTED", "Refusées"],
                    ] as const
                ).map(([value, label]) => (
                    <button
                        key={value}
                        type="button"
                        className={
                            currentFilter === value
                                ? styles.filterActive
                                : styles.filter
                        }
                        onClick={() =>handleFilterChange(value)}
                    >
                        {label}
                    </button>
                ))}

            </nav>


            {/* ------------------------------------------------ */}
            {/* Error */}
            {/* ------------------------------------------------ */}
            {error && (
                <div className={styles.error}>
                    {error}
                </div>
            )}


            {/* ------------------------------------------------ */}
            {/* Content */}
            {/* ------------------------------------------------ */}

            <div className={styles.content}>
                {/* ------------------------------------------------ */}
                {/* Offers list */}
                {/* ------------------------------------------------ */}
                <section className={styles.listSection}>
                    <div className={styles.sectionHeader}>
                        <div>
                            <h2>
                                Vos offres
                            </h2>
                            <span>
                                {currentTotal} offre
                                {currentTotal > 1 ? "s" : ""}
                            </span>
                        </div>
                    </div>


                    {loading && (
                        <div className={styles.loading}>
                            Chargement des offres...
                        </div>
                    )}

                    {!loading &&
                        myEmploymentOffers.length === 0 && (
                            <div className={styles.empty}>
                                <div className={styles.emptyIcon}>
                                    ○
                                </div>
                                <h3>
                                    Aucune offre
                                </h3>
                                <p>
                                    Vous n'avez aucune offre
                                    correspondant à ce filtre.
                                </p>
                            </div>
                        )
                    }


                    <div className={styles.offerList}>
                        {myEmploymentOffers.map((offer) => {
                            const status = getStatusConfig(offer.status);
                            const selected =currentEmploymentDetails?.id == offer.id;
                            const awaiting = isAwaitingOffer(offer);

                            return (
                                <article
                                    key={offer.id}
                                    className={
                                        selected
                                            ? styles.offerCardSelected
                                            : styles.offerCard
                                    }
                                    onClick={() =>
                                        handleSelectOffer(offer)
                                    }
                                >
                                    <div className={styles.offerTop}>
                                        <div className={styles.company}>
                                            <div
                                                className={styles.logo}
                                            >
                                                {offer.company.logoUrl ? (
                                                    <img
                                                        alt=""
                                                        src={offer.company.logoUrl }
                                                    />
                                                ) : (
                                                    <span>
                                                        {offer.company.name.charAt(0).toUpperCase()}
                                                    </span>
                                                )}
                                            </div>

                                            <div>
                                                <strong>
                                                    { offer.company .name }
                                                </strong>
                                                <h3>
                                                    {offer.jobOffer.title}
                                                </h3>
                                            </div>
                                        </div>

                                        <span
                                            className={`${styles.status} ${styles[status.className]}`}
                                        >
                                            {status.label}
                                        </span>
                                    </div>

                                    <div className={styles.offerMeta}>
                                        <span>
                                            <b>€</b>
                                            {formatSalary(offer.salary)} {/** Annuel */}
                                        </span>
                                        <span>
                                            <b>▣</b>
                                            Jusqu'au{" "}
                                            {formatDateSafely(offer.scheduledEndDate)}
                                        </span>
                                        <span>
                                            Reçue le{" "}
                                            {formatDateSafely(offer.createdAt)}
                                        </span>

                                    </div>

                                    {awaiting && (
                                        <div className={ styles.expiration } >
                                            Réponse avant le{" "}
                                            <strong>
                                                {formatDateSafely(
                                                    offer.expiredAt
                                                )}
                                            </strong>
                                        </div>
                                    )}
                                </article>
                            );
                        })}
                    </div>

                    {/* Pagination */}
                    {currentTotal > limit && (
                        <div className={styles.pagination}>
                            <Pagination
                                currentPage={Math.floor(skip / limit) + 1}
                                totalPages={Math.ceil(currentTotal / limit)}
                                onChange={(page) => {
                                    setSkip((page - 1) * limit);
                                }}
                            />
                        </div>
                    )}
                </section>


                {/* ------------------------------------------------ */}
                {/* Details */}
                {/* ------------------------------------------------ */}
                <aside className={styles.details}>
                    {!currentEmploymentDetails ? (
                        <div className={styles.detailsEmpty}>
                            <span>
                                Sélectionnez une offre
                            </span>
                            <p>
                                Les détails de l'offre
                                apparaîtront ici.
                            </p>
                        </div>
                    ) : (
                        <div>
                            <div className={styles.detailsHeader}>
                                <div className={styles.detailsLogo}>
                                    {currentEmploymentDetails.company
                                        .logoUrl ? (
                                        <img
                                            src={
                                                currentEmploymentDetails
                                                    .company
                                                    .logoUrl
                                            }
                                            alt=""
                                        />
                                    ) : (
                                        <span>
                                            {currentEmploymentDetails
                                                .company.name
                                                .charAt(0)
                                                .toUpperCase()
                                            }
                                        </span>
                                    )}
                                </div>

                                <div>
                                    <span>
                                        {currentEmploymentDetails.company.name }
                                    </span>
                                    <h2>
                                        {currentEmploymentDetails.jobOffer.title}
                                    </h2>
                                </div>
                            </div>

                            <div className={styles.detailsStatus}>
                                <span
                                    className={`${styles.status} ${
                                        styles[
                                            getStatusConfig(
                                                currentEmploymentDetails
                                                    .status
                                            ).className
                                        ]
                                    }`}
                                >
                                    {getStatusConfig(currentEmploymentDetails.status).label  }
                                </span>
                            </div>

                            {/* Salary */}
                            <div className={styles.detailBlock}>
                                <span className={styles.detailLabel}>
                                    Rémunération
                                </span>
                                <strong className={styles.salary}>
                                    {formatSalary(currentEmploymentDetails.salary)}
                                    <small>
                                        / an
                                    </small>
                                </strong>
                            </div>

                            {/* Dates */}
                            <div className={styles.dateGrid}>
                                <div>
                                    <span>
                                        Offre reçue
                                    </span>
                                    <strong>
                                        {formatDateSafely(currentEmploymentDetails.createdAt)}
                                    </strong>
                                </div>

                                <div>
                                    <span>
                                        Date de fin prévue
                                    </span>
                                    <strong>
                                        {formatDateSafely(currentEmploymentDetails.scheduledEndDate)}
                                    </strong>
                                </div>

                                <div>
                                    <span> Date limite de réponse</span>
                                    <strong>{formatDateSafely(currentEmploymentDetails.expiredAt.date)}</strong>
                                </div>
                            </div>


                            {/* Message */}
                            {currentEmploymentDetails.message && (
                                <div className={styles.detailBlock}>
                                    <span
                                        className={styles.detailLabel}
                                    >
                                        Message de l'entreprise
                                    </span>

                                    <p className={styles.message}>
                                        {currentEmploymentDetails.message}
                                    </p>

                                </div>
                            )}

                            {/* Rejection */}
                            {currentEmploymentDetails
                                .rejectionReason && (
                                <div className={styles.rejectionBox}>
                                    <span>
                                        Motif du refus
                                    </span>

                                    <p>
                                        {currentEmploymentDetails.rejectionReason}
                                    </p>
                                </div>
                            )}

                            {/* Actions */}
                            {isAwaitingOffer(
                                currentEmploymentDetails
                            ) && (
                                <div
                                    className={styles.actions}
                                >
                                    <button
                                        type="button"
                                        className={styles.acceptButton}
                                        disabled={actionLoading}
                                        onClick={() =>
                                            setConfirmAction("accept")
                                        }
                                    >
                                        Accepter l'offre
                                    </button>

                                    <button
                                        type="button"
                                        className={ styles.refuseButton }
                                        disabled={actionLoading}
                                        onClick={() =>  setConfirmAction("refuse")}
                                    >
                                        Refuser
                                    </button>

                                </div>
                            )}
                        </div>
                    )}
                </aside>
            </div>


            {/* ------------------------------------------------ */}
            {/* Confirmation modal */}
            {/* ------------------------------------------------ */}
            {confirmAction && (
                <div
                    className={styles.modalOverlay}
                    onClick={() =>!actionLoading &&setConfirmAction(null) }
                >
                    <div
                        className={styles.modal}
                        onClick={(event) =>{
                            event.stopPropagation()
                        }}
                    >
                        {confirmAction === "accept" ? (
                            <>
                                <h2>
                                    Accepter cette offre ?
                                </h2>
                                <p>
                                    Vous êtes sur le point
                                    d'accepter cette proposition
                                    d'emploi.
                                </p>

                                <div
                                    className={styles.modalActions}
                                >
                                    <button
                                        type="button"
                                        onClick={() =>setConfirmAction(null) }
                                        disabled={ actionLoading }
                                    >
                                        Annuler
                                    </button>

                                    <button
                                        type="button"
                                        className={styles.acceptButton}
                                        onClick={handleAccept}
                                        disabled={actionLoading}
                                    >
                                        {actionLoading ? "Acceptation..." : "Confirmer"}
                                    </button>
                                </div>
                            </>
                        ) : (
                            <>
                                <h2>
                                    Refuser cette offre ?
                                </h2>
                                <p>
                                    Cette action est définitive.
                                    Indiquez pourquoi vous
                                    souhaitez refuser cette
                                    proposition.
                                </p>

                                <textarea
                                    value={rejectionReason}
                                    onChange={(event) =>setRejectionReason(event.target.value)}
                                    placeholder="Motif du refus..."
                                    className={styles.reasonInput}
                                    rows={4}
                                    disabled={actionLoading}
                                />

                                <div  className={styles.modalActions}>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setConfirmAction(null);
                                            setRejectionReason("");
                                        }}
                                        disabled={actionLoading}
                                    >
                                        Annuler
                                    </button>

                                    <button
                                        type="button"
                                        className={styles.refuseButton}
                                        onClick={handleRefusal}
                                        disabled={
                                            actionLoading ||
                                            !rejectionReason.trim()
                                        }
                                    >
                                        {actionLoading ? "Refus..." : "Confirmer le refus"}
                                    </button>
                                </div>
                            </>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
};

export default CandidateOfferPage;


/**---------------------
 * Helpers 
 * -------------------
 */
const getStatusConfig = (status: string) => {
    switch (status) {
        case "SENT":
        case "sent":
            return {
                label: "En attente",
                className: "awaiting",
            };

        case "ACCEPTED":
        case "accepted":
            return {
                label: "Acceptée",
                className: "accepted",
            };

        case "COMPLETED":
        case "completed":
            return {
                label: "Terminée",
                className: "completed",
            };

        case "DECLINED":
        case "declined":
        case "REJECTED":
        case "rejected":
            return {
                label: "Refusée",
                className: "rejected",
            };

        case "EXPIRED":
        case "expired":
            return {
                label: "Expirée",
                className: "expired",
            };

        default:
            return {
                label: String(status),
                className: "default",
            };
    }
};

const isAwaitingOffer = (
    offer: CandidateEmploymentOffer
): boolean => {
    return (
        offer.status === "SENT" ||
        (offer.status as any) === "sent" 
    );
};


const formatSalary = (salary?: number | null): string => {
    if (salary === null || salary === undefined) {
        return "Salaire non communiqué";
    }

    return new Intl.NumberFormat("fr-FR", {
        style: "currency",
        currency: "EUR",
        maximumFractionDigits: 0,
    }).format(salary);
};
