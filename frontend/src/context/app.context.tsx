import { 
    useState,
    createContext,
} from "react";
import type { AppLoadingState, AppPopUpSettings, UserAppNavBarProps } from "./app.context.type";


interface AppContextProps{
    //-- Loading
    loading?: AppLoadingState;
    setLoading: (param: AppLoadingState)=>void;
    
    //-- Popup
    popup: AppPopUpSettings;
    setPopup: (param: AppPopUpSettings)=>void;

    navbar?: UserAppNavBarProps | null;
    setNavbar: (param: UserAppNavBarProps | null) => void;
}

export const AppContext = createContext<AppContextProps | null>(null);


interface AppContextProviderProps{
    children: React.ReactNode
}


const AppContextProvider: React.FC<AppContextProviderProps> = ({children}) => {
    //-- Global state
    const [popup, setPopup] = useState<AppPopUpSettings>(null)
    const [loading, setLoading] = useState<AppLoadingState>();
    const [navbar, setNavbar] = useState<UserAppNavBarProps | null>(null);

    return ( 
        <AppContext 
            value={{
                popup, setPopup,
                loading, setLoading,
                navbar, setNavbar
            }}
        >
            {children}
        </AppContext>
    );
}
 
export default AppContextProvider;
