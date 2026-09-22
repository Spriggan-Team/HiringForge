
import { format } from "date-fns";
import { useTranslation } from "react-i18next";
import React, { useCallback, useEffect, useRef, useState } from "react";

/**  Services */
import { useAppContext } from "../../../../../hooks/context";
import { type FlatOffer, type RecruiterEmploymentOffer, EmploymentOfferStatus } from "../../../../../features/employment/offer";
import EmploymentOffersQueries from "../../../../../api/services/employment/queries";
import { formatDateTimeSafely, getInitials } from "../../../../../utils/format";
import ApplicationQueries from "../../../../../api/services/application/queries";

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
    const {t} = useTranslation();
    const { setModal, setLoading, setPopup } = useAppContext();

    //-- Offers
    const [employmentOffers, setEmploymentOffers] = useState<FlatOffer[]>([]);
    const [isUpdating, setIsUpdating] = useState<string | null>(null);

    //-- Pagination
    const [skip, setSkip] = useState<number>(0);
    const [hasMore, setHasMore] = useState<boolean>(true);
    const [isLoadingMore, setIsLoadingMore] = useState<boolean>(false);

    //- Lock
    const isLoadingRef = useRef(false);
    const imageURLsCache = useRef<Record<string, string>>({}); //employment : id => url-image

    const fetchEmploymentOffers = useCallback(async (currentSkip: number) => {
        // Secure calls
        if (isLoadingRef.current || (!hasMore && currentSkip !== 0)) return;

        isLoadingRef.current = true;
        setIsLoadingMore(true);

        try {
            const rawData = await EmploymentOffersQueries.getUserEmploymentOffer({
                jobId,
                skip: currentSkip,
                limit: LIMIT,
            });

            if (rawData.length < LIMIT) {
                setHasMore(false);
            }

            const offersPromises = rawData.map(async (data: RecruiterEmploymentOffer): Promise<FlatOffer> => {
                let avatarUrl: string | null = null;
                const cache = imageURLsCache.current;

                if (cache[data.id]) {
                    avatarUrl = cache[data.id];
                }
                else if (data.candidate.image?.name) {
                    // Load image if it really exist
                    try {
                        const blob = await ApplicationQueries.getCandidateProfilImage({
                            candidateId: data.candidate.id,
                            applicationId: data.application.id
                        });
                        const url = URL.createObjectURL(blob);
                        cache[data.id] = url;
                        avatarUrl = url;
                    }
                    catch (error) {
                        console.error(`Impossible de charger l'image pour ${data.candidate.id}`, error);
                    }
                }

                const payload: FlatOffer = {
                    id: data.id,
                    candidate: `${data.candidate.firstName} ${data.candidate.lastName}`,
                    email: data.candidate.email,
                    jobTitle: data.jobOffer.title,
                    salary: data.salary,
                    expiresAt: data.expiredAt.date,
                    createdAt: data.createdAt.date, 
                    status: data.status,
                    avatarUrl: avatarUrl,
                    message: data.message,
                    scheduledEndDate: data.scheduledEndDate.date
                }

                return payload;
            });

            //-- Produce successfull result and error
            const mappedOffers = await Promise.all(offersPromises);
            setEmploymentOffers(prev => (currentSkip === 0 ? mappedOffers : [...prev, ...mappedOffers]));
        }  
        catch (error) {
            console.error("Failed to load offers", error);
        }
        finally {
            isLoadingRef.current = false;
            setIsLoadingMore(false);
        }
    }, [jobId]);



    
    //-- Intializing data (Fetching first batch)
    useEffect(() => {
        setSkip(0);
        setHasMore(true);
        fetchEmploymentOffers(0);
    }, [jobId]);


    // Function triggered when the user scrolls to the bottom sentinel element
    const handleLoadMore = () => {
        if (hasMore && !isLoadingMore) {
            const nextSkip = skip + LIMIT;
            setSkip(nextSkip);
            fetchEmploymentOffers(nextSkip);
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
        setModal({
            isOpen: true,
            title: "Créer une offre d'embauche",
            content: (
                <CreateOfferForm
                    onSubmit={async (payload) => {
                        setLoading({ state: true, subtitle: t("employmentOffer.messages.loading.employmentOfferGeneration") });
                        try{
                            const data = await EmploymentOffersServices.create(payload);
                            setEmploymentOffers((prevOffers) => {
                                const name = `${payload.candiate.firstName} ${payload.candiate.lastName}`;
                                return ([
                                    {
                                        id: data.id,
                                        email: payload.candiate.email,
                                        status: data.status, // status
                                        candidate: name,
                                        salary: payload.salary ? payload.salary : undefined,
                                        avatarUrl: payload.avatarUrl,
                                        jobTitle: payload.jobTitle,
                                        message: payload.message,
                                        expiresAt: payload.expiredAt,
                                        createdAt: data.createdAt,
                                        scheduledEndDate: payload.scheduledEndDate
                                    },
                                    ...prevOffers,
                                ]);
                            });
                            setLoading({ state: false })
                            setPopup({
                                status: 'success',
                                message:  t("employmentOffer.apiResponse.employmentGenerationSucceed")
                            })
                        }
                        catch(error){
                            console.log("Something went wrong while saving employment offer", error)
                            setLoading({state: false})
                            setPopup({
                                status: 'error',
                                message: t("employmentOffer.apiResponse.employmentGenerationFailed")
                            })
                        }
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
                onCloseCallback(); // reset eye
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

    //-- Cleaning up
    useEffect(()=>{
        return ()=>{
            Object.values(imageURLsCache.current).forEach((url)=>{
                URL.revokeObjectURL(url);
            })
        }
    },[])

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
        if (!target) 
            return;

        const observer = new IntersectionObserver(
            (entries) => {
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
    }, [hasMore, isLoadingMore]);


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
                        employmentOffers.map((offer) => {
                            const disableDeletion = new Date() > new Date(offer.expiresAt) || offer.status === EmploymentOfferStatus.DECLINED;
                            return (
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
                                            {offer.salary ? formatSalary(offer.salary) : 'Aucune'}
                                        </span>
                                    </td>

                                    {/* Sent date */}
                                    <td data-label="Date d'envoi">
                                        {formatDateTimeSafely(offer.createdAt)}
                                    </td>

                                    {/* Expiration */}
                                    <td data-label="Expiration">
                                        {formatDateTimeSafely(offer.expiresAt)}
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
                                            {(offer.status === "SENT") && (
                                                <button
                                                    type="button"
                                                    onClick={() => onCancel(offer.id)}
                                                    className={styles.btnDeactivate}
                                                    title="Annuler l'offre envoyée"
                                                    disabled={isUpdating === offer.id || disableDeletion}
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
                            )
                        })
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
