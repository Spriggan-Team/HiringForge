import { useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { navigateTo } from "../App";
import RouteScheme from "../route.scheme";



interface EntryPageProps{
}

const EntryPage: React.FC<EntryPageProps> = ({
}) => {
    const navigate = useNavigate()
    
    useEffect(()=>{
        navigateTo(navigate, RouteScheme.jobs)
    },[navigate])

    return (
        <>
        </>
    );
}
 
export default EntryPage;