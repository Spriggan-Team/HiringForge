

import { useEffect } from "react";
import { useNavigate } from "react-router-dom";

import styles from "./styles.module.css"
import RouteScheme from "../../route.scheme";


const Home = () => {
    const navigate = useNavigate()

    useEffect(()=>{
        const token = localStorage.getItem("token") ?? undefined;
        if(!token)
            navigate(RouteScheme.main);  
    },[])
    
    return ( 
        <div className={styles.container}>
            <div className={styles.statsSection}></div>
            <div className={styles.jobsSection}>
                <div className={styles.searchSection} ></div>
                <div className={styles.notificationSection}></div>
            </div>
        </div>
    );
}
 
export default Home;