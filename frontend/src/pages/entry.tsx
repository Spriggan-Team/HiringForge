import { useEffect } from "react";
import { useNavigate } from "react-router-dom";
import RouteScheme from "../route.scheme";


const EntryPage = () => {
    const navigate = useNavigate();

    useEffect(()=>{
        const token = localStorage.getItem("token") ?? undefined;
        if(token)
            navigate(RouteScheme.home)
        else
            navigate(RouteScheme.login);     
    }, []);

    return ( <></> );
}
 
export default EntryPage;