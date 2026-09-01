import { format } from "date-fns";
import { useTranslation } from "react-i18next";
import React, { useCallback, useEffect, useMemo, useRef, useState } from "react";

import { useAppContext } from "../../../../../hooks/context";
import { useDebounce } from "../../../../../hooks/timer";
import InterviewsQueries from "../../../../../api/services/interviews/queries";
import InterviewsServices from "../../../../../api/services/interviews/command";
import JobQueries from "../../../../../api/services/jobs/queries";

import type { CandidateLightModel } from "../../../../../features/candidates/candidates";
import { INTERVIEW_STATUSES, InterviewStatus, type CreateInterviewFormData, type InterviewStatusValue, type InterviewTypeValue } from "../../../../../features/interviews/interviews";


import InterviewRow from "./components/interviews.table.row";
import InterviewFilters from "./components/interview.filters";
import { InterviewToolbar } from "./components/interview.toolbar";
import { GenerateInterviewModal } from "./components/generate.interview.modal";


import styles from "./Interviews.module.css";
import { ConcurrentInterviewsException } from "../../../../../api/services/interviews/exceptions";
import { UnableResourceDeletion } from "../../../../../api/services/exceptions";
import type { CompleteJobView } from "../../../../../features/jobs/JobOffer";
import { formatDateSafely } from "../../../../../utils/format";
import type { PendingRequest } from "../../../../../features/shared/global";



//-----------------
//-- Query Cache
//-----------------

interface InterviewsQueryCache {
    data: Interview[];
    hasMore: boolean;
    fetchedAt: number;
}

type FetchMode = "replace" | "append";

const CACHE_DURATION = 1000 * 60 * 5;
const PAGE_LIMIT = 10;




const getInterviewsCacheKey = ({
    jobId,
    companyId,
    skip,
    limit,
    statuses,
}: {
    jobId?: string;
    companyId?: string;
    skip: number;
    limit: number;
    statuses?: InterviewStatusValue[] | null;
}) => {
    return JSON.stringify({
        jobId: jobId ?? null,
        companyId: companyId ?? null,
        skip,
        limit,
        statuses: statuses
            ? [...statuses].sort()
            : null,
    });
};


//-- Pendings Requets
type PendingInterviewRequest = PendingRequest<InterviewsQueryCache>;

/**
 * ------------------
 * --- Page
 * ------------------
 */
export interface Interview {
  id: string;
  jobTitle: string;
  candidate: string;
  email: string;
  scheduledAt: string;
  locationOrLink?: string;
  status: InterviewStatusValue;
  minutes: number;
  avatarUrl?: string | null;
  candidateApproval?: boolean;
}


export interface InterviewsSectionProps {
  job: {
    id: string;
    title: string;
  };
  companyId?: string;
  updateJob?: React.Dispatch<React.SetStateAction<CompleteJobView | null>>
}

interface InterviewsFilters{
  statuses: InterviewStatusValue[] | null
}


