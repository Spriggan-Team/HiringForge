

import React, { useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { redirectAccordingToSession } from "../core/auth.helpers";

interface EntryPageProps{
    children?: React.ReactNode 
}

const EntryPage: React.FC<EntryPageProps> = ({
    children
}) => {
    const navigate = useNavigate();

    useEffect(()=>{
        redirectAccordingToSession(navigate)
    }, []);

    return ( <>{children}</> );
}
 
export default EntryPage;