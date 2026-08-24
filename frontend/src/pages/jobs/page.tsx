
import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useTranslation } from "react-i18next";

//-- Services
import RouteScheme from "../../route.scheme";
import { useAppContext, useCurrentUser } from "../../hooks/context";
import JobQueries from "../../api/services/jobs/queries";
import type {  JobSummary, JobView } from "../../features/jobs/JobOffer";


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




const PAGE_SIZE = 10;


const DEFAULT_FILTERS: FilterState = {
    publishedState: { draft: false, closed: false, published: true },
    offerState: { active: true, pending: false, inactive: true },
    salary: 0,
    candidateCount: 0,
    searchText: '',
    searchAddress: '',
};



const UserJobsPage: React.FC<{}> = () => {
    const { t } = useTranslation();
    const navigate = useNavigate();
    const user = useCurrentUser();

    // -- Memory cache
    const jobViewCache = useRef<Map<string, JobView>>(new Map());

    // -- Listes & Pagination
    const [jobDataSummary, setJobDataSummary] = useState<JobSummary[]>([]);
    const [currentPage, setCurrentPage] = useState<number>(0);
    const [isLoading, setIsLoading] = useState<boolean>(false);
    const [totalJobCount, setTotalJobCount] = useState<number>(0);

    // -- Selected Offre
    const [currentJobId, setCurrentJobId] = useState<string | null>(null);
    const [currentJobView, setCurrentJobView] = useState<JobView | null>(null);
    const [isJobLoading, setIsJobLoading] = useState<boolean>(false);

    // -- Filtres & Search
    const [filters, setFilters] = useState<FilterState>(DEFAULT_FILTERS);

    const totalPages = useMemo(
        () => Math.ceil(totalJobCount / PAGE_SIZE),
        [totalJobCount]
    );


    //-- Loading
    const fetchJobs = useCallback(async (page: number, currentFilters: FilterState) => {
        if (!user?.id) return;
        
        setIsLoading(true);
        try {
            const skip = page * PAGE_SIZE;

            const [data, total] = await Promise.all([
                JobQueries.getJobsSummary(PAGE_SIZE, skip, currentFilters),
                JobQueries.countJobOffers(currentFilters)
            ]);

            setJobDataSummary(data);
            setTotalJobCount(total);

            //  Auto-select if no offer is selected
            if (data.length > 0 && !currentJobId) {
                setCurrentJobId(data[0].id);
            }
        }
        catch (error) {
            console.error("Erreur récupération offres:", error);
        }
        finally {
            setIsLoading(false);
        }
    }, [user?.id, currentJobId]);


    // Filter Debounce
    useEffect(() => {
        const timer = setTimeout(() => {
            setCurrentPage(0);
            fetchJobs(0, filters);
        }, 400);

        return () => clearTimeout(timer);
    }, [filters, fetchJobs]);



    const handlePageChange = (newPage: number) => {
        setCurrentPage(newPage);
        fetchJobs(newPage, filters);
    };

    //--- Load Details
    useEffect(() => {
        if (!currentJobId) return;

        //-- Verify cache
        if (jobViewCache.current.has(currentJobId)) {
            setCurrentJobView(jobViewCache.current.get(currentJobId)!);
            setIsJobLoading(false);
            return;
        }

        //-- Fallback api call
        let isSubscribed = true;
        setIsJobLoading(true);

        JobQueries.getJobView(currentJobId)
            .then((view) => {
                if (isSubscribed) {
                    // Save in cache
                    jobViewCache.current.set(currentJobId, view);
                    setCurrentJobView(view);
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

    //-- Cache invalidation (to be called after a modification/edit)
    const invalidateJobCache = (jobId?: string) => {
        if (jobId) {
            jobViewCache.current.delete(jobId);
        } else {
            jobViewCache.current.clear();
        }
    };

    // Handlers
    const handleSearchTextChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setFilters((prev) => ({ ...prev, searchText: e.target.value }));
    };


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
                                onClick={(id) => {
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
 
export default UserJobsPage;



