
import { format } from "date-fns";
import React, { useCallback, useEffect, useRef, useState } from "react";

/**  Services */
import { useAppContext } from "../../../../../hooks/context";
import type { OfferStatus } from "../../../../../features/offer/offer";
import OffersQueries from "../../../../../api/services/offer/queries";

//-- Custom Components
import { CreateOfferForm } from "../../../../components/createOfferForm/create.offer.form";

//-- Offers Services
import OffersServices from "../../../../../api/services/offer/command";

//-- Styles
import styles from "./OffersSection.module.css";




export interface Offer {
    id: string;
    candidate: string;
    email: string;
    jobTitle: string;
    salary: number; // Ex: 45000 (en €/an)
    sentAt?: string;
    expiresAt?: string;
    status: OfferStatus;
    avatarUrl?: string;
}



interface OffersSectionProps{
    jobId: string
}

const LIMIT = 17;

export default function OffersSection({
    jobId
}: OffersSectionProps) {
    const { setModal } = useAppContext();

    //-- Offers
    const [offers, setOffers] = useState<Offer[]>([]);
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
            const rawData = await OffersQueries.getUserOfferForThisJob(jobId, { 
                skip: currentSkip, 
                limit: LIMIT 
            });

            // Stop pagination if back-end returns fewer items than requested LIMIT
            if (rawData.length < LIMIT) {
                setHasMore(false);
            }

            const mappedOffers: Offer[] = rawData.map((data: any) => ({
                id: data.id,
                candidate: `${data.candidate.firstName} ${data.candidate.lastName}`,
                email: data.candidate.email,
                jobTitle: data.jobOffer.title,
                salary: data.salary,
                expiresAt: format(new Date(data.expiredAt), 'dd MMMM yyyy'),
                status: data.status,
                avatarUrl: data.candidate.image?.name // optional avatar path
            }));

            setOffers(prev => (currentSkip === 0 ? mappedOffers : [...prev, ...mappedOffers]));
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


    //-- Handle Status
    const handleStatusChange = async (id: string, newStatus: OfferStatus) => {
        setIsUpdating(id);
        try {
            // TODO: API call -> await api.updateOfferStatus(id, newStatus);
            setOffers((prev) =>
                prev.map((item) => (item.id === id ? { ...item, status: newStatus } : item))
            );
        }
        catch (error) {
            console.error("Erreur lors de la mise à jour de l'offre", error);
        }
        finally {
            setIsUpdating(null);
        }
    };


    // Handle Delete/Cancel offer
    const handleDelete = async (id: string) => {
        setIsUpdating(id);
        try {
            await OffersServices.deleteOffer(id);
            setOffers((prev) => prev.filter((item) => item.id !== id));
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
            await OffersServices.cancelOffer(id);

            setOffers(currentOffers =>
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
                    applications={[]}
                    onSubmit={async (payload) => {
                        await OffersServices.create(payload);
                    }}
                />
            ),
        });
    };


    const handleConsult = useCallback((offer: Offer)=>{
        try{

        }
        catch(error){

        }
    },[])




    return (
        <div className={styles.tableCard}>
            
            <div className={styles.tableHeader}>
                <div className={styles.headerTitleGroup}>
                    <h2>Propositions d'embauche (Offres)</h2>
                    <span className={styles.badgeCount}>{offers.length} au total</span>
                </div>
                <button onClick={handleOpenCreateModal} className={styles.btnPrimary}>
                    + Générer une offre
                </button>
            </div>

            <OfferTable  
                offers={offers}
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





interface OffersTableProps {
    offers: Offer[];
    onDelete: (offerId: string) => void;
    onCancel: (offerId: string) => void;
    onView: (offer: Offer) => void;
    isUpdating?: string | null; // optionnal if loading loading state is manage (by lines)
    onLoadMore: ()=>void;
    hasMore: boolean;
    isLoadingMore: boolean;
}


const OfferTable: React.FC<OffersTableProps> = ({
    offers,
    onDelete,
    onCancel,
    onView,
    isUpdating,

    onLoadMore,
    hasMore,
    isLoadingMore
}) => {
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
    const renderStatusBadge = (status: OfferStatus) => {
        const statusConfig: Record<OfferStatus, { label: string; className: string }> = {
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
                    {offers.length === 0 ? (
                        <tr>
                            <td colSpan={7} className={styles.emptyState}>
                                Aucune offre enregistrée.
                            </td>
                        </tr>
                    ) : (
                        offers.map((offer) => (
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

                                {/* Salaire */}
                                <td data-label="Rémunération">
                                    <span className={styles.salaryText}>
                                        {formatSalary(offer.salary)}/an
                                    </span>
                                </td>

                                {/* Date d'envoi */}
                                <td data-label="Date d'envoi">
                                    {offer.sentAt
                                        ? new Date(offer.sentAt).toLocaleDateString("fr-FR", {
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

                                {/* Statut : Affichage strict (Badge) */}
                                <td data-label="Statut">
                                    {renderStatusBadge(offer.status)}
                                </td>

                                {/* Actions conditionnelles */}
                                <td data-label="Actions" className={styles.actionsCell}>
                                    <div className={styles.actionGroup}>
                                        {/* 1. Bouton "Voir / Consulter" (Toujours visible) */}
                                        <button
                                            type="button"
                                            onClick={() => onView(offer)}
                                            className={styles.btnSecondary}
                                            title="Voir les détails"
                                        >
                                            Voir
                                        </button>

                                        {/* 2. Bouton "Annuler" (Seulement si l'offre a été envoyée et attend une réponse) */}
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

                                        {/* 3. Bouton "Supprimer" (Seulement si l'offre est un BROUILLON) */}
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
                        offers.length > 0 && hasMore && (
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
