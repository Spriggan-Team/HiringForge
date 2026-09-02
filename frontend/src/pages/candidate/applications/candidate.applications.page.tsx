

import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { FileText } from "lucide-react";


import { JobApplicationStatus, type ApplicationMenu, type ApplicationStatusValue, type ApplicationView, type ApplicationDetails, type CandidateApplicationStats } from "../../../features/application/application";
import CandidatesQueries from "../../../api/services/candidate/queries";

import TopBarNavigation from "../../../layout/components/navigation/topbar/topbar.navigation";
import Title from "../../../layout/components/text/title/title";
import TipTapRenderer from "../../../layout/components/editors/tiptap/tiptap.renderer";
import { Pagination } from "../../../layout/components/navigation/pagination/pagination";

import styles from "./CandidateApplicationPage.module.css"

//------------------------
//--- Query cache
//------------------------

interface QueryCacheItem{
    skip: number;
    limit: number;
    menu: ApplicationMenu;
}


interface ApplicationsCacheItem {
    data: Record<string, ApplicationView>;
    order: string[];
    total: number;
}



const PAGE_LIMIT = 15;

//-------------------
//-- PAGE Components
//--------------------


interface CandidateApplicationPageProps{}

const CandidateApplicationPage: React.FC<CandidateApplicationPageProps> = () => {
    //-- Applicatrion state
    const [candidateApplicationsStats, setApplicationsStats] = useState<CandidateApplicationStats>({
        applicationCount: 0,
        hiredCount: 0,
        interviewCount: 0,
        completedCount: 0,
        pendingCount: 0
    })

    const [currentMenu, setCurrentMenu] = useState<ApplicationMenu>("ALL");
    const [currentApplicationCollection, setCurrentApplicationCollection] = useState<{
        order: string[]
        data: Record<string, ApplicationView>
    }>();
    const [currentApplicationViewDetails, setCurrentApplicationDetailsView] = useState<ApplicationDetails | null>(null);

    //-- Pagination
    const [skip, setSkip] = useState(0);

    const [currentTotal, setCurrentTotal] = useState(0);

    //-- Memory  Cache
    const applicationsCollectionCache = useRef<Record<string, ApplicationsCacheItem>>({});// key: QueryCacheItem
    const applicationsDetailsCache = useRef<Record<string, ApplicationDetails>>({}); // key: application.id

    //-------------------------
    //-- Menus
    //-----------------------
    const applicationMenuItems = useMemo(() => [
        {
            key: "ALL" as const,
            label: "Toute",
            count: candidateApplicationsStats.applicationCount
        },
        {
            key: "PENDING" as const,
            label: "En cours",
            count: candidateApplicationsStats.pendingCount
        },
        {
            key: "INTERVIEW" as const,
            label: "Entretien",
            count: candidateApplicationsStats.interviewCount
        },
        {
            key: "COMPLETED" as const,
            label: "Terminées",
            count: candidateApplicationsStats.completedCount
        }
    ], [candidateApplicationsStats]);


    const topNavigationItems = useMemo(() =>
        applicationMenuItems.map((item) => ({
            mode: item.key,
            current: item.key === currentMenu,
            text: item.label,
            count: item.count,
            onClick: () => {
                setSkip(0);
                setCurrentMenu(item.key);
            }
        })),
    [
        applicationMenuItems,
        currentMenu
    ]);

    //-----------------------
    //--- Event Handlers
    //-----------------------

        //-- Applicatin collection
    const fetchApplicationCollection = useCallback(async () => {
        try {
            const params: QueryCacheItem = {
                skip,
                limit: PAGE_LIMIT,
                menu: currentMenu,
            };

            const cacheKey = JSON.stringify(params);
            const cache = applicationsCollectionCache.current;

            const cached = cache[cacheKey];

            if (cached) {
                setCurrentApplicationCollection({ order: cached.order, data: cached.data });
                setCurrentTotal(cached.total);
                return cached.data;
            }

            const { data, total } = await CandidatesQueries.getMyApplications(params);
            const [order, record] = data.reduce<
                [string[], Record<string, ApplicationView>]
            >(
                (acc, current) => {
                    acc[0].push(current.id);
                    acc[1][current.id] = current;

                    return acc;
                },
                [[], {}]
            );

            cache[cacheKey] = {
                order,
                data: record,
                total,
            };
            setCurrentApplicationCollection(cache[cacheKey]);
            setCurrentTotal(total);

            return data;
        }
        catch (error) {
            console.warn(
                "Something went wrong while fetching:",
                error
            );

            return [];
        }
    }, [skip, currentMenu]);



        //-- Applications stats
    const fetchApplicationStats = useCallback(async()=>{
        try{
            const results = await CandidatesQueries.getApplicationsSats();
            setApplicationsStats(results);
        }
        catch(error){
            console.warn("Something went wrong : ", error)
        }
    },[]);

        //-- Feth details
    const fetchApplicationDetails = useCallback(
        async (applicationId: string) => {
            try {
                const detailsCache = applicationsDetailsCache.current;

                if (detailsCache[applicationId]) {
                    setCurrentApplicationDetailsView(
                        detailsCache[applicationId]
                    );
                    return;
                }

                const data = await CandidatesQueries.getApplicationViewDetails({ applicationId });
                const application =  currentApplicationCollection?.data[applicationId];

                if (!application) {
                    return;
                }

                const mapped: ApplicationDetails = {
                    ...application,
                    ...data,
                };

                detailsCache[applicationId] = mapped;
                setCurrentApplicationDetailsView(mapped);
            }
            catch (error) {
                console.warn(
                    "Something went wrong while fetching application details:",
                    error
                );
            }
        },
        [currentApplicationCollection]
    );
    

    //------------------
    // Effects
    //------------------

    useEffect(() => {
        const fetchData = async () => {
            const applications = await fetchApplicationCollection();

            const collections = Object.values(applications)
            if (collections.length > 0) {
                await fetchApplicationDetails(collections[0].id);
            }
            else {
                setCurrentApplicationDetailsView(null);
            }
        };

        fetchData();
    }, [
        fetchApplicationCollection,
        fetchApplicationDetails
    ]);


    //-- Init (Require Once)
    useEffect(()=>{
        fetchApplicationStats();
    },[fetchApplicationStats])


    return (
        <div className={styles.container}>
            {/** Components Header */}
            <div className={styles.headers}>
                <Title title="Mes applications" />
                <TopBarNavigation
                    options={topNavigationItems}
                />
            </div>

            {/** Body (My applications )*/}
            <div className={styles.container}>
                {/** COLLECTIONS VIEWS */}
                <div className={styles.collections}>
                    <div className={styles.appDetails}>
                        {
                            currentApplicationCollection && currentApplicationCollection.order.length > 0 ?
                                currentApplicationCollection.order.map((id, index)=>{
                                    return (
                                        <ApplicationCard
                                            key={`${id}-${index}`}
                                            application={currentApplicationCollection.data[id]}
                                            onClick={(id)=>{
                                                fetchApplicationDetails(id)
                                            }}
                                        />
                                    )
                                })
                                : (
                                    <div>
                                        <span>Aucune application trouvé</span>
                                    </div>
                                )
                        }
                    </div>
                    <div className={styles.footer}>
                        <Pagination
                            currentPage={Math.floor(skip / PAGE_LIMIT)}
                            onChange={(page)=>{
                                setSkip(page * PAGE_LIMIT);
                            }}
                            totalPages={Math.ceil(currentTotal / PAGE_LIMIT)}
                        />
                    </div>
                </div>

                {/** CONTENT DETAILS */}
                <div className={styles.detials}>
                    {
                        currentApplicationViewDetails && (
                            <ApplicationContent details={currentApplicationViewDetails} /> 
                        )
                    }
                </div>
            </div>
        </div>
    );
}


