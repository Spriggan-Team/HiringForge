import { createContext, useContext, useMemo, useState } from "react";
import { INITIAL_JOB_VIEW, type CompleteJobView, type JobCardinal, type JobEngagementMetrics, type JobSummary, type JobView } from "../features/jobs/JobOffer";


/** ----------------------------------------------------------------
 * Job Context
 * ---------------------------------------------------------------- */


export interface JobOverview
    extends Pick<
        JobView,
        "title" | "skills" | "categories" | "contract" | "salary"
    > {
    location: string;
}

interface JobContextValue {
    /**
     * Global job overview used across the application.
     * Used for AI generation, preview, creation workflow, etc.
     */
    jobOverview: JobOverview | null;
    setJobOverview: React.Dispatch<
        React.SetStateAction<JobOverview | null>
    >;

    /**
     * Job currently displayed in a detailed view.
     */
    viewedJob: CompleteJobView | null;
    setViewedJob: React.Dispatch<
        React.SetStateAction<CompleteJobView | null>
    >;

    /**
     * Job currently being edited.
     */
    editingJob: JobView;
    setEditingJob: React.Dispatch<
        React.SetStateAction<JobView>
    >;
}



const JobContext = createContext<JobContextValue | null>(null);


interface JobContextProviderProps{
    children: React.ReactNode
}


const UserJobContextProvider: React.FC<JobContextProviderProps> = ({
    children
}) => {
    
    //-- user job
    const [jobOverview, setJobOverview ] = useState<JobOverview | null>(null);

    const [viewedJob, setViewedJob] = useState<CompleteJobView  | null>(null); // view
    const [editingJob, setEditingJob] = useState<JobView>(INITIAL_JOB_VIEW); // edition

    const contextValues = useMemo(() => ({
        jobOverview,
        setJobOverview,

        viewedJob,
        setViewedJob,

        editingJob,
        setEditingJob
    }), [
        jobOverview,
        viewedJob,
        editingJob
    ]);

    return (
        <JobContext.Provider value={contextValues}>
            {children}
        </JobContext.Provider>
    );
};



export default UserJobContextProvider;


export const useUserJobContext = () => {
    const context = useContext(JobContext);

    if (!context) {
        throw new Error("useJob must be used within a JobProvider.");
    }

    return context;
};



