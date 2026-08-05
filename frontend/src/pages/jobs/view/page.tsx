
import { useParams } from "react-router-dom";
import { useCallback, useEffect, useMemo, useState } from "react";
import { useTranslation } from "react-i18next";

//-- Services
import { renderStatus } from "../utils/utils";
import RouteScheme from "../../../route.scheme";
import { useAppContext } from "../../../hooks/context";
import { jobStatusStyles } from "../../../context/styles";
import {  jobsViewData } from "../../../core/mock/job.data";
import type { JobView } from "../../../features/jobs/JobOffer";
import type { EntityAction } from "../../../features/shared/global";
import JobQueries from "../../../api/services/jobs/queries";

//-- Custom components
import Title from "../../../layout/components/text/title/title";
import JobOverviewSection from "./components/overview/job.overview.section";
import BreadCrumbs from "../../../layout/components/navigation/auth/link/bread.crumbs";
import SimpleButton from "../../../layout/components/buttons/simple/simple.button";
import TopBarNavigation from "../../../layout/components/navigation/topbar/topbar.navigation";
import InfoPill, { type InfoPillProps } from "../../../layout/components/badges/pill/info.pill";
import MenuDrawer, { MenuDrawerBody, MenuDrawerItem, MenuDrawerTrigger } from "../../../layout/components/menu/drawer/menu.drawer";
import CandidatesViewSection from "./components/candidates/application.table.section";

//-- SVG Components
import EditSVG from "/src/assets/svg/menu/edit-2-svgrepo-com.svg"
import VerticalOptionsSVGComponent from "/src/assets/svg/menu/options-vertical-svgrepo-com.svg"

//-- CSS styles
import styles from "./PrivateJobViewPage.module.css"
import InterviewsSection from "./components/interviews/interviews.section";
import OffersSection from "./components/offer/offer.section";
import { JobStatisticsSection } from "./components/stats/job.statistics.sections";



//-- Types

export interface UserPageSinglePageProps{}


type ViewModeTypes = 
    | "overview"
    | "interviews"
    | "candidates"
    | "offers"
    | "statistics"



