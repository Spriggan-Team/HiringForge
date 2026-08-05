import React, { useEffect, useState } from "react";
import styles from "./InterviewsSection.module.css";

export type InterviewStatus = "Scheduled" | "Completed" | "Cancelled";

export interface Interview {
    id: string;
    candidate: string;
    email: string;
    jobTitle: string;
    scheduledAt: string; // ISO
    locationOrLink?: string;
    status: InterviewStatus;
    avatarUrl?: string;
}

const mockInterviews: Interview[] = [
    {
        id: "1",
        candidate: "Thomas Bernard",
        email: "thomas@email.com",
        jobTitle: "Développeur Fullstack PHP",
        scheduledAt: "2026-08-10T10:00:00",
        locationOrLink: "https://meet.google.com/abc-defg-hij",
        status: "Scheduled",
    },
    {
        id: "2",
        candidate: "Sarah Dupont",
        email: "sarah@email.com",
        jobTitle: "UX/UI Designer",
        scheduledAt: "2026-08-03T14:30:00",
        locationOrLink: "Salle de Réunion B",
        status: "Completed",
    },
    {
        id: "3",
        candidate: "Alexandre Petit",
        email: "alex@email.com",
        jobTitle: "DevOps Engineer",
        scheduledAt: "2026-08-12T11:00:00",
        locationOrLink: "https://zoom.us/j/123456789",
        status: "Cancelled",
    },
];

const STATUS_OPTIONS: InterviewStatus[] = ["Scheduled", "Completed", "Cancelled"];


interface InterviewsSectionProps{
    jobId: string;
    companyId?: string;
    userId?: string;
}

export default function InterviewsSection({
    jobId,
    companyId,
    userId
}: InterviewsSectionProps) {
    const [interviews, setInterviews] = useState<Interview[]>(mockInterviews);
    const [isUpdating, setIsUpdating] = useState<string | null>(null);

    const getInitials = (name: string) => {
        return name
            .split(" ")
            .map((n) => n[0])
            .join("")
            .toUpperCase()
            .slice(0, 2);
    };

    useEffect(()=>{
        try{

        }
        catch(error){
            console.log("Something went wrong")
        }
    },[])

    // Update Status
    const handleStatusChange = async (id: string, newStatus: InterviewStatus) => {
        setIsUpdating(id);
        try {
            // TODO: API call -> await api.updateInterviewStatus(id, newStatus);
            setInterviews((prev) =>
                prev.map((item) => (item.id === id ? { ...item, status: newStatus } : item))
            );
        }
        catch (error) {
            console.error("Erreur lors de la mise à jour du statut", error);
        }
        finally {
            setIsUpdating(null);
        }
    };

    // Handler (delete/cancel)
    const handleCancel = async (id: string) => {
        setIsUpdating(id);
        try {
            // TODO: API call -> await api.cancelInterview(id);
            setInterviews((prev) =>
                prev.map((item) => (item.id === id ? { ...item, status: "Cancelled" } : item))
            );
        }
        catch (error) {
            console.error("Erreur lors de l'annulation", error);
        }
        finally {
            setIsUpdating(null);
        }
    };


    return (
        <div className={styles.tableCard}>
            <div className={styles.tableHeader}>
                <h2>Entretiens à venir</h2>
                <span className={styles.badgeCount}>{interviews.length} au total</span>
            </div>

            <div className={styles.tableContainer}>
                <table className={styles.interviewsTable}>
                    <thead>
                        <tr>
                            <th>Candidat</th>
                            <th>Offre d'emploi</th>
                            <th>Date & Heure</th>
                            <th>Lieu / Lien</th>
                            <th>Statut</th>
                            <th className={styles.textRight}>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        {interviews.length === 0 ? (
                            <tr>
                                <td colSpan={6} className={styles.emptyState}>
                                    Aucun entretien planifié.
                                </td>
                            </tr>
                        ) : (
                            interviews.map((interview) => (
                                <tr
                                    key={interview.id}
                                    className={isUpdating === interview.id ? styles.rowDisabled : ""}
                                >
                                    {/* Candidat */}
                                    <td data-label="Candidat">
                                        <div className={styles.candidateCell}>
                                            {interview.avatarUrl ? (
                                                <img
                                                    src={interview.avatarUrl}
                                                    alt={interview.candidate}
                                                    className={styles.avatar}
                                                />
                                            ) : (
                                                <div className={styles.avatarFallback}>
                                                    {getInitials(interview.candidate)}
                                                </div>
                                            )}
                                            <div>
                                                <span className={styles.candidateName}>
                                                    {interview.candidate}
                                                </span>
                                                <span className={styles.emailText}>{interview.email}</span>
                                            </div>
                                        </div>
                                    </td>

                                    {/* Offre */}
                                    <td data-label="Offre d'emploi">
                                        <span className={styles.jobTitle}>{interview.jobTitle}</span>
                                    </td>

                                    {/* Date & Heure */}
                                    <td data-label="Date & Heure">
                                        <div className={styles.dateCell}>
                                            <span className={styles.dateText}>
                                                {new Date(interview.scheduledAt).toLocaleDateString("fr-FR", {
                                                    day: "numeric",
                                                    month: "short",
                                                    year: "numeric",
                                                })}
                                            </span>
                                            <span className={styles.timeText}>
                                                {new Date(interview.scheduledAt).toLocaleTimeString("fr-FR", {
                                                    hour: "2-digit",
                                                    minute: "2-digit",
                                                })}
                                            </span>
                                        </div>
                                    </td>

                                    {/* Lieu / Lien */}
                                    <td data-label="Lieu / Lien">
                                        {interview.locationOrLink ? (
                                            interview.locationOrLink.startsWith("http") ? (
                                                <a
                                                    href={interview.locationOrLink}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className={styles.linkText}
                                                >
                                                    Rejoindre la visio 🔗
                                                </a>
                                            ) : (
                                                <span className={styles.locationText}>
                                                    📍 {interview.locationOrLink}
                                                </span>
                                            )
                                        ) : (
                                            <span className={styles.mutedText}>Non spécifié</span>
                                        )}
                                    </td>

                                    {/* Statut */}
                                    <td data-label="Statut">
                                        <span className={`${styles.status} ${styles[`status${interview.status}`]}`}>
                                            {interview.status}
                                        </span>
                                    </td>

                                    {/* Actions */}
                                    <td data-label="Actions" className={styles.actionsCell}>
                                        <div className={styles.actionGroup}>
                                            <select
                                                value={interview.status}
                                                onChange={(e) =>
                                                    handleStatusChange(interview.id, e.target.value as InterviewStatus)
                                                }
                                                className={styles.statusSelect}
                                                disabled={isUpdating === interview.id}
                                            >
                                                {STATUS_OPTIONS.map((status) => (
                                                    <option key={status} value={status}>
                                                        {status}
                                                    </option>
                                                ))}
                                            </select>

                                            {interview.status !== "Cancelled" && (
                                                <button
                                                    type="button"
                                                    onClick={() => handleCancel(interview.id)}
                                                    className={styles.btnDanger}
                                                    title="Annuler l'entretien"
                                                    disabled={isUpdating === interview.id}
                                                >
                                                    Annuler
                                                </button>
                                            )}
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