
import { format } from "date-fns";
import React, { useCallback, useEffect, useRef, useState } from "react";

/**  Services */
import { useAppContext } from "../../../../../hooks/context";
import type { FlatOffer, EmploymentOfferStatus } from "../../../../../features/employment/offer";
import EmploymentOffersQueries from "../../../../../api/services/employment/queries";

//-- Custom Components
import { CreateOfferForm } from "../../../../components/createOfferForm/create.offer.form";
import { OfferDetailModal } from "../../offer/offer.details.modal";
import { EyeIcon } from "../../../../../layout/components/icons/eye.icon";

//-- Employment Offers Services
import EmploymentOffersServices from "../../../../../api/services/employment/command";

//-- Styles
import styles from "./EmploymentOffersSection.module.css";


interface OffersSectionProps{
    jobId?: string
}


const LIMIT = 17;


export default function EmploymentOffersSection({
    jobId
}: OffersSectionProps) {
    const { setModal } = useAppContext();

    //-- Offers
    const [employmentOffers, setEmploymentOffers] = useState<FlatOffer[]>([]);
    const [isUpdating, setIsUpdating] = useState<string | null>(null);

    //-- Pagination
    const [skip, setSkip] = useState<number>(0);
    const [hasMore, setHasMore] = useState<boolean>(true);
    const [isLoadingMore, setIsLoadingMore] = useState<boolean>(false);


    const fetchOffers = useCallback(async (currentSkip: number) => {
        // Secure calls
        if (isLoadingMore || (!hasMore && currentSkip !== 0)) return;

        setIsLoadingMore(true);
        try {
            const rawData = await EmploymentOffersQueries.getUserEmploymentOffer({
                jobId, 
                skip: currentSkip, 
                limit: LIMIT 
            });

            // Stop pagination if back-end returns fewer items than requested LIMIT
            if (rawData.length < LIMIT) {
                setHasMore(false);
            }

            const mappedOffers: FlatOffer[] = rawData.map((data: any) => ({
                id: data.id,
                candidate: `${data.candidate.firstName} ${data.candidate.lastName}`,
                email: data.candidate.email,
                jobTitle: data.jobOffer.title,
                salary: data.salary,
                expiresAt: format(new Date(data.expiredAt), 'dd MMMM yyyy'),
                status: data.status,
                avatarUrl: data.candidate.image?.name // optional avatar path
            }));

            setEmploymentOffers(prev => (currentSkip === 0 ? mappedOffers : [...prev, ...mappedOffers]));
        }
        catch (error) {
            console.error("Failed to load offers", error);
        }
        finally {
            setIsLoadingMore(false);
        }
    }, [jobId, hasMore, isLoadingMore]);


    //-- Intializing data (Fetching first batch)
    useEffect(() => {
        setSkip(0);
        setHasMore(true);
        fetchOffers(0);
    }, [jobId]);


    // Function triggered when the user scrolls to the bottom sentinel element
    const handleLoadMore = () => {
        if (hasMore && !isLoadingMore) {
            const nextSkip = skip + LIMIT;
            setSkip(nextSkip);
            fetchOffers(nextSkip);
        }
    };

    // Handle Delete/Cancel offer
    const handleDelete = async (id: string) => {
        setIsUpdating(id);
        try {
            await EmploymentOffersServices.deleteOffer(id);
            setEmploymentOffers((prev) => prev.filter((item) => item.id !== id));
        }
        catch (error) {
            console.error("Erreur lors de la suppression de l'offre", error);
        }
        finally {
            setIsUpdating(null);
        }
    };

    const handleCancel = useCallback(async (id: string) => {
        try {
            await EmploymentOffersServices.cancelOffer(id);

            setEmploymentOffers(currentOffers =>
                currentOffers.map(offer =>
                    offer.id === id
                        ? { ...offer, status: 'CANCELLED' }
                        : offer
                )
            );
        }
        catch (error) {
            console.warn("Something went wrong while cancelling the offer");
        }
    }, []);


    //-- Open Modal for creating offer
    const handleOpenCreateModal = () => {
        console.log("Open modal")
        setModal({
            isOpen: true,
            title: "Créer une offre d'embauche",
            content: (
                <CreateOfferForm
                    onSubmit={async (payload) => {
                        const data = await EmploymentOffersServices.create(payload);
                        setEmploymentOffers((prevOffers) => [
                            {
                                id: data.id,
                                email: payload.candiate.email,
                                status: data.status, // Assure-toi que status est bien présent dans EmploymentSaved si nécessaire
                                candidate: `${payload.candiate.firstName} ${payload.candiate.lastName}`,
                                salary: payload.salary,
                                avatarUrl: payload.avatarUrl,
                                createdAt: data.createdAt,
                                jobTitle: payload.jobTitle,
                                expiresAt: payload.expiredAt,
                            },
                            ...prevOffers,
                        ]);
                    }}
                />
            ),
        });
    };


    //-- Open offer details
    const handleConsult = (offer: FlatOffer, onCloseCallback?: () => void) => {
        const handleClose = () => {
            setModal(null);
            if (onCloseCallback) {
                onCloseCallback(); // Réinitialise l'œil dans le tableau
            }
        };

        setModal({
            isOpen: true,
            title: `Détails de l'offre - ${offer.candidate}`,
            content: (
                <OfferDetailModal
                    offer={offer} 
                    onClose={handleClose} 
                />
            ),
            onClose: handleClose
        });
    };


    return (
        <div className={styles.tableCard}>
            <div className={styles.tableHeader}>
                <div className={styles.headerTitleGroup}>
                    <h2>Propositions d'embauche (Offres)</h2>
                    <span className={styles.badgeCount}>{employmentOffers.length} au total</span>
                </div>
                <button onClick={handleOpenCreateModal} className={styles.btnPrimary}>
                    + Générer une offre
                </button>
            </div>

            <OfferTable  
                employmentOffers={employmentOffers}
                onDelete={handleDelete}
                onCancel={handleCancel}
                onView={handleConsult}
                isUpdating={isUpdating}
                onLoadMore={handleLoadMore}
                hasMore={hasMore}
                isLoadingMore={isLoadingMore}
            />
        </div>
    );
}


