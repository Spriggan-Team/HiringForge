import React, { useEffect, useMemo } from "react";
import { useTranslation } from "react-i18next";
import { useParams } from "react-router-dom";

//-- Services
import RouteScheme from "../../../route.scheme";
import UserJobContextProvider, { useUserJobContext } from "../../../context/user.job.context";
import JobQueries from "../../../api/services/jobs/queries";
import JobServices from "../../../api/services/jobs/command";
import type { JobView } from "../../../features/jobs/JobOffer";
import { useAppContext } from "../../../hooks/context";
import { useAppNavigate } from "../../../hooks/navigation";

//- Components
import JobFormPage from "./components/job.form.page";
import BreadCrumbs, { type LinkData } from "../../../layout/components/navigation/auth/link/bread.crumbs";




interface ModifyJobPageProps{}


const ModifyJobPage: React.FC<ModifyJobPageProps> = ({}) => {
    return (
        <UserJobContextProvider>
            <ModifyJobPageContent />
        </UserJobContextProvider>
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
    const { setPopup } = useAppContext();
    const { editingJob, setEditingJob } = useUserJobContext();


    const navigate = useAppNavigate();
    const { id: jobId } = useParams<{id: string}>();


    const linkData = useMemo(()=>{
        const links: LinkData[] = [];
        links.push({ route: RouteScheme.userJobs,  text: t("jobs.jobs"), current: false })
        
        if(editingJob && editingJob.id)
        {
            links.push({ 
                current: false, 
                text: t("jobs.jobs"), 
                route: RouteScheme.userJobView,
            })
        }

        links.push({ route: RouteScheme.createJob, text: t("jobs.modifyJob"), current: true  })
        
        return (links);
    }, [editingJob]);


    //-- Initalize
    useEffect(()=>{
        if(!jobId)
            return;

        const fetchCurrentJobView = async ()=>{
            try{
                const id = jobId ?? editingJob?.id;
                const view = await JobQueries.getJobView(id);
                
                console.log({view});
                setEditingJob(view);
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

    
    useEffect(()=>{
        if(!jobId && !editingJob?.id){
            setPopup({ status: 'error', message: t('global.messages.unexpectedErrorReload') })
            return;
        }
    },[editingJob])

    const handleUpdateJob = async (job: JobView)=>{
        // console.log("UPDATED JOB: ",job)
        await JobServices.updateJob(job);
        return { offerId: job.id };
    }

    const handleUdateAssets = async (
        jobId: string, 
        images: {
            file: File;
            isMain: boolean;
        }[]
    )=>{
        const ids = [editingJob?.mainImageFileId].filter(
            (id): id is string => Boolean(id)
        );
        await JobServices.updateJobAssets(
            jobId,
            images,
            ids
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
 
