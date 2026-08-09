import { formatDate } from "date-fns";
import { Trans, useTranslation } from "react-i18next";
import React, { useCallback, useEffect, useRef, useState } from "react";


import ApplicationQueries from "../../../../../api/services/application/queries";
import {  JobApplicationStatus, type Application, type ApplicationStatusValue,   } from "../../../../../features/application/application";
import { useAppContext } from "../../../../../hooks/context";
import { canTransitionStatus } from "../../../../../features/application/helpers";


import { ApplicationSearchHeader } from "./components/applications.search.header";
import { ApplicationTableHead, ApplicationTableTitle } from "./components/application.table.header";
import { ApplicationRow } from "./components/application.table.row";
import { ConfirmModal } from "../../../../../layout/components/conform.box";
import { ApplicationDetailModal } from "../../../components/application/application.details";


import styles from "./ApplicationsTable.module.css";



const PAGE_SIZE = 10;
const DEBOUNCE_DELAY = 400;


// --- API Helper
const fetchApplicationsApi = async (
  params: { jobId?: string; companyId?: string; skip: number; limit: number; search?: string },
  signal?: AbortSignal
): Promise<Application[]> => {
  const data = (await ApplicationQueries.getApplicationsJob({...params, signal })) ?? [];

  return data.map((value: any) => ({
    id: value.id,
    candidate: `${value.candidate.firstName} ${value.candidate.lastName}`,
    matchScore: value.matchScore ?? 0,
    status: value.status,
    email: value.candidate.email,
    avatarUrl: value.candidate.imageUrl,
    appliedAt: formatDate(value.appliedAt, 'd MM yyyy'),
  }));
};



interface ApplicationsTableProps {
  jobId?: string;
  companyId?: string;
}



