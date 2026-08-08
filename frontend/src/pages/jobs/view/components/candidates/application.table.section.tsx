import { formatDate } from "date-fns";
import React, { useCallback, useEffect, useRef, useState } from "react";
import { Trans, useTranslation } from "react-i18next";


import MatchScoreCircle from "../../../../../layout/components/progress/circle/match.circle";
import ApplicationQueries from "../../../../../api/services/application/queries";
import { JOB_APPLICATION_STATUSES, JobApplicationStatus, type Application, type ApplicationStatusValue,   } from "../../../../../features/application/application";
import { useAppContext } from "../../../../../hooks/context";
import { canTransitionStatus } from "../../../../../features/application/helpers";


import { ConfirmModal } from "../../../../../layout/components/conform.box";
import { StatusDropdown } from "./status.dropdown";
import { ApplicationDetailModal } from "../../../components/application/application.details";
import { EyeIcon } from "../../../../../layout/components/icons/eye.icon";
import BasicInput from "../../../../../layout/components/form/input/basic.input";

import SearchSVGComponent from "/src/assets/svg/menu/search-svgrepo-com.svg"


import styles from "./ApplicationTableSection.module.css";



const PAGE_SIZE = 10;
const STATUS_OPTIONS = JOB_APPLICATION_STATUSES;


interface ApplicationsTablePros{
    jobId?: string;
    companyId?: string;
}


