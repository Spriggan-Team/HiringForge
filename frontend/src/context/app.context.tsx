import { 
    useState,
    createContext,
    useMemo,
} from "react";
import type { AppLoadingState, AppPopUpSettings, UserAppNavBarProps } from "./context.type";
import type { CurrentActor } from "../features/shared/account";


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

    // Context value
    const contextValue = useMemo(
        () => ({
            popup, setPopup,
            loading, setLoading,
            navbar, setNavbar,
            currentActor, setCurrentActor,
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