//---------------------------
//--- TABLES
//----------------------------



interface OffersTableProps {
    employmentOffers: FlatOffer[];
    onDelete: (offerId: string) => void;
    onCancel: (offerId: string) => void;
    onView: (offer: FlatOffer, onCloseCallback?: () => void) => void;
    isUpdating?: string | null; // optionnal if loading loading state is manage (by lines)
    onLoadMore: ()=>void;
    hasMore: boolean;
    isLoadingMore: boolean;
}


const OfferTable: React.FC<OffersTableProps> = ({
    employmentOffers,
    onDelete,
    onCancel,
    onView,
    isUpdating,

    onLoadMore,
    hasMore,
    isLoadingMore
}) => {
    const { setModal } = useAppContext();
    const [activeOfferId, setActiveOfferId] = useState<string | null>(null);

    //-- Handle Eye icon
    const handleToggleEye = (offer: FlatOffer) => { 
        if (activeOfferId === offer.id) {
            //-- Open
            setModal(null);
            setActiveOfferId(null);
        }
        else {
            //-- Close
            setActiveOfferId(offer.id);
            onView(offer, () => {
                setActiveOfferId(null);
            });
        }
    };
    

    const observerTarget = useRef<HTMLTableRowElement | null>(null); //determines wether the scorl is at the bottom

    useEffect(() => {
        const target = observerTarget.current;
        if (!target) return;

        const observer = new IntersectionObserver(
            (entries) => {
                // Trigger load-more when sentinel becomes visible
                if (entries[0].isIntersecting && hasMore && !isLoadingMore) {
                    onLoadMore();
                }
            },
            { threshold: 0.1 }
        );

        observer.observe(target);

        return () => {
            if (target) observer.unobserve(target);
        };
    }, [onLoadMore, hasMore, isLoadingMore]);



    //-- Construct intials
    const getInitials = (name: string) => {
        if (!name) return "??";
        return name
            .split(" ")
            .map((n) => n[0])
            .join("")
            .toUpperCase()
            .slice(0, 2);
    };

  
    const formatSalary = (salary: number | string) => {
        if (!salary) return "—";
        return new Intl.NumberFormat("fr-FR", {
            style: "currency",
            currency: "EUR",
            maximumFractionDigits: 0,
        }).format(Number(salary));
    };


    //-- Dynamic static badge color
    const renderStatusBadge = (status: EmploymentOfferStatus) => {
        const statusConfig: Record<EmploymentOfferStatus, { label: string; className: string }> = {
            DRAFT: { label: "Brouillon", className: styles.statusDraft },
            SENT: { label: "En attente", className: styles.statusSent },
            ACCEPTED: { label: "Acceptée", className: styles.statusAccepted },
            DECLINED: { label: "Refusée", className: styles.statusDeclined },
            EXPIRED: { label: "Expirée", className: styles.statusExpired },
            CANCELLED: { label: "Annulée", className: styles.statusCancelled },
        };

        const config = statusConfig[status] || { label: status, className: "" };

        return <span className={`${styles.badge} ${config.className}`}>{config.label}</span>;
    };



    return (
        <div className={styles.tableContainer}>
            <table className={styles.offersTable}>
                <thead>
                    <tr>
                        <th>Candidat</th>
                        <th>Poste</th>
                        <th>Rémunération</th>
                        <th>Date d'envoi</th>
                        <th>Expiration</th>
                        <th>Statut</th>
                        <th className={styles.textRight}>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    {employmentOffers.length === 0 ? (
                        <tr>
                            <td colSpan={7} className={styles.emptyState}>
                                Aucune offre enregistrée.
                            </td>
                        </tr>
                    ) : (
                        employmentOffers.map((offer) => (
                            <tr
                                key={offer.id}
                                className={isUpdating === offer.id ? styles.rowDisabled : ""}
                            >
                                {/* Candidat */}
                                <td data-label="Candidat">
                                    <div className={styles.candidateCell}>
                                        {offer.avatarUrl ? (
                                            <img
                                                src={offer.avatarUrl}
                                                alt={offer.candidate}
                                                className={styles.avatar}
                                            />
                                        ) : (
                                            <div className={styles.avatarFallback}>
                                                {getInitials(offer.candidate)}
                                            </div>
                                        )}
                                        <div>
                                            <span className={styles.candidateName}>
                                                {offer.candidate}
                                            </span>
                                            <span className={styles.emailText}>{offer.email}</span>
                                        </div>
                                    </div>
                                </td>

                                {/* Poste */}
                                <td data-label="Poste">
                                    <span className={styles.jobTitle}>{offer.jobTitle}</span>
                                </td>

                                {/* Salary */}
                                <td data-label="Rémunération">
                                    <span className={styles.salaryText}>
                                        {offer.salary ? formatSalary(offer.salary) : 'None'}/an
                                    </span>
                                </td>

                                {/* Sent date */}
                                <td data-label="Date d'envoi">
                                    {offer.createdAt
                                        ? new Date(offer.createdAt).toLocaleDateString("fr-FR", {
                                              day: "numeric",
                                              month: "short",
                                              year: "numeric",
                                          })
                                        : "—"}
                                </td>

                                {/* Expiration */}
                                <td data-label="Expiration">
                                    {offer.expiresAt
                                        ? new Date(offer.expiresAt).toLocaleDateString("fr-FR", {
                                              day: "numeric",
                                              month: "short",
                                              year: "numeric",
                                          })
                                        : "—"}
                                </td>

                                {/* Statut : Display strict (Badge) */}
                                <td data-label="Statut">
                                    {renderStatusBadge(offer.status)}
                                </td>

                                {/* Conditional Actions  */}
                                    <td data-label="Actions" className={styles.actionsCell}>
                                    <div className={styles.actionGroup}>
                                        {/* “View” button changed to an interactive eye button */}
                                        <button
                                            type="button"
                                            onClick={() => handleToggleEye(offer)}
                                            className={styles.btnSecondary}
                                            title={activeOfferId ? "Masquer les détails" : "Voir les détails"}
                                        >
                                            <EyeIcon isOpen={!!activeOfferId} />
                                        </button>

                                        {/* Cancel & delete */}
                                        {offer.status === "SENT" && (
                                            <button
                                                type="button"
                                                onClick={() => onCancel(offer.id)}
                                                className={styles.btnWarning}
                                                title="Annuler l'offre envoyée"
                                                disabled={isUpdating === offer.id}
                                            >
                                                Annuler
                                            </button>
                                        )}

                                        {offer.status === "DRAFT" && (
                                            <button
                                                type="button"
                                                onClick={() => onDelete(offer.id)}
                                                className={styles.btnDanger}
                                                title="Supprimer le brouillon"
                                                disabled={isUpdating === offer.id}
                                            >
                                                Supprimer
                                            </button>
                                        )}
                                    </div>
                                </td>
                            </tr>
                        ))
                    )}
                    {
                        employmentOffers.length > 0 && hasMore && (
                            <tr ref={observerTarget} className={styles.loadingRow}>
                                <td
                                    colSpan={7}
                                    className={styles.textCenter}
                                >
                                    {isLoadingMore ? "Chargement des offres..." : ""}
                                </td>
                            </tr>
                        )
                    }
                </tbody>
            </table>
        </div>
    );
};
