import { Trans, useTranslation } from "react-i18next";
import { useCallback, useEffect, useRef, useState } from "react";


import ApplicationQueries from "../../../../../api/services/application/queries";
import {  JobApplicationStatus, type Application, type ApplicationStatusValue,   } from "../../../../../features/application/application";
import { useAppContext } from "../../../../../hooks/context";
import { canTransitionStatus } from "../../../../../features/application/helpers";


import { ApplicationSearchHeader } from "./components/applications.search.header";
import { ApplicationTableHead, ApplicationTableTitle } from "./components/application.table.header";
import { ApplicationRow } from "./components/application.table.row";
import { ConfirmModal } from "../../../../../layout/components/conform.box";
import { ApplicationDetailModal } from "../../../components/application/application.details";

import ApplicationServices from "../../../../../api/services/application/command";
import { getInitials } from "../../../../../utils/format";


import styles from "./ApplicationsTable.module.css";



//---------------
//-- Page
//---------------
const PAGE_SIZE = 10;
const DEBOUNCE_DELAY = 400;

//------------
//-- Cache
//--------------

interface ApplicationsQueryCache {
    data: Application[];
    hasMore: boolean;
    fetchedAt: number;
}


const CACHE_DURATION = 1000 * 60 *5; // 5 min

const getApplicationsCacheKey = ({
    jobId,
    companyId,
    skip,
    limit,
    search,
    status,
}: {
    jobId?: string;
    companyId?: string;
    skip: number;
    limit: number;
    search?: string;
    status?: ApplicationStatusValue[] | null;
}) => {
    return [
        jobId ?? "all-jobs",
        companyId ?? "all-companies",
        `skip:${skip}`,
        `limit:${limit}`,
        `search:${search?.trim().toLowerCase() ?? ""}`,
        `status:${[...(status ?? [])].sort().join(",")}`,
    ].join("|");
};


//-----------------------
// --- API Helper & Props
//-----------------------

interface ApplicationFilters{
  status: ApplicationStatusValue[] | null
}

const fetchApplicationsApi = async (
  params: { 
    jobId?: string; 
    companyId?: string; 
    skip: number; 
    limit: number; 
    search?: string
  } & Partial<ApplicationFilters>,
  signal?: AbortSignal
): Promise<Application[]> => {
  // console.log("PARAMS  : ", params )
  const data = (await ApplicationQueries.getApplications({...params, signal })) ?? [];
  return data.map((value: any) => {
    // console.log("Application id : ",value.candidate.id)
      return ({
        id: value.id,
        candidateId: value.candidate.id,
        candidate: `${value.candidate.firstName} ${value.candidate.lastName}`,
        matchScore: value.matchScore ?? 0,
        status: value.status,
        email: value.candidate.email,
        avatarUrl: value.candidate.imageUrl,
        appliedAt: value.appliedAt ?? "",
    })
  });
};


type FetchMode = "replace" | "append";


//-- Props
interface ApplicationsTableProps {
  jobId?: string;
  companyId?: string;
}


