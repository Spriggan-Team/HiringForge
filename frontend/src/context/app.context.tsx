import { 
    useState,
    useContext,
    createContext,
} from "react";
import type { AppLoadingState, AppPopUpSettings } from "./app.context.type";


interface AppContextProps{
    loading?: AppLoadingState;
    popup: AppPopUpSettings;
    setLoading: (param: AppLoadingState)=>void;
    setPopup: (param: AppPopUpSettings)=>void;
}

export const AppContext = createContext<AppContextProps | null>(null);


interface AppContextProviderProps{
    children: React.ReactNode
}


const AppContextProvider: React.FC<AppContextProviderProps> = ({children}) => {
    //-- Global state
    const [popup, setPopup] = useState<AppPopUpSettings>(null)
    const [loading, setLoading] = useState<AppLoadingState>();

    return ( 
        <AppContext.Provider value={{loading, setLoading, popup, setPopup }}>
            {children}
        </AppContext.Provider>
    );
}
 
export default AppContextProvider;
