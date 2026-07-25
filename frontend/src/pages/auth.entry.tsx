

import React, { useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { redirectAccordingToSession } from "../core/auth.helpers";

interface EntryPageProps{
    children?: React.ReactNode 
}

const AuthEntryPage: React.FC<EntryPageProps> = ({
    children
}) => {
    const navigate = useNavigate();
    const location = useLocation();

    useEffect(()=>{
        redirectAccordingToSession(navigate, location);

        //-- init data 
        
    }, []);

    return ( <>{children}</> );
}
 
export default AuthEntryPage;