export default CandidateApplicationPage;


//---------------------------------------
//     Compenonts Seggragation/Details
//---------------------------------------

//--------------
// COLLECTION ITEMS
//----------------

interface ApplicationCardProps{
    application: ApplicationView;
    onClick: (id: string)=>void;
}

const ApplicationCard: React.FC<ApplicationCardProps> = ({
    application,
    onClick
}) => {
    return (
        <div 
            className={styles.appCard}
            onClick={()=>{
                onClick(application.id)
            }}
        >
            <div className={styles.companyLogo}>
                <img src={application.company.logo ?? ""} alt="" />
            </div>        
            <div className={styles.cardContent}>
                <span>{application.job.title}</span>
                <img
                    src={application.job.image}
                    alt={application.job.title}
                />
                <Badge text={application.status}  />
            </div>
        </div>
    );
}
 


//-----------------------------
//---------- ApplicationContent
//-----------------------------

interface ApplicationContentProps{
    details: ApplicationDetails;
}

const ApplicationContent: React.FC<ApplicationContentProps> = ({
    details
}) => {
    const locationToString = (location?: {
        city?: string;
        street?: string;
        postalCode?: string;
        country?: string;
    }): string => {
        return Object.values(location ?? {})
            .filter(Boolean)
            .join(", ");
    };

    const getApplicationStatusClass = (
        status: ApplicationStatusValue,
        step: "pending" | "interview" | "completion"
    ): string => {

        const interviewStatuses: ApplicationStatusValue[] = [
            JobApplicationStatus.INTERVIEW_SCHEDULED,
            JobApplicationStatus.IN_INTERVIEW
        ];

        const completedStatuses: ApplicationStatusValue[] = [
            JobApplicationStatus.OFFER_ACCEPTED,
            JobApplicationStatus.OFFER_DECLINED
        ];

        if (
            step === "interview" &&
            interviewStatuses.includes(status)
        ) {
            return styles.interview;
        }

        if (
            step === "completion" &&
            completedStatuses.includes(status)
        ) {
            return styles.accepted;
        }

        if (
            step === "pending" &&
            !interviewStatuses.includes(status) &&
            !completedStatuses.includes(status)
        ) {
            return styles.pending;
        }

        return "";
    }; 

    return (
        <div className={styles.details}>
            <span>DETAILS DE LA CANDIDATURE</span>
            <div className={styles.overview}>
                <span className={styles.title}>{details.job.title}</span>
                <div className={styles.contract}>
                    <FileText />
                    <span> {details.contractType}</span>
                </div>
                <div className={styles.location}>
                    <span>{locationToString(details.location)}</span>
                </div>
            </div>
            <div className={styles.applicationsStatus}>
                <span>Candidature envoyée : </span>
                <div>
                    <span className={`${getApplicationStatusClass(details.status, "pending")}`} >En cours</span>
                    <span className={`${getApplicationStatusClass(details.status, "interview")}`}>Entrerien</span>
                    <span  className={`${getApplicationStatusClass(details.status, "completion")}`}>Décision</span>
                </div>
            </div>
            <div className={styles.tiptap}>
                <span className={styles.title}>INFORMATION DE L'OFFRE</span>
                <TipTapRenderer  content={details.content}  />
            </div>
            <div className={styles.skills}>
                {
                    (details.skills ?? []).map((item, index)=>(
                        <div key={index} className={styles.skill}>
                            <span>{item.name}</span>
                        </div>
                    ))
                }
            </div>
        </div>
    );
}



//-----------------------------
//---------- Badge
//-----------------------------


interface BadgeProps{
    text: string;
}


const Badge: React.FC<BadgeProps> = ({text}) => {
    return (
        <div className={styles.text} >{text}</div>
    );
}
 
