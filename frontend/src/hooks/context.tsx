import { useContext } from "react";
import { AppContext } from "../context/app.context";

export const useAppContext = () => {
    const context = useContext(AppContext);
    
    // Sécurité indispensable si le hook est appelé hors du Provider
    if (!context) {
        throw new Error("useAppContent must be used within an AppProvider");
    }
    
    return context;
};