

import React, { useEffect, useState } from "react";
import styles from "./OffersSection.module.css";
import { CreateOfferForm } from "../../../../components/createOfferForm/create.offer.form";
import { useAppContext } from "../../../../../hooks/context";
import OffersServices from "../../../../../api/services/offer/command";

export type OfferStatus = "Draft" | "Sent" | "Accepted" | "Declined" | "Expired";

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

const mockOffers: Offer[] = [
    {
        id: "1",
        candidate: "Sarah Dupont",
        email: "sarah@email.com",
        jobTitle: "UX/UI Designer",
        salary: 42000,
        sentAt: "2026-08-01",
        expiresAt: "2026-08-15",
        status: "Sent",
    },
    {
        id: "2",
        candidate: "Lucas Moreau",
        email: "lucas@email.com",
        jobTitle: "Lead Developer React",
        salary: 58000,
        sentAt: "2026-07-25",
        expiresAt: "2026-08-05",
        status: "Accepted",
    },
    {
        id: "3",
        candidate: "Julie Lambert",
        email: "julie@email.com",
        jobTitle: "Product Owner",
        salary: 48000,
        sentAt: "2026-07-10",
        expiresAt: "2026-07-24",
        status: "Declined",
    },
];


const STATUS_OPTIONS: OfferStatus[] = ["Draft", "Sent", "Accepted", "Declined", "Expired"];


export default function OffersSection() {
    const { setModal } = useAppContext();
    const [offers, setOffers] = useState<Offer[]>(mockOffers);
    const [isUpdating, setIsUpdating] = useState<string | null>(null);

    const getInitials = (name: string) => {
        return name
            .split(" ")
            .map((n) => n[0])
            .join("")
            .toUpperCase()
            .slice(0, 2);
    };

    //-- format salary
    const formatSalary = (amount: number) => {
        return new Intl.NumberFormat("fr-FR", {
            style: "currency",
            currency: "EUR",
            maximumFractionDigits: 0,
        }).format(amount);
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
            // TODO: API call -> await api.deleteOffer(id);
            setOffers((prev) => prev.filter((item) => item.id !== id));
        }
        catch (error) {
            console.error("Erreur lors de la suppression de l'offre", error);
        }
        finally {
            setIsUpdating(null);
        }
    };


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


    //-- Fetch applications
    useEffect(()=>{

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
                                        <span className={styles.salaryText}>{formatSalary(offer.salary)}/an</span>
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

                                    {/* Statut */}
                                    <td data-label="Statut">
                                        <span className={`${styles.status} ${styles[`status${offer.status}`]}`}>
                                            {offer.status}
                                        </span>
                                    </td>

                                    {/* Actions */}
                                    <td data-label="Actions" className={styles.actionsCell}>
                                        <div className={styles.actionGroup}>
                                            <select
                                                value={offer.status}
                                                onChange={(e) =>
                                                    handleStatusChange(offer.id, e.target.value as OfferStatus)
                                                }
                                                className={styles.statusSelect}
                                                disabled={isUpdating === offer.id}
                                            >
                                                {STATUS_OPTIONS.map((status) => (
                                                    <option key={status} value={status}>
                                                        {status}
                                                    </option>
                                                ))}
                                            </select>

                                            <button
                                                type="button"
                                                onClick={() => handleDelete(offer.id)}
                                                className={styles.btnDanger}
                                                title="Supprimer l'offre"
                                                disabled={isUpdating === offer.id}
                                            >
                                                Supprimer
                                            </button>
                                        </div>
                                    </td>
                                    
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}