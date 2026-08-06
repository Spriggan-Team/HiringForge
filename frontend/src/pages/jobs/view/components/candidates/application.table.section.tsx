import React, { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";


import MatchScoreCircle from "../../../../../layout/components/progress/circle/match.circle";
import { JOB_APPLICATION_STATUSES, type Application, type ApplicationStatusValue,   } from "../../../../../features/application/application";
import ApplicationQueries from "../../../../../api/services/application/queries";

import styles from "./ApplicationTableSection.module.css";
import { formatDate } from "date-fns";




const STATUS_OPTIONS = JOB_APPLICATION_STATUSES;


interface ApplicationsTablePros{
    jobId: string;
    companyId?: string;
    userId?: string;
}


export default function ApplicationsTable({
    jobId,
    userId,
    companyId
}: ApplicationsTablePros){
    const {t} = useTranslation();

    const [applications, setApplications] = useState<Application[]>([]);
    const [isUpdating, setIsUpdating] = useState<string | null>(null);

    //-- Hot loading (load application by list)
    const [count, setCount] = useState(0);
    const [skip, setSkip] = useState(0);

    useEffect(()=>{
        try{
            const initializingData = async ()=>{
                const data = await ApplicationQueries.getApplicationsForJob(jobId, { companyId }) ?? [];
                
                const applications: Application[] = data.map((value)=>({
                    id: value.id,
                    candidate: `${value.candidate.firstName} ${value.candidate.lastName}`,
                    matchScore: value.matchScore ?? 0,
                    status: value.status,
                    email: value.candidate.email,
                    avatarUrl: value.candidate.imageUrl,
                    appliedAt: formatDate(value.appliedAt, 'd MM yyyy')
                })); 

                setApplications((prev)=>([...prev,...applications]));
            }

            initializingData();
        }
        catch(error){
            console.warn('Something went wrong', error)
        }
    }, [])


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
    const handleStatusChange = async (id: string, newStatus: ApplicationStatusValue) => {
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
                            <th>{t('global.candidate.candidateLabel_one')}</th>
                            <th>{t('global.text.email')}</th>
                            <th>Score</th>
                            <th>{t('global.text.postulationDate')}</th>
                            <th>{t('global.text.status')}</th>
                            <th className={styles.textRight}>{t("global.text.actions")}</th>
                        </tr>
                    </thead>

                    <tbody>
                        {(applications ?? []).length === 0 ? (
                            <tr>
                                <td colSpan={5} className={styles.emptyState}>
                                    Aucune candidature trouvée.
                                </td>
                            </tr>
                        ) : (
                            (applications ?? []).map((application) => (
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
                            
                                    {/** Match Score */}
                                    <td data-label="Score">
                                        <MatchScoreCircle score={application.matchScore} />
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
                                                    handleStatusChange(application.id, e.target.value as ApplicationStatusValue)
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