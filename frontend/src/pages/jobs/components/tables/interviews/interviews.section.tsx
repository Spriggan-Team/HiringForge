import { format } from "date-fns";
import { useTranslation } from "react-i18next";
import React, { useCallback, useEffect, useMemo, useRef, useState } from "react";

import { useAppContext } from "../../../../../hooks/context";
import { useDebounce } from "../../../../../hooks/timer";
import InterviewsQueries from "../../../../../api/services/interviews/queries";
import InterviewsServices from "../../../../../api/services/interviews/command";
import JobQueries from "../../../../../api/services/jobs/queries";

import type { CandidateLightModel } from "../../../../../features/candidates/candidates";
import { INTERVIEW_STATUSES, type CreateInterviewFormData } from "../../../../../features/interviews/interviews";


import InterviewRow from "./components/interviews.table.row";
import { InterviewToolbar } from "./components/interview.toolbar";
import { GenerateInterviewModal } from "./components/generate.interview.modal";


import styles from "./Interviews.module.css";



const STATUS_OPTIONS= INTERVIEW_STATUSES;



export interface Interview {
  id: string;
  jobTitle: string;
  candidate: string;
  email: string;
  scheduledAt: string;
  locationOrLink?: string;
  status: string;
  avatarUrl?: string;
}



export interface InterviewsSectionProps {
  job: {
    id: string;
    title: string;
  };
  companyId?: string;
}