export default function ApplicationsTable({ jobId, companyId }: ApplicationsTableProps) {
  const { t } = useTranslation();
  const { setModal } = useAppContext();

  const [applications, setApplications] = useState<Application[]>([]);
  const [isUpdating, setIsUpdating] = useState<string | null>(null);
  const [activeApplicationId, setActiveApplicationId] = useState<string | null>(null);

  // -- Pagination / Hot loading
  const [skip, setSkip] = useState(0);
  const [hasMore, setHasMore] = useState(true);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const loadedRef = useRef<boolean>(false); // Synchronous varification

  // -- Control scroll
  const observerTarget = useRef<HTMLTableRowElement | null>(null);

  // -- Search State
  const [search, setSearch] = useState<string>('');
  const [debouncedSearch, setDebouncedSearch] = useState<string>('');

  // Reference for canceling the previous query if a new search or filter is initiated
  const abortControllerRef = useRef<AbortController | null>(null);

  // Search Debounce
  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedSearch(search);
    }, DEBOUNCE_DELAY);

    return () => {
      clearTimeout(handler);
    };
  }, [search]);


  //  Method for REFRESHING / RESETTING (New search, change of job/company)
  const handleResetAndFetch = useCallback(
    async (searchTerm: string) => {
      // Cancel the previous request if it is still in progress
      if (abortControllerRef.current) {
        abortControllerRef.current.abort();
      }

      const newController = new AbortController();
      abortControllerRef.current = newController;

      setIsLoadingMore(true);
      loadedRef.current = true;
      setSkip(0);

      try {
        const freshData = await fetchApplicationsApi(
          {
            jobId,
            companyId,
            skip: 0,
            limit: PAGE_SIZE,
            search: searchTerm || undefined,
          },
          newController.signal
        );

        setApplications(freshData);
        setHasMore(freshData.length === PAGE_SIZE);
      }
      catch (error: any) {
        if (error.name !== 'AbortError') {
          console.error('Erreur lors du chargement des candidatures:', error);
        }
      }
      finally {
        setIsLoadingMore(false);
        loadedRef.current = false;
      }
    },
    [jobId, companyId]
  );
  

  // Pagination
  const handleFetchMore = useCallback(async () => {
    if (isLoadingMore || !hasMore)
      return;

    setIsLoadingMore(true);
    loadedRef.current = true;

    const nextSkip = skip + PAGE_SIZE;

    try {
      const moreData = await fetchApplicationsApi({
        jobId,
        companyId,
        skip: nextSkip,
        limit: PAGE_SIZE,
        search: debouncedSearch || undefined,
      });

      setApplications((prev) => [...prev, ...moreData]);
      setSkip(nextSkip);
      setHasMore(moreData.length === PAGE_SIZE);
    } 
    catch (error) {
      console.error('Erreur lors du chargement de la suite des candidatures:', error);
    }
    finally {
      setIsLoadingMore(false);
      loadedRef.current = false;
    }
  }, [jobId, companyId, skip, hasMore, debouncedSearch]);

  

  // Reset trigger (search or change of props)
  useEffect(() => {
    handleResetAndFetch(debouncedSearch);
  }, [debouncedSearch, jobId, companyId, handleResetAndFetch]);


  // Intersection Observer for Infinite Scroll
  useEffect(() => {
      const observer = new IntersectionObserver(
          (entries) => {
              if (entries[0].isIntersecting && hasMore && !isLoadingMore) {
              handleFetchMore();
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
  },[hasMore, handleFetchMore]);


  // Utility Initials
  const getInitials = (name: string) => {
    return name
      .split(' ')
      .map((n) => n[0])
      .join('')
      .toUpperCase()
      .slice(0, 2);
  };


  // --- Handlers of statut & actions ---
  const handleStatusChange = async (
    id: string,
    currentStatus: ApplicationStatusValue,
    newStatus: ApplicationStatusValue
  ) => {
    if (!canTransitionStatus(currentStatus, newStatus)) {
      console.warn(`Transition non autorisée de ${currentStatus} vers ${newStatus}`);
      return;
    }

    setIsUpdating(id);
    try {
      // TODO: API Call -> await api.updateStatus(id, newStatus);
      setApplications((prev) =>
        prev.map((app) => (app.id === id ? { ...app, status: newStatus } : app))
      );
    }
    catch (error) {
      console.error('Erreur lors de la mise à jour du statut', error);
    }
    finally {
      setIsUpdating(null);
    }
  };


  const handleRequestStatusChange = (
    applicationId: string,
    candidateName: string,
    currentStatus: ApplicationStatusValue,
    newStatus: ApplicationStatusValue
  ) => {
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
                  status: newStatus.toLocaleUpperCase(),
                }}
                components={{
                  strong: <strong />,
                }}
              />
            }
            confirmText={t('applications.confirm.rejectCandidate')}
            variant="danger"
            onConfirm={() => {
              handleStatusChange(applicationId, currentStatus, newStatus);
              setModal(null);
            }}
            onCancel={() => setModal(null)}
          />
        ),
      });
      return;
    }

    handleStatusChange(applicationId, currentStatus, newStatus);
  };

  

  const handleReject = async (id: string) => {
    setIsUpdating(id);
    try {
      // TODO: API call -> await api.deleteApplication(id);
      setApplications((prev) => prev.filter((app) => app.id !== id));
    }
    catch (error) {
      console.error('Erreur lors de la suppression', error);
    }
    finally {
      setIsUpdating(null);
    }
  };



  const handleConsult = (application: Application, onCloseCallback?: () => void) => {
    const handleClose = () => {
      setModal(null);
      if (onCloseCallback) {
        onCloseCallback();
      }
    };

    setModal({
      isOpen: true,
      title: `${t('applications.headers.detailsOfApplication')} - ${application.candidate}`,
      content: <ApplicationDetailModal application={application} onClose={handleClose} />,
      onClose: handleClose,
    });
  };


  const handleToggleEye = (application: Application) => {
    if (activeApplicationId === application.id) {
      setModal(null);
      setActiveApplicationId(null);
    } else {
      setActiveApplicationId(application.id);
      handleConsult(application, () => {
        setActiveApplicationId(null);
      });
    }
  };


  return (
    <div className={styles.container}>
      <ApplicationSearchHeader 
        t={t}
        search={search}
        onSearchChange={setSearch}
      />

      <div className={styles.tableCard}>
        <ApplicationTableTitle totalCount={applications.length} />

        <div className={styles.tableContainer}>
          <table className={styles.applicationsTable}>
            <ApplicationTableHead  t={t} />

            <tbody>
              {applications.length === 0 && !isLoadingMore ? (
                <tr>
                  <td colSpan={6} className={styles.emptyState}>
                    {t("applications.messages.noApplicationsFounded")}
                  </td>
                </tr>
              ) : (
                applications.map((application) => (
                  <ApplicationRow
                    key={application.id}
                    application={application}
                    isUpdating={isUpdating === application.id}
                    isEyeOpen={activeApplicationId === application.id}
                    t={t}
                    onToggleEye={handleToggleEye}
                    onRequestStatusChange={handleRequestStatusChange}
                    onReject={handleReject}
                    getInitials={getInitials}
                  />
                ))
              )}

              {/* Sentinelle pour l'infinite scroll */}
              {hasMore && (
                <tr ref={observerTarget}>
                  <td colSpan={6} style={{ textAlign: 'center', padding: '1rem' }}>
                    {isLoadingMore ? t('applications.messages.loadingNextApplication') : ''}
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