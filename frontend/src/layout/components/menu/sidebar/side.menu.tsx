
import { useState } from "react";
import { useTranslation } from "react-i18next";

//-- Components
import LogoSVG from '/src/assets/custom-logo.svg';
import HomeSVG from "/src/assets/svg/menu/home.svg"
import PostSVG from "/src/assets/svg/menu/work-svgrepo-com.svg"
import CandidateSVG from "/src/assets/svg/menu/candidate-for-elections-svgrepo-com.svg"
import CalendarSVG from "/src/assets/svg/menu/calendar-svgrepo-com.svg"
import SettingsSVG from "/src/assets/svg/menu/settings-svgrepo-com.svg"
import AgentSVG from "/src/assets/svg/menu/illustrations-of-agents-svgrepo-com.svg"
import ChevronLeftSVG from "/src/assets/svg/menu/chevron-right-double-svgrepo-com.svg"

// CSS- style
import styles from "./styles.module.css"


const SideMenu = () => {
    const { t } = useTranslation();
    const [active, setActive] = useState("home");
    const [isCollapsed, setIsCollapsed] = useState(false);

    const menuItems = [
        { id: "home", svg: HomeSVG, label: t("userHome.menu.home") },
        { id: "poste", svg: PostSVG, label: t("userHome.menu.poste") },
        { id: "candidates", svg: CandidateSVG, label: t("userHome.menu.candidates") },
        { id: "interview", svg: CandidateSVG, label: t("userHome.menu.interview") },
        { id: "calendar", svg: CalendarSVG, label: t("userHome.menu.calendar") },
        { id: "agents", svg: AgentSVG, label: t("userHome.menu.agents") },
        { id: "settings", svg: SettingsSVG, label: t("userHome.menu.settings") },
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
                        onClick={() => setActive(item.id)}
                    />
                ))}
            </nav>
        </aside>
    );
};

export default SideMenu;

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