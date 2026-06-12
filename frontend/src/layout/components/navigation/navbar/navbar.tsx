

import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";

import type { UserBasicData } from "../../../../features/users/user.profile";
import type { RecruiterDashboardKpis } from "../../../../features/dashboard/KpiData";


import styles from "./style.module.css"
import { getSession } from "../../../../core/auth.helpers";


export interface NavBarProps{
    className?: string;
}



const NavBar: React.FC<NavBarProps> = ({
    className
}) => {
    const { t } = useTranslation();
    
    const [notificationCount, setNotificationCount] = useState<number>(0);
    const [kpiData, setKpiData] = useState<RecruiterDashboardKpis | null>(null);
    const [userBasicData, setUserBasicData] = useState<UserBasicData | null>(null);

    useEffect(()=>{}, [])
    

    return (
        <div className={`${styles.container} ${className}`}>
            <div className={styles.leadingSection}>
                <span className={styles.title}>
                    {t("global.messages.welcome")}
                </span>
            </div>
            <div className={styles.actionSection}>
                <div className={styles.search}>
                    <input type="text" name="" id="" />
                </div>

                <div className={styles.notification}></div>
                
                <div className={styles.profile}>
                    <img src="" alt="" />
                </div>
            </div>
        </div>
    );
}
 
export default NavBar;