export default function ApplicationsTable({
    jobId,
    companyId
}: ApplicationsTablePros){
    const {t} = useTranslation();
    const { setModal } = useAppContext();

    const [applications, setApplications] = useState<Application[]>([]);
    const [isUpdating, setIsUpdating] = useState<string | null>(null);

    //-- Selected application
    const [activeApplicationId, setActiveApplicationId] = useState<string | null>(null);

    //-- Hot loading (load application by list)
    const [skip, setSkip] = useState(0);
    const [hasMore, setHasMore] = useState(true);
    const [isLoadingMore, setIsLoadingMore] = useState(false);

    //--- Search
    const [seacrh, setSearch] = useState<string | null>(null)

    //-- Control scroll
    const observerTarget = useRef<HTMLTableRowElement | null>(null);


    const loadApplications = useCallback(async (currentSkip: number) => {
        if (isLoadingMore) return;
        setIsLoadingMore(true);

        try {
            const data = await ApplicationQueries.getApplicationsJob({
                jobId: jobId,
                companyId,
                skip: currentSkip,
                limit: PAGE_SIZE
            }) ?? [];

            const newApplications: Application[] = data.map((value: any) => ({
                id: value.id,
                candidate: `${value.candidate.firstName} ${value.candidate.lastName}`,
                matchScore: value.matchScore ?? 0,
                status: value.status,
                email: value.candidate.email,
                avatarUrl: value.candidate.imageUrl,
                appliedAt: formatDate(value.appliedAt, 'd MM yyyy')
            }));

            // Determines wether there is still some elements to load
            if (newApplications.length < PAGE_SIZE) {
                setHasMore(false);
            }

            setApplications((prev) => (currentSkip === 0 ? newApplications : [...prev, ...newApplications]));
        }
        catch (error) {
            console.warn('Erreur lors du chargement des candidatures', error);
        }
        finally {
            setIsLoadingMore(false);
        }
    }, [jobId, companyId]);


    //-- Initialize loading
    useEffect(() => {
        setSkip(0);
        setHasMore(true);
        setApplications([]);
        loadApplications(0);
    }, [jobId, companyId, loadApplications]);


    //-- Intersactions observer (controll hot loading)
    useEffect(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                if (entries[0].isIntersecting && hasMore && !isLoadingMore) {
                    setSkip((prevSkip) => {
                        const nextSkip = prevSkip + PAGE_SIZE;
                        loadApplications(nextSkip);
                        return nextSkip;
                    });
                }
            },
            { threshold: 0.5 }
        );

        const currentTarget = observerTarget.current;
        if (currentTarget) {
            observer.observe(currentTarget);
        }

        return () => {
            if (currentTarget) {
                observer.unobserve(currentTarget);
            }
        };
    }, [hasMore, isLoadingMore, loadApplications]);



    //-- Generates the initiales
    const getInitials = (name: string) => {
        return name
            .split(" ")
            .map((n) => n[0])
            .join("")
            .toUpperCase()
            .slice(0, 2);
    };


    //-----------------------------------
    //------ Change Jobs Status
    //-----------------------------------


    //-- Update Status
    const handleStatusChange = async (id: string, currentStatus: ApplicationStatusValue, newStatus: ApplicationStatusValue) => {
        // Additionnal sécurity on client
        if (!canTransitionStatus(currentStatus, newStatus)) {
            console.warn(`Transition non autorisée de ${currentStatus} vers ${newStatus}`);
            return;
        }

        setIsUpdating(id);
        try {
            // TODO: Appel API réel -> await api.updateStatus(id, newStatus);
            setApplications((prev) =>
                prev.map((app) => (app.id === id ? { ...app, status: newStatus } : app))
            );
        }
        catch (error) {
            console.error("Erreur lors de la mise à jour du statut", error);
        }
        finally {
            setIsUpdating(null);
        }
    };


    //-- Decide wether the status should change
    const handleRequestStatusChange = (
        applicationId: string,
        candidateName: string,
        currentStatus: ApplicationStatusValue,
        newStatus: ApplicationStatusValue
    ) => {
        // -- Confrm reject
        if (newStatus === JobApplicationStatus.REJECTED) {
            setModal({
                isOpen: true,
                title: 'Refuser la candidature',
                content: (
                    <ConfirmModal
                        title="Refuser le candidat"
                        message={
                            <Trans
                                i18nKey="applications.messages.confirmStatusChange"
                                values={{
                                    candidateName,
                                    status: newStatus.toLocaleUpperCase()
                                }}
                                components={{
                                    strong: <strong />
                                }}
                            />
                        }
                        warningText="Le candidat recevra une notification de refus si les emails automatiques sont activés."
                        confirmText="Refuser le candidat"
                        variant="danger"
                        onConfirm={() => {
                            handleStatusChange(applicationId, currentStatus, newStatus);
                            setModal(null);
                        }}
                        onCancel={() => setModal(null)}
                    />
                )
            });
            return;
        }

        // For all other statuses that do not require confirmation
        handleStatusChange(applicationId, currentStatus, newStatus);
    };




    //-- Handle Reject
    const handleReject = async (id: string) => {
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


    //-- Consult details about a candidates
    const handleConsult = (application: Application, onCloseCallback?: () => void) => {
        const handleClose = () => {
            setModal(null);
            if (onCloseCallback) {
                onCloseCallback();
            }
        };

        setModal({
            isOpen: true,
            title: `Détails de la candidature - ${application.candidate}`,
            content: (
                <ApplicationDetailModal
                    application={application}
                    onClose={handleClose}
                />
            ),
            onClose: handleClose
        });
    };

    //-- Handle Toogle Eye icon
    const handleToggleEye = (application: Application) => {
            if (activeApplicationId === application.id) {
                // Close modal
                setModal(null);
                setActiveApplicationId(null);
            }
            else {
                // Open the modal & deal with clearing
                setActiveApplicationId(application.id);
                handleConsult(application, () => {
                    setActiveApplicationId(null);
                });
            }
    };


    return (
        <div className={styles.container}>
            <div> 
                <BasicInput
                    value={seacrh}
                    svg={SearchSVGComponent}
                    backgroundColor="white"
                    className={`${styles.input} input`}
                    onChange={(e)=>setSearch(e.target.value)}
                />
            </div>
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
                                (applications ?? []).map((application) => {
                                    const isEyeOpen = activeApplicationId === application.id;
                                    return(
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
                                                <StatusDropdown
                                                    currentStatus={application.status}
                                                    onStatusChange={(newStatus) =>
                                                        handleRequestStatusChange(
                                                            application.id,
                                                            application.candidate,
                                                            application.status,
                                                            newStatus
                                                        )
                                                    }
                                                    disabled={isUpdating === application.id}
                                                />
                                            </td>

                                            {/* Actions */}
                                            <td data-label="Actions" className={styles.actionsCell}>
                                                {/** Eye Icon for */}
                                                <button
                                                    type="button"
                                                    onClick={() => handleToggleEye(application)}
                                                    className={styles.btnSecondary}
                                                    title={activeApplicationId ? "Masquer les détails" : "Voir les détails"}
                                                    disabled={isUpdating === application.id}
                                                >
                                                    <EyeIcon isOpen={isEyeOpen} />
                                                </button>

                                                {/*** Reject  */}
                                                <div className={styles.actionGroup}>
                                                    <button
                                                        type="button"
                                                        onClick={() => handleReject(application.id)}
                                                        className={styles.btnDanger}
                                                        title="Supprimer"
                                                        disabled={isUpdating === application.id}
                                                    >
                                                        {t('global.actions.reject')}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    )
                                })
                            )}

                            {/* Ligne sentinelle & Loader de fin de tableau */}
                            {hasMore && (
                                <tr ref={observerTarget}>
                                    <td colSpan={6} style={{ textAlign: 'center', padding: '1rem' }}>
                                        {isLoadingMore ? t('applications.messages.loadingNextApplication') : ""}
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}