import React, { useEffect, useMemo } from "react";
import { useTranslation } from "react-i18next";

//-- Services
import RouteScheme from "../../../route.scheme";
import JobContextProvider from "../../../context/job.context";

//- Components
import JobFormPage from "./components/job.form.page";
import BreadCrumbs, { type LinkData } from "../../../layout/components/navigation/auth/link/bread.crumbs";
import { useAppContext } from "../../../hooks/context";
import { useAppNavigate } from "../../../hooks/navigation";
import { useParams } from "react-router-dom";
import JobQueries from "../../../api/services/jobs/queries";
import JobServices from "../../../api/services/jobs/command";



interface ModifyJobPageProps{}


const ModifyJobPage: React.FC<ModifyJobPageProps> = ({}) => {
    return (
        <JobContextProvider>
            <ModifyJobPageContent />
        </JobContextProvider>
    );
}


export default ModifyJobPage;


/**
 * ------
 * Modify Page Content
 * -----
 */

interface ModifyJobPageContentProps
{}

const ModifyJobPageContent: React.FC<ModifyJobPageContentProps> = ({}) => {
    const { t } = useTranslation()
    const { currentJob, setPopup, setCurrentJob } = useAppContext();

    const { id: jobId } = useParams<{id: string}>();
    const navigate = useAppNavigate();

    const linkData = useMemo(()=>{
        const links: LinkData[] = [];
        links.push({ route: RouteScheme.userJobs,  text: t("jobs.jobs"), current: false })
        
        if(currentJob && currentJob.id)
        {
            links.push({ 
                current: false, 
                text: t("jobs.jobs"), 
                route: RouteScheme.userJobView,
            })
        }

        links.push({ route: RouteScheme.createJob, text: t("jobs.modifyJob"), current: true  })
        
        return (links);
    }, [currentJob]);


    //-- Initalize
    useEffect(()=>{
        if(!jobId)
            return;

        const fetchCurrentJobView = async ()=>{
            try{
                const view = await JobQueries.getJobView(jobId);
                setCurrentJob(view);
                console.log({view})
            }
            catch(error){
                console.log("Something went wrong while loading job: ", error);
                setPopup({status: 'error', message: t('global.messages.error')});
                navigate(RouteScheme.userJobs, { from: RouteScheme.modifyJob });
            }
        }

        fetchCurrentJobView();
    },[]);


    const handleUpdateJob = async ()=>{
        await JobServices.updateJob(currentJob);
        return { offerId: currentJob.id }; //jobId
    }

    const handleUdateAssets = async (
        jobId: string, 
        images: {
            file: File;
            isMain: boolean;
        }[]
    )=>{
        await JobServices.updateJobAssets(
            jobId,
            images
        );
    }

    return (
        <>
            <JobFormPage
                navBar={{
                    title: t("jobs.modifyJob"),
                    description: <BreadCrumbs overlayColor="#4338CA" links={linkData} />,
                }}
                handleJob={handleUpdateJob}
                handleUploadImage={handleUdateAssets}
                formType="modify"
            />
        </>
    );
}
 
