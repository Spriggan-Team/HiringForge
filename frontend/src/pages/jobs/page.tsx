

import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useTranslation } from "react-i18next";


//-- Services
import RouteScheme from "../../route.scheme";
import { useAppContext, useCurrentUser } from "../../hooks/context";
import JobQueries from "../../api/services/jobs/queries";
import UserJobContextProvider, { useUserJobContext } from "../../context/user.job.context";
import type {  CompleteJobView, JobEngagementMetrics, JobSummary, JobView } from "../../features/jobs/JobOffer";


//-- Custom Components
import CurrentJob from "./user/components/currentJob/current.job";
import type { FilterState } from "../../features/shared/global";
import JobsSection from "./user/components/jobs/jobs.section";
import BasicInput from "../../layout/components/form/input/basic.input";
import BrandButton from "../../layout/components/buttons/brand.button";
import JobMapFilters from "./user/components/filters/job.map.filter";
import { Pagination } from "../../layout/components/navigation/pagination/pagination";


//-- SVG components
import SearchSVGComponent from "/src/assets/svg/menu/search-svgrepo-com.svg?react"
import LocationSVGComponent from "/src/assets/svg/location/location-svgrepo-com.svg?react"
import AddSVGComponent from "/src/assets/svg/add/add-svgrepo-com.svg?react"


//-- CSS styles 
import styles from "./UserJobPage.module.css"
import { useDebounce } from "../../hooks/timer";


interface UserJobsPageProps{}


const UserJobsPage: React.FC<UserJobsPageProps> = () => {
    return (
        <UserJobContextProvider>
            <UserJobsPageContent />
        </UserJobContextProvider>
    );
}
 
export default UserJobsPage;


//----------------
//-- Page Content 
//------------------

const PAGE_SIZE = 10;


const DEFAULT_FILTERS: FilterState = {
    publishedState: { draft: false, closed: false, published: true },
    offerState: { active: true, pending: false, inactive: true },
    salary: 0,
    candidateCount: 0,
    searchText: '',
    searchAddress: '',
};

//-----------------
//-- Cache
//-----------------

interface JobsQueryCache {
    data: JobSummary[];
    total: number;
    fetchedAt: number;
}

const getJobsCacheKey = (
    page: number,
    filters: FilterState
): string => {
    return JSON.stringify({
        page,
        publishedState: filters.publishedState,
        offerState: filters.offerState,
        salary: filters.salary,
        candidateCount: filters.candidateCount,
        searchText: filters.searchText.trim().toLowerCase(),
        searchAddress: filters.searchAddress.trim().toLowerCase(),
    });
};


const CACHE_DURATION = 1000 * 60 * 5; // 5 min


//----------------
//--- Components
//----------------