const PrivateJobViewPage: React.FC<UserPageSinglePageProps> = () => {
    const { t } = useTranslation()
    const { setNavbar } = useAppContext();

    const { id } = useParams(); 
    const [currentJob, setCurrentJob] = useState<JobView | null>(null);


    /** -- Initialize data --- */
    const [isLoading, setIsLoading] = useState<boolean>(true);
    const [error, setError] = useState<string | null>(null);

    const loadJobOffer = useCallback(async (jobId: string) => {
        if (!jobId) {
            console.warn('Job ID is required for handling this route');
            return;
        }

        try {
            setIsLoading(true);
            setError(null);

            const data = await JobQueries.getJobView(jobId);
            setCurrentJob(data);
        }
        catch (err) {
            console.error('Something went wrong while retrieving job:', err);
            setError(t('jobs.messages.failedToLoadJob'));
        }
        finally {
            setIsLoading(false);
        }
    }, []);


    useEffect(() => {
        if (id) {
            loadJobOffer(id);
        }
    }, [id, loadJobOffer]);


    /** -- States -- */
    const [action, setAction] = useState<EntityAction>(null);
    const [viewMenu, setViewMenu] = useState<ViewModeTypes>("overview");


    //-- Top Nav options --
    const options = useMemo(() => {
        if(!currentJob)
            return [];
        return([
            {
                mode: "overview" as ViewModeTypes,
                text: t("jobs.overview.title"),
                current: viewMenu === "overview",
                onClick: () => setViewMenu("overview"),
            },
            {
                mode: "candidates" as ViewModeTypes,
                current: viewMenu === "candidates",
                count: currentJob.cardinal?.candidates ?? 0,
                onClick: () => setViewMenu("candidates"),
                text: t("global.candidate.candidateLabel", {
                    count: currentJob.cardinal?.candidates ?? 0,
                }),
            },
            {
                mode: "interviews" as ViewModeTypes,
                current: viewMenu === "interviews",
                count: currentJob.cardinal?.interviews ?? 0,
                onClick: () => setViewMenu("interviews"),
                text: t("global.interview.interviewLabel", {
                    count: currentJob.cardinal?.interviews ?? 0,
                }),
            },
            {
                mode: "offers" as ViewModeTypes,
                current: viewMenu === "offers",
                count: currentJob.cardinal?.offers ?? 0,
                onClick: () => setViewMenu("offers"),
                text: t("global.offer.offerLabel", {
                    count: currentJob.cardinal?.offers ?? 0,
                }),
            },
            {
                mode: "statistics" as ViewModeTypes,
                current: viewMenu === "statistics",
                onClick: () => setViewMenu("statistics"),
                text: t("global.statistics.statistics_other"),
            },
        ]);
    }, [viewMenu, currentJob?.cardinal, t]);


    /**-- Styles & design --- */
    const [infoPillSettings, setInfoPillSettings] = useState<InfoPillProps>({ text: "" });


    useEffect(()=>{
        if(!currentJob)
            return;
        const computed = getComputedStyle(document.documentElement);

        const status = currentJob?.activityStatus ?? currentJob?.publicationStatus;
        const text = renderStatus(t, status);

        const txtColor = computed.getPropertyValue(jobStatusStyles[status]?.txtColor);
        const backgroundColor = computed.getPropertyValue(jobStatusStyles[status]?.bgColor);

        setInfoPillSettings({ 
            text,
            txtColor,
            backgroundColor,
            borderRadius: 10
        })

    },[jobsViewData, currentJob])


    /** Global Side effects */
    useEffect(()=>{
        if(!currentJob)
            return;
        console.log({currentJob})
        
        //--Navbar
        const linkData = [
            { route: RouteScheme.userJobs, text: t("jobs.jobs"), current: false },
            { route: RouteScheme.createJob, text: currentJob?.title, current: true }
        ];

        setNavbar({
            title: null,
            description: (
                <BreadCrumbs
                    overlayColor="#4338CA"
                    links={linkData} 
                />
            )
        });

        return ()=>{
            setNavbar(null);
        };
    },[setNavbar, currentJob])


    /** -- RENDER --- */

    if (isLoading) return <div>Chargement de l'offre...</div>;
    if (error) return <div>{error}</div>;
    if (!currentJob) return <div>Aucune offre trouvée.</div>;

    return (
        <main className={styles.main}>
            {/** PANNEL (prensetation) */}
            <div className={styles.pannel}>
                {/** LEFT */}
                <div className={styles.left}>
                    <Title title={currentJob.title} fontSize="25px"/>
                    <InfoPill  {...infoPillSettings} />
                </div>
                
                {/** RIGHT */}
                <div className={styles.right}>
                    <SimpleButton>
                        <div className={styles.editBtn}>
                            <EditSVG width={15} height={15} className={styles.editsvg}/>
                            <span>{t("jobs.modifyJob")}</span>
                        </div>
                    </SimpleButton>

                    <MenuDrawer
                        onChange={(value)=> setAction(value)}
                    >
                        <MenuDrawerTrigger >
                            <VerticalOptionsSVGComponent
                                className={styles.optionssvg} 
                                width={15} height={15}
                            />
                        </MenuDrawerTrigger>

                        <MenuDrawerBody
                            position="initial-absolute"
                        >
                            <MenuDrawerItem value="edit">{t("global.actions.edit")}</MenuDrawerItem>
                            <MenuDrawerItem value="duplicate">{t("global.actions.duplicate")}</MenuDrawerItem>
                            <MenuDrawerItem value="delete">{t("global.actions.delete")}</MenuDrawerItem>
                        </MenuDrawerBody>
                    </MenuDrawer>
                    
                </div>
            </div>

            {/** View Mode (Naviagtion)*/}
            <div className={styles.viewMode}>
                <TopBarNavigation
                    options={options}
                />
            </div>

            {/** WIDGET (main content) */}
            <div className={styles.widget}>
                {
                    viewMenu === "overview" ? 
                        (<JobOverviewSection jobView={currentJob} />)
                    : viewMenu === "candidates" ? 
                        (<CandidatesViewSection jobId={currentJob.id} />)
                    : viewMenu == "interviews" ?
                        (<InterviewsSection  jobId={currentJob.id} />)
                    : viewMenu == "offers" ?
                        (<OffersSection />)
                    : viewMenu === "statistics" ?
                        (<JobStatisticsSection />)
                    : null
                }
            </div>
            
        </main>
    );
}
 
export default PrivateJobViewPage;