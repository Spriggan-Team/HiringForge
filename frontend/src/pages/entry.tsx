import { useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { redirectAccordingToSession } from "../core/auth.helpers";


const EntryPage = () => {
    const navigate = useNavigate();

    useEffect(()=>{
        redirectAccordingToSession(navigate)
    }, []);

    return ( <></> );
}
 
export default EntryPage;