const UserJobsPageContent: React.FC<{}> = () => {
    const { t } = useTranslation();
    const navigate = useNavigate();

    const user = useCurrentUser();
    const { setViewedJob  } = useUserJobContext();

    // -- Memory cache
    const jobViewCache = useRef<Map<string, CompleteJobView>>(
        new Map()
    );
    const jobsQueryCache = useRef<Map<string, JobsQueryCache>>(
        new Map()
    );
    const pendingRequests = useRef<Map<string, Promise<JobsQueryCache>>>(new Map());

    // -- Listes & Pagination
    const [currentPage, setCurrentPage] = useState<number>(0);
    const [isLoading, setIsLoading] = useState<boolean>(false);
    const [totalJobCount, setTotalJobCount] = useState<number>(0);
    const [jobDataSummary, setJobDataSummary] = useState<JobSummary[]>([]);

    // -- Selected Offre
    const [currentJobId, setCurrentJobId] = useState<string | null>(null);
    const [currentJobView, setCurrentJobView] = useState<CompleteJobView  | null>(null);
    const [isJobLoading, setIsJobLoading] = useState<boolean>(false);

    // -- Filtres & Search
    const [filters, setFilters] = useState<FilterState>(DEFAULT_FILTERS);
    const debouncedFilters = useDebounce(
        filters,
        400
    );

    const totalPages = useMemo(
        () => Math.ceil(totalJobCount / PAGE_SIZE),
        [totalJobCount]
    );


    //-- Cache invalidation (to be called after a modification/edit)
    const invalidateJobViewCache = (jobId?: string) => {
        if (jobId) {
            jobViewCache.current.delete(jobId);
        } else {
            jobViewCache.current.clear();
        }
    };

    /** Handle concurrent requests */
    const requestJobs = async (
        page: number,
        filters: FilterState
    ): Promise<JobsQueryCache> => {
        const key = getJobsCacheKey(page, filters);

        // Cache
        const cached = jobsQueryCache.current.get(key);

        if (
            cached &&
            Date.now() - cached.fetchedAt < CACHE_DURATION
        ) {
            return cached;
        }

        // Request already running
        const pending = pendingRequests.current.get(key);

        if (pending) {
            return pending;
        }

        // Create request
        const request = Promise.all([
            JobQueries.getJobsSummary(
                PAGE_SIZE,
                page * PAGE_SIZE,
                filters
            ),
            JobQueries.countJobOffers(filters),
        ])
            .then(([data, total]) => {
                const result = {
                    data,
                    total,
                    fetchedAt: Date.now(),
                };

                jobsQueryCache.current.set(key, result);

                return result;
            })
            .finally(() => {
                pendingRequests.current.delete(key);
            });

        pendingRequests.current.set(key, request);

        return request;
    };


    //-- Load data
    const fetchJobs = useCallback(async (page: number, currentFilters: FilterState) => {
        if (!user?.id) return;
        
        try {
            setIsLoading(true);

            const result = await requestJobs(
                page,
                currentFilters
            );

            setJobDataSummary(result.data);
            setTotalJobCount(result.total);

            setCurrentJobId((prevId) => {
                if (result.data.length === 0) {
                    return null;
                }

                return result.data.some(
                    job => job.id === prevId
                )
                    ? prevId
                    : result.data[0].id;
            });
        }
        catch (error) {
            console.error(
                "Error job request retrieval",
                error
            );
        }
        finally {
            setIsLoading(false);
        }        
    }, [user?.id]);



    // Filter Debounce Time
    useEffect(() => {
        setCurrentPage(0);
        fetchJobs(0, debouncedFilters);
    }, [debouncedFilters, fetchJobs]);



    //-- Pagination
    const handlePageChange = (newPage: number) => {
        setCurrentPage(newPage);
        fetchJobs(newPage, filters);
    };


    //--- Load Jobs Details
    useEffect(() => {
        if (!currentJobId) return;

        //-- Reteive metrics from job summary
        const summaryItem = jobDataSummary.find((item) => item.id === currentJobId);

        //-- Verify cache
        if (jobViewCache.current.has(currentJobId)) {
            const cachedView = jobViewCache.current.get(currentJobId)!;
            
            setCurrentJobView({
                ...cachedView,
                applications: summaryItem?.cardinal.candidates ?? cachedView.applications,
                views:  cachedView.views ?? 0,
                cardinal: summaryItem?.cardinal ?? cachedView.cardinal,
            });

            setIsJobLoading(false);
            return;
        }

        //-- Fallback api call
        let isSubscribed = true;
        setIsJobLoading(true);

        JobQueries.getJobView(currentJobId)
            .then((view) => {
                if (isSubscribed) {
                    //-- Construct metrics

                    const mergedView: CompleteJobView = {
                        ...view,
                        applications: summaryItem?.cardinal.candidates ?? (view as any).applications ?? 0,
                        views:  (view as any).views ?? 0,
                        cardinal: summaryItem?.cardinal ?? view.cardinal,
                    };

                    // Save in cache
                    jobViewCache.current.set(currentJobId, mergedView);
                    setCurrentJobView(mergedView);

                    console.log("COMPLETE JOB OFFER : ", mergedView)
                }
            })
            .catch((err) => {
                console.error(`Erreur chargement offre ${currentJobId}:`, err);
            })
            .finally(() => {
                if (isSubscribed) 
                    setIsJobLoading(false);
            });

        return () => {
            isSubscribed = false;
        };
    }, [currentJobId]);



    //-- Handlers
    const handleSearchTextChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setFilters((prev) => ({ ...prev, searchText: e.target.value }));
    };


    //-- Address search
    const handleSearchAddressChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setFilters((prev) => ({ ...prev, searchAddress: e.target.value }));
    };


    return (
        <main className={styles.container}>
            <div className={styles.header}>
                <div className={styles.searchInputsSection}>
                    <BasicInput
                        backgroundColor="white"
                        svg={SearchSVGComponent}
                        className={styles.input}
                        enableFocusWithinDefaultDesign={false}
                        placeholder={t("jobs.inputs.searchJob.placeholder")}
                        value={filters.searchText}
                        onChange={handleSearchTextChange}
                    />
                    <BasicInput
                        backgroundColor="white"
                        svg={LocationSVGComponent}
                        className={styles.input}
                        enableFocusWithinDefaultDesign={false}
                        placeholder={t("jobs.inputs.searchAddress.placeholder")}
                        value={filters.searchAddress}
                        onChange={handleSearchAddressChange}
                    />
                </div>
                <BrandButton
                    svg={AddSVGComponent}
                    btnClassName={styles.createJob}
                    text={t("jobs.buttons.create")}
                    onClick={() => navigate(RouteScheme.createJob)}
                />
            </div>

            <div className={styles.body}>
                <div className={styles.filter}>
                    <JobMapFilters
                        filters={filters}
                        onFilterChange={(newFilters) => setFilters(newFilters)}
                    />
                </div>

                <div className={styles.jobs}>
                    {isLoading ? (
                        <p>Chargement des offres...</p>
                    ) : (
                        <JobsSection
                            data={jobDataSummary}
                            onClick={(id) => setCurrentJobId(id)}
                        />
                    )}
                </div>

                <div className={styles.selectedJob}>
                    {isJobLoading ? (
                        <p>Chargement du détail...</p>
                    ) : (
                        currentJobView && (
                            <CurrentJob
                                job={currentJobView}
                                onInvalidateCache={invalidateJobViewCache}
                                onClick={(id) => {
                                    setViewedJob(currentJobView);
                                    navigate(RouteScheme.userJobView.replace(':id', id));
                                }}
                            />
                        )
                    )}
                </div>
            </div>

            <div className={styles.footer}>
                <Pagination
                    currentPage={currentPage}
                    totalPages={totalPages}
                    onChange={handlePageChange}
                />
            </div>
        </main>
    );
};
 



