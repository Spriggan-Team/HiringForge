
import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";

//-- Services
import { renderStatus } from "../utils/utils";
import RouteScheme from "../../../route.scheme";
import { useAppContext } from "../../../hooks/context";
import { jobStatusStyles } from "../../../context/styles";
import {  jobsViewData } from "../../../core/mock/job.data";
import type { JobView } from "../../../features/jobs/JobOffer";
import type { EntityAction } from "../../../features/shared/global";

//-- Custom components
import Title from "../../../layout/components/text/title/title";
import JobInformationSection from "./components/job.information.section";
import BreadCrumbs from "../../../layout/components/navigation/auth/link/bread.crumbs";
import SimpleButton from "../../../layout/components/buttons/simple/simple.button";
import InfoPill, { type InfoPillProps } from "../../../layout/components/badges/pill/info.pill";
import MenuDrawer, { MenuDrawerBody, MenuDrawerItem, MenuDrawerTrigger } from "../../../layout/components/menu/drawer/menu.drawer";

//-- SVG Components
import EditSVG from "/src/assets/svg/menu/edit-2-svgrepo-com.svg"
import VerticalOptionsSVGComponent from "/src/assets/svg/menu/options-vertical-svgrepo-com.svg"

//-- CSS styles
import styles from "./PrivateJobViewPage.module.css"




//-- Types

export interface UserPageSinglePageProps{}


type ViewModeTypes = 
    | "overview"



const PrivateJobViewPage: React.FC<UserPageSinglePageProps> = () => {
    const { t } = useTranslation()
    const { setNavbar } = useAppContext();

    /** States */
    const [action, setAction] = useState<EntityAction>(null);
    const [viewMode, setViewMenu] = useState<ViewModeTypes>("overview");
    const [jobView, setJobView] = useState(jobsViewData["1"] as unknown as JobView);

    /** Styles & design */
    const [infoPillSettings, setInfoPillSettings] = useState<InfoPillProps>({ text: "" });

    useEffect(()=>{
        const computed = getComputedStyle(document.documentElement);

        const status = jobView.activityStatus ?? jobView.publicationStatus;
        const text = renderStatus(t, status);

        const txtColor = computed.getPropertyValue(jobStatusStyles[status]?.txtColor);
        const backgroundColor = computed.getPropertyValue(jobStatusStyles[status]?.bgColor);

        setInfoPillSettings({ 
            text,
            txtColor,
            backgroundColor,
            borderRadius: 10
        })
    },[])


    /** Global Side effects */
    useEffect(()=>{
        console.log({jobView})
        
        //--Navbar
        const linkData = [
            { route: RouteScheme.userJobs, text: t("jobs.jobs"), current: false },
            { route: RouteScheme.createJob, text: jobView.title, current: true }
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
    },[setNavbar, jobView])


    /** -- RENDER --- */

    return (
        <main className={styles.main}>
            {/** PANNEL (prensetation) */}
            <div className={styles.pannel}>
                {/** LEFT */}
                <div className={styles.left}>
                    <Title title={jobView.title} fontSize="25px"/>
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

            {/** View */}
            <div>

            </div>

            {/** WIDGET (main content) */}
            <div className={styles.widget}>
                {
                    viewMode === "overview" && (
                        <JobInformationSection />
                    )
                }
            </div>
            
        </main>
    );
}
 
export default PrivateJobViewPage;