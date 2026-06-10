

import { useEffect } from "react";
import { useNavigate } from "react-router-dom";

import RouteScheme from "../../../route.scheme";



import styles from "./styles.module.css"



const UserHome = () => {
    const navigate = useNavigate()

    useEffect(()=>{
        const token = localStorage.getItem("token") ?? undefined;
        if(!token)
            navigate(RouteScheme.main);  
    },[])
    
    return ( 
        <div className={styles.container}>
            {/** Main */}
            <main>
                <div>
                    {/* AnalyticsCard */}
                </div>
            </main>
        </div>
    );
}
 
export default UserHome;