export default function InterviewsSection({
  job: { id: jobId, title: jobTitle },
  companyId,
}: InterviewsSectionProps) {
  const { t } = useTranslation();
  const { setModal } = useAppContext() ;

  const [interviews, setInterviews] = useState<Interview[]>([]);
  const [isUpdating, setIsUpdating] = useState<string | null>(null);

  // Recherche
  const [searchQuery, setSearchQuery] = useState("");
  const debouncedSearch = useDebounce(searchQuery, 350);

  // Pagination & Infinite Scroll
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const isLoadingRef = useRef(false);

  // Cache & Refs
  const loadedPagesRef = useRef<Set<number>>(new Set());
  const tableContainerRef = useRef<HTMLDivElement | null>(null);
  const observerTargetRef = useRef<HTMLDivElement | null>(null);

  const PAGE_LIMIT = 10;

  const getInitials = useCallback((name: string) => {
    if (!name) return "";
    return name
      .split(" ")
      .map((n) => n[0])
      .join("")
      .toUpperCase()
      .slice(0, 2);
  }, []);

  // --- Fetch Candidates
  const fetchCandidatesApi = useCallback(
    async (targetJobId: string, search: string, limit: number): Promise<CandidateLightModel[]> => {
      return await JobQueries.getJobCandidates({
        jobId: targetJobId,
        search,
        limit,
      });
    },
    []
  );

  // --- FETCH ENTRETIENS ---
  const fetchInterviewsPage = useCallback(
    async (pageToFetch: number) => {
      if (loadedPagesRef.current.has(pageToFetch) || isLoadingRef.current) return;

      try {
        isLoadingRef.current = true;
        setIsLoadingMore(true);

        const responseData = await InterviewsQueries.getRecruiterJobOfferInterviews({
          jobId,
          skip: pageToFetch,
          limit: PAGE_LIMIT,
        });

        if (!responseData || responseData.length === 0) {
          setHasMore(false);
          loadedPagesRef.current.add(pageToFetch);
          return;
        }

        const mappedInterviews: Interview[] = responseData.map((value: any) => ({
          id: value.id,
          jobTitle: jobTitle,
          candidate: `${value.candidate.firstName} ${value.candidate.lastName}`,
          email: value.candidate.email,
          scheduledAt: format(new Date(value.startDate), "yyyy-MM-dd'T'HH:mm:ss"),
          locationOrLink: value.url,
          status: value.status,
          avatarUrl: value.candidate.avatarUrl,
        }));

        loadedPagesRef.current.add(pageToFetch);

        setInterviews((prev) => {
          const combined = [...prev, ...mappedInterviews];
          const uniqueMap = new Map(combined.map((item) => [item.id, item]));
          return Array.from(uniqueMap.values());
        });

        if (responseData.length < PAGE_LIMIT) {
          setHasMore(false);
        }
      }
      catch (error) {
        console.error("Erreur lors de la récupération des entretiens :", error);
      }
      finally {
        isLoadingRef.current = false;
        setIsLoadingMore(false);
      }
    },
    [jobId, jobTitle]
  );


  // Reload the complete list when creating an interview
  const refreshInterviews = useCallback(() => {
    loadedPagesRef.current.clear();
    setInterviews([]);
    setHasMore(true);
    setPage(1);
    fetchInterviewsPage(1);
  }, [fetchInterviewsPage]);



  // Trigger pagination
  useEffect(() => {
    fetchInterviewsPage(page);
  }, [page, fetchInterviewsPage]);

  
  // Infinite Scroll Observer
  useEffect(() => {
    const target = observerTargetRef.current;
    const container = tableContainerRef.current;

    if (!target || !hasMore || isLoadingMore) 
      return;

    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting && hasMore && !isLoadingRef.current) {
          setPage((prevPage) => prevPage + 1);
        }
      },
      {
        root: container,
        rootMargin: "0px 0px 100px 0px",
        threshold: 0.1,
      }
    );

    observer.observe(target);

    return () => {
      observer.disconnect();
    };
  }, [hasMore, isLoadingMore]);



  // Locally Filter Search
  const filteredInterviews = useMemo(() => {
    if (!debouncedSearch.trim()) return interviews;
    const query = debouncedSearch.toLowerCase();

    return interviews.filter(
      (item) =>
        item.candidate.toLowerCase().includes(query) ||
        item.email.toLowerCase().includes(query) ||
        item.jobTitle.toLowerCase().includes(query)
    );
  }, [interviews, debouncedSearch]);



  // Submit creation 
  const handleCreateInterview = useCallback(
    async (payload: CreateInterviewFormData) => {
      await InterviewsServices.createInterview({
        ...payload,
        jobId,
      });
      refreshInterviews();
    },
    [jobId, refreshInterviews]
  );


  // Action: Cancel interview
  const handleCancel = useCallback(async (id: string) => {
    setIsUpdating(id);
    try {
      await InterviewsServices.cancelInterview(id);
      setInterviews((prev) =>
        prev.map((item) =>
          item.id === id ? { ...item, status: "cancel" } : item
        )
      );
    }
    catch (error) {
      console.error("Erreur lors de l'annulation de l'entretien :", error);
    }
    finally {
      setIsUpdating(null);
    }
  }, []);

  


  return (
    <div className={styles.tableCard}>
      {/* Header */}
      <InterviewToolbar
        t={t}
        searchQuery={searchQuery}
        onSearchChange={setSearchQuery}
        totalCount={filteredInterviews.length}
        onOpenGenerateModal={() => {
          setModal({
            isOpen: true,
            title: 'Création d\'un entrtien',
            content: <GenerateInterviewModal
              jobId={jobId}
              onClose={() => {
                setModal(null)
              }}
              onSubmit={handleCreateInterview}
              fetchCandidatesApi={fetchCandidatesApi}
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
            {filteredInterviews.length === 0 && !isLoadingMore ? (
              <tr>
                <td colSpan={6} className={styles.emptyState}>
                  Aucun entretien trouvé.
                </td>
              </tr>
            ) : (
              filteredInterviews.map((interview) => (
                <InterviewRow
                  key={interview.id}
                  interview={interview}
                  isUpdating={isUpdating === interview.id}
                  onCancel={handleCancel}
                  getInitials={getInitials}
                />
              ))
            )}
          </tbody>
        </table>

        {/* Sentinelle Infinite Scroll */}
        <div ref={observerTargetRef} className={styles.sentinelContainer}>
          {isLoadingMore && (
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
  );
}