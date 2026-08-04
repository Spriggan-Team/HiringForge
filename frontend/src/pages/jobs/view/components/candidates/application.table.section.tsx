import React, { useState } from "react";
import styles from "./ApplicationTableSection.module.css";

export type ApplicationStatus = "Pending" | "Interview" | "Accepted" | "Rejected";

export interface Application {
    id: string;
    candidate: string;
    email: string;
    appliedAt: string;
    status: ApplicationStatus;
    avatarUrl?: string;
}

const mockApplications: Application[] = [
    {
        id: "1",
        candidate: "Alice Martin",
        email: "alice.martin@email.com",
        appliedAt: "2026-08-01",
        status: "Pending",
    },
    {
        id: "2",
        candidate: "Thomas Bernard",
        email: "thomas@email.com",
        appliedAt: "2026-08-02",
        status: "Interview",
    },
    {
        id: "3",
        candidate: "Sarah Dupont",
        email: "sarah@email.com",
        appliedAt: "2026-08-03",
        status: "Accepted",
    },
    {
        id: "4",
        candidate: "Lucas Moreau",
        email: "lucas@email.com",
        appliedAt: "2026-08-03",
        status: "Rejected",
    },
];

const STATUS_OPTIONS: ApplicationStatus[] = ["Pending", "Interview", "Accepted", "Rejected"];

export default function ApplicationsTable() {
    const [applications, setApplications] = useState<Application[]>(mockApplications);
    const [isUpdating, setIsUpdating] = useState<string | null>(null);

    //-- Generates the initiales
    const getInitials = (name: string) => {
        return name
            .split(" ")
            .map((n) => n[0])
            .join("")
            .toUpperCase()
            .slice(0, 2);
    };

    //-- Update Status
    const handleStatusChange = async (id: string, newStatus: ApplicationStatus) => {
        setIsUpdating(id);
        try {
            // TODO: API call -> await api.updateStatus(id, newStatus);
            setApplications((prev) =>
                prev.map((app) => (app.id === id ? { ...app, status: newStatus } : app))
            );
        } catch (error) {
            console.error("Erreur lors de la mise à jour du statut", error);
        } finally {
            setIsUpdating(null);
        }
    };

    //-- Handle Reject
    const handleDelete = async (id: string) => {
        setIsUpdating(id);
        try {
            // TODO: API call -> await api.deleteApplication(id);
            setApplications((prev) => prev.filter((app) => app.id !== id));
        }
        catch (error) {
            console.error("Erreur lors de la suppression", error);
        }
        finally {
            setIsUpdating(null);
        }
    };

    return (
        <div className={styles.tableCard}>
            <div className={styles.tableHeader}>
                <h2>Candidatures</h2>
                <span className={styles.badgeCount}>{applications.length} total</span>
            </div>

            <div className={styles.tableContainer}>
                <table className={styles.applicationsTable}>
                    <thead>
                        <tr>
                            <th>Candidat</th>
                            <th>Email</th>
                            <th>Date de postulation</th>
                            <th>Statut</th>
                            <th className={styles.textRight}>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        {applications.length === 0 ? (
                            <tr>
                                <td colSpan={5} className={styles.emptyState}>
                                    Aucune candidature trouvée.
                                </td>
                            </tr>
                        ) : (
                            applications.map((application) => (
                                <tr key={application.id} className={isUpdating === application.id ? styles.rowDisabled : ""}>
                                    {/* Candidat */}
                                    <td data-label="Candidat">
                                        <div className={styles.candidateCell}>
                                            {application.avatarUrl ? (
                                                <img
                                                    src={application.avatarUrl}
                                                    alt={application.candidate}
                                                    className={styles.avatar}
                                                />
                                            ) : (
                                                <div className={styles.avatarFallback}>
                                                    {getInitials(application.candidate)}
                                                </div>
                                            )}
                                            <span className={styles.candidateName}>{application.candidate}</span>
                                        </div>
                                    </td>

                                    {/* Email */}
                                    <td data-label="Email">
                                        <span className={styles.emailText}>{application.email}</span>
                                    </td>

                                    {/* Date */}
                                    <td data-label="Date de postulation">
                                        {new Date(application.appliedAt).toLocaleDateString("fr-FR", {
                                            day: "numeric",
                                            month: "short",
                                            year: "numeric",
                                        })}
                                    </td>

                                    {/* Statut */}
                                    <td data-label="Statut">
                                        <span className={`${styles.status} ${styles[`status${application.status}`]}`}>
                                            {application.status}
                                        </span>
                                    </td>

                                    {/* Actions */}
                                    <td data-label="Actions" className={styles.actionsCell}>
                                        <div className={styles.actionGroup}>
                                            <select
                                                value={application.status}
                                                onChange={(e) =>
                                                    handleStatusChange(application.id, e.target.value as ApplicationStatus)
                                                }
                                                className={styles.statusSelect}
                                                disabled={isUpdating === application.id}
                                            >
                                                {STATUS_OPTIONS.map((status) => (
                                                    <option key={status} value={status}>
                                                        {status}
                                                    </option>
                                                ))}
                                            </select>

                                            <button
                                                type="button"
                                                onClick={() => handleDelete(application.id)}
                                                className={styles.btnDanger}
                                                title="Supprimer"
                                                disabled={isUpdating === application.id}
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