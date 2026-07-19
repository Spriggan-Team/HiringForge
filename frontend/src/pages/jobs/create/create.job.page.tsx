
import { useCallback, useEffect } from "react";
import { useTranslation } from "react-i18next";
import { useNavigate } from "react-router-dom";

//--Custom Comoponents
import BreadCrumbs from "../../../layout/components/navigation/auth/link/bread.crumbs";
import InfoBoxSection from "./components/infoBoxSection/info.box.section";
import OptionBoxSection from "./components/optionBoxSection/option.box.section";
import ImageInput from "../../../layout/components/form/input/image/image";
import InputLabel from "../../../layout/components/form/input/input.label";

//-- Services
import { navigateTo } from "../../../App";
import RouteScheme from "../../../route.scheme";
import { useAppContext } from "../../../hooks/context";
import JobContextProvider  from "../../../context/job.context";
import type { JobView } from "../../../features/jobs/JobOffer";

//-- SVG components
import RightToLeftArrowSVG from '/src/assets/svg/arrows/back-arrow-direction-down-right-left-up-svgrepo-com.svg';

//-- CSS Styles
import styles from "./CreateJobPage.module.css"
import JobServices from "../../../api/services/jobs/command";


/** -- Page Components: CreateJobPage -- */


interface CreateJobPageProps{}

const CreateJobPage: React.FC<CreateJobPageProps> = () => {
    const navigate = useNavigate();

    const { t } = useTranslation();
    const { setNavbar, setLoading , setPopup } = useAppContext();

    useEffect(()=>{
        //--navbar
        const linkData = [
                        { route: RouteScheme.userJobs, text: t("jobs.jobs"), current: false },
                        { route: RouteScheme.createJob, text: t("jobs.buttons.create"), current: true }
                    ];
        setNavbar({
            title: t("jobs.buttons.create"),
            description: (
                <BreadCrumbs
                    overlayColor="#4338CA"
                    links={linkData} 
                />
            )
        })
        return ()=>{
            setNavbar(null);
        };
    },[setNavbar])


    //-- Handlers
    const handleSave = useCallback(async (currentJob: JobView)=>{
        try{
            setLoading({state: true, subtitle: t('jobs.createJob.messages.creatingJob')});
            const res = await JobServices.createJob(currentJob);

            setLoading({state: false, subtitle: undefined });
            setPopup({
                status: "success",
                message: t("global.messages.save")
            });
        }
        catch(error){
            setLoading({state: false, subtitle: undefined});
            
            //-- message error
            if (error instanceof Error) {}
            else {
                setPopup({
                    status: "success",
                    message: t("global.messages.error")
                });
                console.log("Unknown error:", error);
            }
        }
    },[]);

    
    
    //-- RENDER
    return (
        <JobContextProvider>
            <main className={styles.container}>
                {/**-- ASIDE (icon) -- */}
                <aside className={styles.side}>
                    <button
                        className={`${styles.backButton} card`}
                        onClick={()=> navigateTo(navigate, RouteScheme.userJobs)} 
                    >
                        <RightToLeftArrowSVG width={15} height={15} />
                    </button>
                </aside>

                <section className={styles.content}>
                    {/**-- COLUMNS -- */}
                    <div className={styles.columns}>
                        {/** MAIN INPUT COLUMN */}
                        <div className={styles.mainInfoBox}>
                            <InfoBoxSection />
                        </div>

                        {/** IMAGE */}
                        <div className={`${styles.image} card`}>
                            <InputLabel label={"Main image"}/>
                            <ImageInput />
                        </div>
                        
                        {/** PARAM SETTINGS */}
                        <div className={styles.paramBox} >
                            <OptionBoxSection
                                onComplete={handleSave}
                            />
                        </div>
                    </div>
                </section>
            </main>
        </JobContextProvider>
    );
}


export default CreateJobPage;