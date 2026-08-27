
import React, { useMemo } from "react";
import { useTranslation } from "react-i18next";

//-- services
import RouteScheme from "../../../route.scheme";

//-- Components
import JobFormPage from "./components/job.form.page";
import BreadCrumbs from "../../../layout/components/navigation/auth/link/bread.crumbs";
import JobContextProvider from "../../../context/job.context";




interface CreateJobPageProps{}

const CreateJobPage: React.FC<CreateJobPageProps> = ({}) => {
    return (
        <JobContextProvider>
            <CreateJoPageContent />
        </JobContextProvider>
    )
}
 
export default CreateJobPage;



/**
 * -------------
 * Page Content
 * ---------------
 */

interface CreateJoPageContentProps{

}


const CreateJoPageContent: React.FC<CreateJoPageContentProps> = ({}) => {
    const {t} = useTranslation();
    const linkData = useMemo(()=>(
        [
            { route: RouteScheme.userJobs,  text: t("jobs.jobs"),           current: false },
            { route: RouteScheme.createJob, text: t("jobs.buttons.create"), current: true  },
        ]
    ), []);

    
    return (
        <>
            <JobFormPage navBar={
                {
                    title: t("jobs.buttons.create"),
                    description: <BreadCrumbs overlayColor="#4338CA" links={linkData} />,
                }
            } />
        </>
    );
}
 


