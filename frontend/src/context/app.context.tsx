import { 
    useState,
    createContext,
    useMemo,
    useCallback,
    useEffect,
} from "react";

import type { JobOverview } from "./user.job.context";
import type { CurrentActor } from "../features/shared/account";
import type { AppLoadingState, AppModalProps, AppPopUpSettings, UserAppNavBarProps } from "./context.type";

import UserQueriesServices from "../api/services/user/queries";
import CandidatesQueries from "../api/services/candidate/queries";
import { INITIAL_JOB_VIEW, type JobView } from "../features/jobs/JobOffer";

import { AccountRole } from "../core/enums/AccountRole";
import { getSession } from "../core/auth.helpers";
import { type RecruiterDashboardKpis } from "../features/dashboard/KpiData";

//-- Const
import { COUNTDOWN_EXPIRED_STORAGE_KEY,  COUNTDOWN_LABEL_STORAGE_KEY,  DraggableCountdown, type DraggableCountdownProps } from "../layout/components/draggable.contdown";



interface AppContextProps{
    //-- Loading
    loading?: AppLoadingState;
    setLoading: (param: AppLoadingState)=>void;
    
    //-- App initialization
    isAppInitializing: boolean;
    initializeAccountData: ()=>void;

    //-- Popup
    popup: AppPopUpSettings;
    setPopup: (param: AppPopUpSettings)=>void;

    //-- Modal
    modal: AppModalProps | null;
    setModal: (param: AppModalProps | null)=>void;

    //-- User(Recruiter) Navbar - Config
    navbar?: UserAppNavBarProps | null;
    setNavbar: (param: UserAppNavBarProps | null) => void;

    //-- Countdown
    countdown: DraggableCountdownProps | null;
    setCountdown: (param: DraggableCountdownProps | null) => void; // modify state & clear the countndow and all associated dependencies

    //-- Current Actor
    currentActor: CurrentActor | null;
    setCurrentActor: React.Dispatch<React.SetStateAction<CurrentActor | null>>;

    //-- Kpi data
    kpiData: RecruiterDashboardKpis | null;
    setKpiData: React.Dispatch<React.SetStateAction<RecruiterDashboardKpis | null>>;

    //-- Notification
    notificationCount: number;
    setNotificationCount: React.Dispatch<React.SetStateAction<number>>;

    /** Actor profi limage */
    avatarUrl: string ;
    setAvatarUrl: (param: string)=>void;

}

export const AppContext = createContext<AppContextProps | null>(null);



interface AppContextProviderProps{
    children: React.ReactNode
}



