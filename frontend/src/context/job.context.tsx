import { createContext, useContext, useState } from "react";
import { INITIAL_JOB_VIEW, type JobView } from "../features/jobs/JobOffer";


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
     * Current job overview used across the application
     * (AI generation, preview, creation, edition, ...).
     */
    overview: JobOverview | null;
    setOverview: React.Dispatch<React.SetStateAction<JobOverview | null>>;

    currentJob: JobView;
    setCurrentJob: React.Dispatch<React.SetStateAction<JobView>>;
}


const JobContext = createContext<JobContextValue | null>(null);


export const useJob = () => {
    const context = useContext(JobContext);

    if (!context) {
        throw new Error("useJob must be used within a JobProvider.");
    }

    return context;
};


interface JobContextProviderProps{
    children: React.ReactNode
}


const JobContextProvider: React.FC<JobContextProviderProps> = ({
    children
}) => {
    const [jobOverview, setJobOverview ] = useState<JobOverview | null>(null);
    const [currentJob, setCurrentJob] = useState<JobView>(INITIAL_JOB_VIEW);

    return (
        <JobContext.Provider value={{
            overview: jobOverview,
            setOverview: setJobOverview,
            currentJob, setCurrentJob
        }}>
            {children}
        </JobContext.Provider>
    );
};



export default JobContextProvider;