

import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";

//-- Data Types
import type { UserBasicData } from "../../../../features/users/user.profile";
import type { RecruiterDashboardKpis } from "../../../../features/dashboard/KpiData";

//-- SVG Components
import SearchSVGComponent from "/src/assets/svg/menu/search-svgrepo-com.svg"
import NotificationRingSVGComponent from "/src/assets/svg/menu/alarm-alert-bell-notification-warning-svgrepo-com.svg"
import DownArrowSVGComponent from "/src/assets/svg/menu/down-arrow-5-svgrepo-com.svg"

//-- CSS Styles
import styles from "./style.module.css"
import BasicInput from "../../../../layout/components/form/input/basic.input";
import { useAppContext } from "../../../../hooks/context";


export interface NavBarProps{
    className?: string;
}



const NavBar: React.FC<NavBarProps> = ({
    className
}) => {
    const { t } = useTranslation();
    const { navbar } = useAppContext();

    //-- Text
    const postsTxt = t('userHome.header.leading.open_post', { count: 0 });
    const candTxt = t('userHome.header.leading.candidature', { count: 12 });

    const informationTxt = t('userHome.header.leading.summary_sentence', { 
        openPostsText: postsTxt, 
        candidaturesText: candTxt 
    });
    

    return (
        <div className={`${styles.container} ${className}`}>

            <div className={styles.leadingSection}>
                <h1 className={styles.title}>
                    {navbar?.title ?? t("global.messages.welcome", { name: "Nexus Gaming" })}
                </h1>
                <p className={styles.desc}>
                    { navbar?.description ?? informationTxt }
                </p>
            </div>

            <div className={styles.actionSection}>
                <div className={styles.search}>
                    <BasicInput
                        svg={SearchSVGComponent}
                        backgroundColor="white"
                        className={`${styles.input} input`}
                        placeholder={t("userHome.inputs.search.placeholder")}
                    />
                </div>

                <div 
                    style={{ ["--notifNumber" as string]: "2" }}
                    className={styles.notification}
                >
                    <NotificationRingSVGComponent 
                        width={25}
                        height={25}
                        className={styles.notficationRing}
                    />
                </div>

                <div className={styles.profile}>
                    <img 
                        alt=""
                        className={styles.img}
                        src="/src/assets/images/pngtree-glitch-effect-avatar-profile-vector-png-image_15605578.png" 
                    />

                    <div className={styles.profileMenu}>
                        <div className={styles.txt}>
                            <span className={styles.username}>Thomas</span>
                            <span>Recruteur</span>
                        </div>
                        <DownArrowSVGComponent height={14} width={14} className={styles.arrow} />
                    </div>
                </div>
                
            </div>

        </div>
    );
}
 
export default NavBar;