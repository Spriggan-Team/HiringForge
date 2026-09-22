
import { useState } from "react";
import { useTranslation } from "react-i18next";
import { useNavigate } from "react-router-dom";


//-- Services
import RouteScheme from "../../../../route.scheme";
import { navigateTo } from "../../../../App";

//-- SVG Components
import LogoSVG from '/src/assets/custom-logo.svg?react';
import HomeSVG from "/src/assets/svg/menu/home.svg?react"
import PostSVG from "/src/assets/svg/menu/job-work-chart-data-analysis-statistic-report-svgrepo-com.svg?react"
import CandidateSVG from "/src/assets/svg/menu/candidate-for-elections-svgrepo-com.svg?react"
import EmploymentOfferSVG from "/src/assets/svg/menu/employment-deal-svgrepo-com.svg?react"
import InterviewsSVG from "/src/assets/svg/menu/user-speak-rounded-svgrepo-com.svg?react"
import StatsSVG from "/src/assets/svg/analytics/analytics-svgrepo-com.svg?react"

// import AgentSVG from "/src/assets/svg/menu/illustrations-of-agents-svgrepo-com.svg?react"
import ChevronLeftSVG from "/src/assets/svg/menu/chevron-right-double-svgrepo-com.svg?react"
import DateSVGComponent from "/src/assets/svg/catalog/date-svgrepo-com.svg?react"


//-- CSS- style
import styles from "./UserSideMenu.module.css"

export type MenuKeys = |
        "home"
        | "poste"
        | "candidates"
        | "offers"
        | "calendar"
        | "stats"
        | "interviews"


const UserSideMenu = () => {
    const { t } = useTranslation();
    const navigate = useNavigate();

    const [isCollapsed, setIsCollapsed] = useState(true);
    const [active, setActive] = useState(localStorage.getItem("menu") ?? "home");

    const menuItems: {id: MenuKeys, [key: string]: any}[] = [
        { id: "home", svg: HomeSVG, label: t("global.menu.home"), route: RouteScheme.userHome },
        { id: "poste", svg: PostSVG, label: t("global.menu.poste"), route: RouteScheme.userJobs },
        { id: "candidates", svg: CandidateSVG, label: t("global.menu.candidates"), route: RouteScheme.userCandidate },
        { id: "offers", svg: EmploymentOfferSVG, label: t("global.menu.offers"), route: RouteScheme.userOffer },
        { id: "interviews", svg: InterviewsSVG, label: t("global.menu.Interviews", "Interviews"), route: RouteScheme.userIntervews },
        { id: "calendar", svg: DateSVGComponent, label: t("global.menu.calendar"), route: RouteScheme.userSchedule },
        { id: "stats", svg: StatsSVG, label: t("global.menu.stats"), route: RouteScheme.userStats },
        // { id: "agents", svg: AgentSVG, label: t("global.menu.agents"), route: "" },
        // { id: "settings", svg: SettingsSVG, label: t("global.menu.settings"), route: "" },
    ];


    return (
        <aside className={`${styles.aside} ${isCollapsed ? styles.collapsed : ""}`}>
            <div className={styles.head}>
                <LogoSVG height={40} width={40} />
                <button 
                    type="button" 
                    className={styles.toggleBtn} 
                    onClick={() => setIsCollapsed(!isCollapsed)}
                >
                    <ChevronLeftSVG width={16} height={16} />
                </button>
            </div>
            <nav className={styles.navLinks}>
                {menuItems.map((item) => (
                    <SideBarItem
                        key={item.id}
                        svg={item.svg}
                        text={item.label}
                        active={active === item.id}
                        onClick={() => {
                            setActive(item.id)
                            navigateTo(navigate, item.route, { menuId:  item.id})
                        }}
                    />
                ))}
            </nav>
        </aside>
    );
};


export default UserSideMenu;


interface SideBarItemProps {
    svg: React.FC<React.SVGProps<SVGSVGElement>>;
    text: string;
    active?: boolean;
    onClick?: () => void;
}


const SideBarItem: React.FC<SideBarItemProps> = ({ text, svg: Icon, onClick, active = false }) => {
    return (
        <div className={`${styles.item} ${active ? styles.active : ""}`} onClick={onClick}>
            <Icon width={22} height={22} />
            <span className={styles.text}>{text}</span>
        </div>
    );
};