const AppContextProvider: React.FC<AppContextProviderProps> = ({children}) => {
    //-- Global state
    const [popup, setPopup] = useState<AppPopUpSettings>(null)
    const [loading, setLoading] = useState<AppLoadingState>();

    //-- Initialization
    const [isAppInitializing, setIsAppInitializing] = useState<boolean>(true);
    const [kpiData, setKpiData] = useState<RecruiterDashboardKpis | null>(null)
    

    //-- modal
    const [modal, setModal] = useState<AppModalProps | null>(null);


    //-- Countdown
    const [countdown, setCountdownState] = useState<DraggableCountdownProps | null>(() => {
        if (typeof window === 'undefined') return null;
        
        const expiration = localStorage.getItem(COUNTDOWN_EXPIRED_STORAGE_KEY);
        return expiration ? {} : null;
    });

    
    // navbar
    const [navbar, setNavbar] = useState<UserAppNavBarProps | null>(null); // user navbar

    const [currentActor, setCurrentActor] = useState<CurrentActor | null>(null);
    const [avatarUrl, setAvatarUrl] = useState<string>("")
    
    //--- Notification
    const [notificationCount, setNotificationCount] = useState<number>(0);


    const initializeAccountData = useCallback(async () => {
        try {
            const { role } = getSession();

            if (role === AccountRole.USER) {
                const data = await UserQueriesServices.getCurrentUserContext();
                console.log("USER DATA",{ data });

                setCurrentActor({
                    type: "user",
                    id: data.user.id,
                    lastName: data.user.lastName,
                    firstName: data.user.firstName,
                    email: data.user.email,
                    avatarUrl: data.user.avatarUrl ?? null,
                    company: {
                        id: data.company.id,
                        name: data.company.name,
                        location: data.company.location,
                        logoUrl: data.company.logoUrl ?? null,
                    },
                });
                
                initialializeUserKpis();
            }
            else if(role === AccountRole.CANDIDATE){
                const data = await CandidatesQueries.getCurrentCandidateContext();
                // console.log("DATA", data);
                
                setCurrentActor({
                    type: "candidate",
                    id: data.id,
                    lastName: data.lastName,
                    firstName: data.firstName,
                    imageId: data.imageUrl ?? null,
                    email: data.email,
                    location: {
                        id: data.address.id ?? null,
                        city: data.address.city,
                        country: data.address.country,
                        postalCode: data.address.postalCode,
                        street: data.address.street ?? null
                    },
                });

                const image = await CandidatesQueries.getCandidateProfileImage(data.id);
                setAvatarUrl(URL.createObjectURL(image))
            }
        }
        catch (error) {
            console.warn("Something went wrong during initialization", error);
            setCurrentActor(null);
        }
        finally {
          setIsAppInitializing(false);
        }

        //--  On disposal
        return ()=>{
            if(avatarUrl){
                URL.revokeObjectURL(avatarUrl)
            }
        }
    }, [setCurrentActor]);


    //-- Load Recruiter Kpis
    const initialializeUserKpis = useCallback(async ()=>{
        try{ 
            // kpis
            const data = await UserQueriesServices.getKPI();
            console.log({ kpis: data })
            setKpiData(data);
        }
        catch(error){
            console.warn("Something went wrong while retreiving kpis & application kpi linechart dataset : ", error)
        }
    }, []);


    useEffect(()=>{
        initializeAccountData();
    },[initializeAccountData])

    //----------------------
    //--  CUSTOM SETTER
    //----------------------
    
    const handleSetCountdown = useCallback((props: DraggableCountdownProps | null) => {
        setCountdownState((prev) => {
            // Close dropdown
            if (props === null) {
                localStorage.removeItem(COUNTDOWN_EXPIRED_STORAGE_KEY);
                localStorage.removeItem(COUNTDOWN_LABEL_STORAGE_KEY);
                return null;
            }
            // Countdown djà actif
            if (prev !== null) {
                return prev;
            }

            return props;
        });
    }, []);

    //----------------------------------
    // Context value
    //----------------------------------

    const contextValue = useMemo(
        () => ({
            popup,
            setPopup,

            modal,
            setModal,

            countdown,
            setCountdown: handleSetCountdown,

            isAppInitializing,
            loading,
            setLoading,

            navbar,
            setNavbar,

            kpiData,
            setKpiData,

            notificationCount,
            setNotificationCount,

            currentActor,
            setCurrentActor,

            avatarUrl,
            setAvatarUrl,

            
            initializeAccountData
        }),
        [
            popup,
            modal,
            countdown,
            isAppInitializing,
            loading,
            navbar,
            kpiData,
            currentActor,
            avatarUrl,
            handleSetCountdown,
        ]
    );


    return (
        <AppContext.Provider value={contextValue}>
            {children}
            <div>
                {countdown && (
                        <DraggableCountdown
                            initialSeconds={900} 
                            initialPosition={
                                typeof window !== 'undefined'
                                ? { x: window.innerWidth - 180, y: window.innerHeight - 70 }
                                : undefined
                            }
                            {...countdown} 
                        />
                    )}
            </div>
        </AppContext.Provider>
    );
}
 
export default AppContextProvider;

