import { 
    useState,
    createContext,
    useMemo,
} from "react";
import type { AppLoadingState, AppPopUpSettings, UserAppNavBarProps } from "./context.type";
import type { CurrentActor } from "../features/shared/account";
import type { JobOverview } from "./job.context";
import { INITIAL_JOB_VIEW, type JobView } from "../features/jobs/JobOffer";


interface AppContextProps{
    //-- Loading
    loading?: AppLoadingState;
    setLoading: (param: AppLoadingState)=>void;
    
    //-- Popup
    popup: AppPopUpSettings;
    setPopup: (param: AppPopUpSettings)=>void;

    //-- Navbar
    navbar?: UserAppNavBarProps | null;
    setNavbar: (param: UserAppNavBarProps | null) => void;

    //-- Current Actor
    currentActor: CurrentActor | null;
    setCurrentActor: (param: CurrentActor | null) => void;

    //------ Job
    /**
     * Current job overview used across the application
     * (AI generation, preview, creation, edition, ...).
     */
    jobOverview: JobOverview | null;
    setJobOverview: React.Dispatch<React.SetStateAction<JobOverview | null>>;

    currentJob: JobView;
    setCurrentJob: React.Dispatch<React.SetStateAction<JobView>>;
}

export const AppContext = createContext<AppContextProps | null>(null);




interface AppContextProviderProps{
    children: React.ReactNode
}





const AppContextProvider: React.FC<AppContextProviderProps> = ({children}) => {
    //-- Global state
    const [popup, setPopup] = useState<AppPopUpSettings>(null)
    const [loading, setLoading] = useState<AppLoadingState>();

    // navbar
    const [navbar, setNavbar] = useState<UserAppNavBarProps | null>(null);


    const [currentActor, setCurrentActor] = useState<CurrentActor | null>(null);

    const [jobOverview, setJobOverview ] = useState<JobOverview | null>(null);
    const [currentJob, setCurrentJob] = useState<JobView>(INITIAL_JOB_VIEW);

    // Context value
    const contextValue = useMemo(
        () => ({
            popup, setPopup,
            loading, setLoading,
            navbar, setNavbar,
            currentActor, setCurrentActor,

            jobOverview, setJobOverview,
            currentJob, setCurrentJob
        }),
        [popup, loading, navbar, currentActor]
    );

    return (
        <AppContext.Provider value={contextValue}>
            {children}
        </AppContext.Provider>
    );
}
 
export default AppContextProvider;