export default function ApplicationsTable({ jobId, companyId }: ApplicationsTableProps) {
  const { t } = useTranslation();
  const { setModal } = useAppContext();

  //-- Applications & params
  const [isUpdating, setIsUpdating] = useState<string | null>(null);
  const [applications, setApplications] = useState<Application[]>([]);
  const [applicationFilters, setApplicationFilters] = useState<ApplicationFilters>({
    status: null
  });

  const [activeApplication, setActiveApplication] = useState<{applicationId: string; candidateId: string} | null>(null);
  const [candidateProfilImages, setCandidateProfilImages] = useState<Record<string, string>>({});
  
  //-- Memory Cache
  const applicationsCache  = useRef<Map<string,ApplicationsQueryCache>>(new Map());
  const pendingRequests  = useRef<Map<string, Promise<ApplicationsQueryCache>>>(new Map());


  // -- Pagination / Hot loading
  const [skip, setSkip] = useState(0);
  const [hasMore, setHasMore] = useState(true);
  //-- Loading
  const [isLoading, setIsLoading] = useState(false); //-- Is resetting view
  const [isLoadingMore, setIsLoadingMore] = useState(false); //-- Is loading More
  const loadedRef = useRef<boolean>(false); // Synchronous varification - indicate something is loading

  //-- cache ref
  const imageUrlCache = useRef<Map<string, string>>(new Map()); // image url ...
  const resumeUrlCache = useRef<Map<string, string>>(new Map()); // image url ...

  // -- Control scroll
  const observerTarget = useRef<HTMLTableRowElement | null>(null);
  
  // -- Search State
  const [search, setSearch] = useState<string>('');
  const [debouncedSearch, setDebouncedSearch] = useState<string>('');


  // Search Debounce
  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedSearch(search);
    }, DEBOUNCE_DELAY);

    return () => {
      clearTimeout(handler);
    };
  }, [search]);


  //-- Global api call
  const requestApplications = async ({
      jobId,
      companyId,
      skip,
      limit,
      search,
      status,
      signal,
      mode = "replace",
  }: {
      jobId?: string;
      companyId?: string;
      skip: number;
      limit: number;
      search?: string;
      status?: ApplicationStatusValue[] | null;
      signal?: AbortSignal;
      mode?: FetchMode;
  }): Promise<ApplicationsQueryCache> => {
      
      const key = getApplicationsCacheKey({
          jobId,
          companyId,
          skip,
          limit,
          search,
          status,
      });

      //----------------------------------
      // CACHE HIT
      //----------------------------------

      const cached = applicationsCache.current.get(key);

      if (
          cached &&
          Date.now() - cached.fetchedAt < CACHE_DURATION
      ) {
          updateApplicationsState(cached.data, mode);
          return cached;
      }

      //----------------------------------
      // REQUEST ALREADY RUNNING
      //----------------------------------

      const pending = pendingRequests.current.get(key);

      if (pending) {
          const result = await pending;
          updateApplicationsState(result.data, mode);
          return result;
      }

      //----------------------------------
      // CACHE MISS → API
      //----------------------------------

      const request = fetchApplicationsApi(
          {
              jobId,
              companyId,
              skip,
              limit,
              search,
              status,
          },
          signal
      )
        .then(async (data) => {
            const normalizedData = await handleApplicationsReceived(data);
            const result: ApplicationsQueryCache = {
                data: normalizedData,
                hasMore: normalizedData.length === limit,
                fetchedAt: Date.now(),
            };

            //-- Updating
            applicationsCache.current.set(key, result);
            updateApplicationsState(normalizedData, mode);

            return result;
        })
        .finally(() => {
            pendingRequests.current.delete(key);
        });

      pendingRequests.current.set(key, request);

      return request;
  };


  //-- Update Application state when fetching
  const updateApplicationsState = (
      applications: Application[],
      mode: FetchMode
  ) => {
      setApplications(prev => {
          if (mode === "replace") {
              return applications;
          }
          const existingIds = new Set(prev.map(app => app.id));
          const newApplications = applications.filter(app => !existingIds.has(app.id));

          return [...prev, ...newApplications];
      });
  };


  //-- handle application received state (through api)
  const handleApplicationsReceived = async (
      applications: Application[]
  ): Promise<Application[]> => {

      const ids = applications
          .filter(
              app => app.status === JobApplicationStatus.APPLIED
          )
          .map(app => app.id);

      if (ids.length > 0) {
          await ApplicationServices.updateApplicationsStatus(
              ids,
              JobApplicationStatus.RECEIVED
          );
      }

      return applications.map(app => ({
          ...app,
          status:
              app.status === JobApplicationStatus.APPLIED
                  ? JobApplicationStatus.RECEIVED
                  : app.status,
      }));
  };


  //-- Loading applications
  const fetchApplications = async ({
      mode,
      skip,
      search,
      signal,
  }: {
      mode: FetchMode;
      skip: number;
      search?: string;
      signal?: AbortSignal;
  }) => {
      loadedRef.current = true;
      try {
          const result = await requestApplications({
              jobId,
              companyId,
              skip,
              limit: PAGE_SIZE,
              search,
              status: applicationFilters.status,
              signal,
              mode,
          });

          //--------------------------------
          // Pagination
          //--------------------------------

          setSkip(skip);
          setHasMore(result.hasMore);

          //--------------------------------
          // Secondary resources
          //--------------------------------

          handleCandidateProfilImage(result.data);
          return result;
      }
      catch (error: any) {
          if (error.name !== "AbortError") {
              console.error(
                  "Error while fetching applications",
                  error
              );
          }

          throw error;
      }
      finally {
          loadedRef.current = false;
      }
  };


  //  Method for REFRESHING / RESETTING (New search, change of job/company)
  const handleResetAndFetch = useCallback(
      async (searchTerm: string) => {
          setIsLoading(true)
          await fetchApplications({
              mode: "replace",
              skip: 0,
              search: searchTerm || undefined,
          });
          setIsLoading(false)
      },
      [
        jobId,
        companyId,
        applicationFilters.status,
      ]
  );
  

  //--  Pagination
  const handleFetchMore = useCallback(async () => {
      if (isLoadingMore || !hasMore) {
          return;
      }

      setIsLoadingMore(true);
      await fetchApplications({
          mode: "append",
          skip: skip + PAGE_SIZE,
          search: debouncedSearch || undefined,
      });
      setIsLoading(false);

  }, [
      skip,
      hasMore,
      isLoadingMore,
      debouncedSearch,
  ]);


  //--  load candidate image
  const handleCandidateProfilImage = useCallback(async (data: Application[]) => {
      for (const application of data) {
        const applicationId = application.id;

        // Image déjà chargée
        const cachedUrl = imageUrlCache.current.get(applicationId);

        if (cachedUrl) {
          setCandidateProfilImages((prev) => ({
            ...prev,
            [applicationId]: cachedUrl,
          }));

          continue;
        }

        try {
          const blob = await ApplicationQueries.getCandidateProfilImage({
            candidateId: application.candidateId,
            applicationId: applicationId,
          });

          const url = URL.createObjectURL(blob);

          imageUrlCache.current.set(applicationId, url);

          setCandidateProfilImages((prev) => ({
            ...prev,
            [applicationId]: url,
          }));
        }
        catch (error) {
          console.error(
            `Impossible de récupérer l'image du candidat ${application.candidateId}`,
            error
          );
        }
      }
  },[]);


  //-- Get CV file
  const handleCandidateResume = useCallback(async ({candidateId, applicationId}: {candidateId: string, applicationId: string})=>{
    const cachedUrl = resumeUrlCache.current.get(applicationId);

    if (cachedUrl) {
      return;
    }

    try{
      const blob = await ApplicationQueries.getCandidateResume({
        candidateId,
        applicationId
      });

      const url = URL.createObjectURL(blob);
      resumeUrlCache.current.set(applicationId, url);

      return url;
    }
    catch(error){
      console.error(
        "Something went wrong while fetching candidates resume"
      );
      return undefined;
    }
  },[]);




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


  //-- Observe filters change
  useEffect(() => {
      fetchApplications({
          mode: "replace",
          skip: 0,
          search: debouncedSearch || undefined,
      });
  }, [
      applicationFilters,
      debouncedSearch,
      jobId,
      companyId,
  ]);


  // --- Handlers change of statut & actions ---
  const handleStatusChange = async (
    applicationId: string,
    currentStatus: ApplicationStatusValue,
    newStatus: ApplicationStatusValue
  ) => {
    console.log("Transition : ", currentStatus, ' -> ' , newStatus, "; can transit : ", canTransitionStatus(currentStatus, newStatus))
    if (!canTransitionStatus(currentStatus, newStatus)) {
      console.warn(`Transition non autorisée de ${currentStatus} vers ${newStatus}`);
      return;
    }

    setIsUpdating(applicationId);
    try {
      await ApplicationServices.updateStatus(applicationId, newStatus);
      setApplications(prev =>
            prev.map(app =>
                app.id === applicationId
                    ? { ...app, status: newStatus }
                    : app
            )
      );
      applicationsCache.current.clear();
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



  const handleConsult = (
    application: Application,
    onCloseCallback?: () => void,
    resumeUrl?: string
  ) => {
    const handleClose = () => {
      setModal(null);
      if (onCloseCallback) {
        onCloseCallback();
      }
    };

    setModal({
      isOpen: true,
      title: `${t('applications.headers.detailsOfApplication')} - ${application.candidate}`,
      content: <ApplicationDetailModal 
                  onClose={handleClose}
                  application={application}
                  imageUrl={candidateProfilImages[application.id]}
                  resumeUrl={resumeUrl}
               />,
      onClose: handleClose,
    });
  };


  const handleToggleEye = async (application: Application) => {
    if (activeApplication?.applicationId === application.id) {
      setModal(null);
      setActiveApplication(null);
    }
    else {
      setActiveApplication({
        applicationId: application.id,
        candidateId: application.candidateId
      });
      const resumeUrl = await handleCandidateResume({
        candidateId: application.candidateId,
        applicationId: application.id,
      });

      handleConsult(application, () => {
        setActiveApplication(null);
      }, resumeUrl);
    }
  };


  //-- Clear memory
  useEffect(()=>{
    return () => {
      for (const url of imageUrlCache.current.values()) {
        URL.revokeObjectURL(url);
      }
      for(const url of resumeUrlCache.current.values()){
        URL.revokeObjectURL(url);
      }
      imageUrlCache.current.clear();
      resumeUrlCache.current.clear()
    };
  },[]);
  

  // Reset trigger (search or change of props)
  useEffect(() => {
    handleResetAndFetch(debouncedSearch);
  }, [debouncedSearch, jobId, companyId, handleResetAndFetch]);



  return (
    <div className={styles.container}>
      <ApplicationSearchHeader 
        t={t}
        onFilterValueChange={({status})=>{
          setApplicationFilters({status})
        }}
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
                    t={t}
                    key={application.id}
                    application={application}
                    isUpdating={isUpdating === application.id}
                    isEyeOpen={activeApplication?.applicationId === application.id}
                    imageUrl={candidateProfilImages[application.id]}
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