export default function InterviewsSection({
  job: { id: jobId, title: jobTitle },
  companyId,
  updateJob
}: InterviewsSectionProps) {
  const { t } = useTranslation();
  const { setModal, setPopup, setLoading } = useAppContext() ;

  const [interviews, setInterviews] = useState<Interview[]>([]);
  const [isUpdating, setIsUpdating] = useState<string | null>(null);

  // Recherche & Filters
  const [searchQuery, setSearchQuery] = useState("");
  const debouncedSearch = useDebounce(searchQuery, 350);
  const [filters, setFilters] = useState<InterviewsFilters>({ statuses: null });

  // Pagination & Infinite Scroll
  const [skip, setSkip] = useState(0);
  const [hasMore, setHasMore] = useState(true);

  //-- Loading State
  const [isLoading, setIsLoading] = useState(false); //-- Reset page 
  const [isLoadingMore, setIsLoadingMore] = useState(false);//-- New page
  const abortControllerRef = useRef<AbortController | null>(null);
  const isFetchingMoreRef = useRef(false); // locker

  // Cache & Refs
    // Interview
  const interviewsCache = useRef(new Map<string, InterviewsQueryCache>());
  const pendingRequests = useRef(new Map<string, PendingInterviewRequest>());

    //-- image
  const imageUrlCache = useRef<Map<string, string>>(new Map()); // image url ...

  //--HTML ELEMENT
  const tableContainerRef = useRef<HTMLDivElement | null>(null);
  const observerTargetRef = useRef<HTMLDivElement | null>(null);


  const getInitials = useCallback((name: string) => {
    if (!name) return "";
    return name
      .split(" ")
      .map((n) => n[0])
      .join("")
      .toUpperCase()
      .slice(0, 2);
  }, []);


  //--------------------
  //-- CAHCHE HANDLERS
  //--------------------
  
  const invalidateInterviewsCache = useCallback(() => {
    interviewsCache.current.clear();
  }, []);

  const invalidateImageCache = useCallback(() => {
    imageUrlCache.current.forEach((url) => URL.revokeObjectURL(url));
    imageUrlCache.current.clear();
  }, []);
  
  const deleteInterviewFromCache = useCallback((interviewId: string) => {
      interviewsCache.current.forEach((cache, key) => {
          const exists = cache.data.some(
              item => item.id === interviewId
          );

          if (!exists) return;

          interviewsCache.current.set(key, {
              ...cache,
              data: cache.data.filter(
                  item => item.id !== interviewId
              ),
          });
      });
  }, []);


  //-- Api handlers
  const requestInterviews = useCallback((async ({
      skip,
      limit,
      statuses,
      search,
      signal
  }: {
      skip: number;
      limit: number;
      search?: string;
      statuses?: InterviewStatusValue[] | null;
      signal?: AbortSignal 
  }): Promise<InterviewsQueryCache> => {
      const key = getInterviewsCacheKey({
          jobId,
          companyId,
          skip,
          limit,
          statuses,
      });

      //--------------------------------
      // CACHE HIT
      //--------------------------------

      const cached = interviewsCache.current.get(key);
      if (
          cached &&
          Date.now() - cached.fetchedAt < CACHE_DURATION
      ) {
          console.log("Interviews cache HIT", key);

          return cached;
      }

      //--------------------------------
      // PENDING REQUEST
      //--------------------------------
      const pending = pendingRequests.current.get(key);

      if (pending) {
          if (!pending?.signal?.aborted) {
              console.log("Interviews pending reused", key);
              return pending.promise;
          }
          //-- Clear killed requst
          pendingRequests.current.delete(key);
      }

      //--------------------------------
      // CACHE MISS
      //--------------------------------

      console.log("Interviews cache MISS", key);

      const request = InterviewsQueries.getRecruiterJobOfferInterviews({
              jobId,
              companyId,
              skip,
              limit,
              statuses,
              signal 
          })
          .then(async (responseData) => {
            const mappedInterviews = await Promise.all(
              responseData.map(async (value) => {
                  const candidateId = value.candidate.id;

                  let image =
                      imageUrlCache.current.get(candidateId) ?? null;

                  if (!image) {
                      try {
                          const blob =
                              await InterviewsQueries.getCandidateImage({
                                  candidateId,
                                  interviewId: value.id,
                              });

                          image = URL.createObjectURL(blob);

                          imageUrlCache.current.set(
                              candidateId,
                              image
                          );
                      }
                      catch {
                          console.warn(
                              `Unable to fetch image for ${candidateId}`
                          );
                      }
                  }

                  return {
                      ...value,
                      id: value.id,
                      jobTitle,
                      candidate:`${value.candidate.firstName} ${value.candidate.lastName}`,
                      email: value.candidate.email,
                      scheduledAt:  formatDateSafely(value.startDate.date),
                      locationOrLink: value.url,
                      status: value.status,
                      avatarUrl: image,
                      minutes: value.minutes,
                      candidateApproval: value.candidateApproval,
                  };
              })
            );

            const result: InterviewsQueryCache = {
                data: mappedInterviews,
                hasMore: mappedInterviews.length === limit,
                fetchedAt: Date.now(),
            };

            interviewsCache.current.set(key, result);
            return result;
          })
          .finally(() => {
              pendingRequests.current.delete(key);
          });


      pendingRequests.current.set(key, {
          promise: request,
          signal,
      });

      return request;
  }), [jobId, companyId, jobTitle]);

  //------------------
  //--- Update Cache
  //------------------

  const updateInterviewInCache = (
      interviewId: string,
      update: Partial<Interview>
  ) => {
      interviewsCache.current.forEach((cache, key) => {
          const exists = cache.data.some(
              item => item.id === interviewId
          );
          if (!exists) return;

          interviewsCache.current.set(key, {
              ...cache,
              data: cache.data.map(item =>
                  item.id === interviewId
                      ? { ...item, ...update }
                      : item
              ),
          });
      });
  };

  const fetchInterviews = useCallback(
      async ({
          skip,
          mode,
          search = "",
          statuses = null,
      }: {
          skip: number;
          mode: FetchMode;
          search?: string;
          statuses?: InterviewStatusValue[] | null;
      }) => {

          //--------------------------------
          // Replace = new query
          //--------------------------------

          if (mode === "replace") {
              abortControllerRef.current?.abort();
              abortControllerRef.current = new AbortController();
              setIsLoading(true);
          }
          else{
            setIsLoadingMore(true)
          }

          const controller = abortControllerRef.current;
          const signal = controller?.signal;

          try {

              const result = await requestInterviews({
                  skip,
                  limit: PAGE_LIMIT,
                  search,
                  statuses,
                  signal,
              });

              //--------------------------------
              // Ignore obsolete response
              //--------------------------------

              if (
                  mode === "replace" &&
                  controller !== abortControllerRef.current
              ) {
                  return;
              }

              //--------------------------------
              // Replace
              //--------------------------------

              if (mode === "replace") {
                  setInterviews(result.data);
              }

              //--------------------------------
              // Append
              //--------------------------------

              else {
                  setInterviews(prev => {
                      const existingIds = new Set(
                          prev.map(item => item.id)
                      );

                      return [
                          ...prev,
                          ...result.data.filter(
                              item => !existingIds.has(item.id)
                          ),
                      ];
                  });
              }

              //--------------------------------
              // Pagination
              //--------------------------------

              setSkip(skip + result.data.length);
              setHasMore(result.hasMore);
          }
          catch (error: any) {
              if (error?.name !== "AbortError") {
                  console.error(
                      "Erreur récupération entretiens:",
                      error
                  );
              }
          }
          finally {
              if (
                  mode !== "replace" ||
                  controller === abortControllerRef.current
              ) {
                  setIsLoading(false);
              }
              else{
                setIsLoadingMore(false);
              }
          }
      },
      [requestInterviews]
  );


  //-------------------
  // - Event Handlers
  //--------------------

  // Reload the complete list when creating an interview
  const refreshInterviews = useCallback(async () => {
      invalidateInterviewsCache();

      await fetchInterviews({
          skip: 0,
          mode: "replace",
          search: debouncedSearch,
          statuses: filters.statuses,
      });
  }, [
      fetchInterviews,
      debouncedSearch,
      filters.statuses,
  ]);



  //-- Trigger Filtering
  useEffect(() => {
      fetchInterviews({
          skip: 0,
          mode: "replace",
          search: debouncedSearch,
          statuses: filters.statuses,
      });

  }, [
      debouncedSearch,
      filters.statuses,
      fetchInterviews,
  ]);

  
  // Infinite Scroll Observer
  useEffect(() => {
    const target = observerTargetRef.current;
    const container = tableContainerRef.current;
    
    if (!target || !hasMore) return;
    
    const observer = new IntersectionObserver(
      ([entry]) => {
        if (
          !entry.isIntersecting ||
          isFetchingMoreRef.current ||
          isLoadingMore ||
          !hasMore
        ) {
          return;
        }

        isFetchingMoreRef.current = true;

        try{
          fetchInterviews({
            skip,
            mode: "append",
            search: debouncedSearch,
            statuses: filters.statuses,
          });
        }
        finally{
          isFetchingMoreRef.current = false;
        }
      },
      {
        root: container,
        rootMargin: "0px 0px 100px 0px",
        threshold: 0.1,
      }
    );

    observer.observe(target);
    return () =>  observer.disconnect();
  }, [
      hasMore,
      isLoadingMore,
      skip,
      fetchInterviews,
      debouncedSearch,
      filters.statuses,
  ]);

  
  //-- Cleaning
  useEffect(()=>{
    return ()=>{
      invalidateImageCache()
    }
  },[])



  // Submit creation 
  const handleCreateInterview = useCallback(
    async (payload: CreateInterviewFormData) => {
      try{
        await InterviewsServices.createInterview({
          ...payload,
        });
      }
      catch(error){
          if(error instanceof ConcurrentInterviewsException){
            setPopup({status: "error", message: t('interviews.apiResponses.error.concurrentInterviewsFounded')})
          }
          console.log("Something went wrong while creating offer", error);
          throw error;
      }
      refreshInterviews();
    },
    [jobId, refreshInterviews]
  );




  // Action: Cancel interview
  const handleDelete = useCallback(async (id: string) => {
    setIsUpdating(id);
    try {
      setLoading({state: true, subtitle: t('interviews.message.deleteInterview')});
      await InterviewsServices.deleteInterview(id);
      
      setInterviews((prev) => prev.filter((item) => item.id !== id));
      setSkip(skip => skip > 0 ? skip - 1 : skip);
      deleteInterviewFromCache(id);

      setLoading({state: false, subtitle: t('interviews.apiResponses.success.delete')});
    } 
    catch (error) {
      if(error instanceof UnableResourceDeletion){
        setPopup({status: 'error', message: t('interviews.apiResponses.error.interviewAlreadyConfirmed')});
      }
      console.error("Erreur lors de la suppression de l'entretien", error);
      setLoading({state: false})
    }
    finally {
      setIsUpdating(null);
    }
  }, []);



  const handleCancel = useCallback(async (id: string) => {
    setIsUpdating(id);
    setLoading({state: true, subtitle: t('interviews.message.cancelIntervicew')});
    try {

        await InterviewsServices.cancelInterview(id);
        const update = {
            status: InterviewStatus.CLOSED,
        };
        setInterviews(prev =>
            prev.map(item =>
                item.id === id
                    ? { ...item, ...update }
                    : item
            )
        );
        updateInterviewInCache(id, update);
    }
    catch (error) {
        console.error(
            "Erreur lors de l'annulation de l'entretien",
            error
        );
    }
    finally {
      setIsUpdating(null);
      setLoading({state: false, subtitle: t('global.messages.error')})
    }
  }, []);


  return (
    <div className={styles.container}>
      {/* Header */}
      <div className={styles.filters}>
        <InterviewFilters 
          onFilterValueChange={({statuses}) => setFilters({statuses})}
        />
      </div>
      <div className={styles.tableCard}>
        <InterviewToolbar
          t={t}
          searchQuery={searchQuery}
          onSearchChange={setSearchQuery}
          totalCount={interviews.length}
          onOpenGenerateModal={() => {
            setModal({
              isOpen: true,
              title: 'Création d\'un entrtien',
              content: <GenerateInterviewModal
                        onClose={() => {
                          setModal(null)
                        }}
                        updateJob={updateJob}
                        onSubmit={handleCreateInterview}
                      />
            });
          }}
        />

        {/* Tableau d'entretiens */}
        <div className={styles.tableContainer} ref={tableContainerRef}>
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
              {interviews.length === 0 && !isLoading ? (
                <tr>
                  <td colSpan={6} className={styles.emptyState}>
                    Aucun entretien trouvé.
                  </td>
                </tr>
              ) : (
                interviews.map((interview) => (
                  <InterviewRow
                    key={interview.id}
                    interview={interview}
                    onDelete={handleDelete}
                    onCancel={handleCancel}
                    getInitials={getInitials}
                    isUpdating={isUpdating === interview.id}
                  />
                ))
              )}
            </tbody>
          </table>

          {/* Sentinelle Infinite Scroll */}
          <div ref={observerTargetRef} className={styles.sentinelContainer}>
            {isLoading && (
              <div className={styles.loadingSpinner}>Chargement des entretiens...</div>
            )}
            {!hasMore && interviews.length > 0 && (
              <span className={styles.endOfListText}>
                Tous les entretiens ont été chargés.
              </span>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}