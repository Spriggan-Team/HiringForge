import { createContext, useContext, useState } from "react";
import { INITIAL_JOB_VIEW, type JobStatus, type JobView } from "../features/jobs/JobOffer";


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


    return (
        <JobContext.Provider value={null}>
            {children}
        </JobContext.Provider>
    );
};



export default JobContextProvider;



