import { createContext, useContext, useState } from "react";
import type { AppPopUpSettings } from "./app.context.type";


interface AppContextProps{
    popup: AppPopUpSettings;
    setPopup: (param: AppPopUpSettings)=>void;
}

export const AppContext = createContext<AppContextProps | null>(null);


interface AppContextProviderProps{
    children: React.ReactNode
}


const AppContextProvider: React.FC<AppContextProviderProps> = ({children}) => {
    //-- Global state
    const [popup, setPopup] = useState<AppPopUpSettings>(null)

    return ( 
        <AppContext.Provider value={{ popup, setPopup }}>
            {children}
        </AppContext.Provider>
    );
}
 
export default AppContextProvider;
