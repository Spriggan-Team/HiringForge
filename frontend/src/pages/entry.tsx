import { useEffect } from "react";
import { useNavigate } from "react-router-dom";
import RouteScheme from "../route.scheme";
import { AccountRole } from "../core/enums/AccountRole";


const EntryPage = () => {
    const navigate = useNavigate();

    useEffect(()=>{
        //--check previous connexion
        const token = localStorage.getItem("token") ?? undefined;
        const role = localStorage.getItem("role");

        if(token){
            if(role === AccountRole.USER)
                navigate(RouteScheme.userHome)
        }
        else
            navigate(RouteScheme.login);     
    }, []);

    return ( <></> );
}
 
export default EntryPage;