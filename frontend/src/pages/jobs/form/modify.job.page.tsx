import React, { useMemo } from "react";
import { useTranslation } from "react-i18next";

//-- Services
import RouteScheme from "../../../route.scheme";
import JobContextProvider from "../../../context/job.context";

//- Components
import JobFormPage from "./components/job.form.page";
import BreadCrumbs, { type LinkData } from "../../../layout/components/navigation/auth/link/bread.crumbs";
import { useAppContext } from "../../../hooks/context";
import { useAppNavigate } from "../../../hooks/navigation";



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
{

}

const ModifyJobPageContent: React.FC<ModifyJobPageContentProps> = ({}) => {
    const { t } = useTranslation()
    const { currentJob } = useAppContext();
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

    return (
        <>
            <JobFormPage 
                navBar={{
                    title: t("jobs.modifyJob"),
                    description: <BreadCrumbs overlayColor="#4338CA" links={linkData} />,
                }} 
            />
        </>
    );
}
 
