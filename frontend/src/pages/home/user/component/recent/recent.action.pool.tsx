import { useTranslation } from "react-i18next";

//-- Services
import type { PersonActionType } from "../../../../../features/shared/account";

//-- Custom Components
import SectionHeader from "../../../../../layout/components/sections/sectionHeader/section.header";
import ViewAllLink from "../../../../../layout/components/link/view.all.link";

//-- CSS styles
import styles from "./RecentActionPool.module.css"
import { useState } from "react";



const mockData = [
    {
        delay: "11min",
        person: "Sarah Martin",
        title: "Frontend Developper",
        type: "postulate" as PersonActionType,
    },
    {
        delay: "1h",
        person: "Marc Leroy",
        title: "Entretien Technique",
        type: "create-interview" as PersonActionType,
    },
    {
        delay: "2h",
        person: null,
        title: "Backend Developper",
        type: "publish-offer" as PersonActionType,
    },
    {
        delay: "3h",
        person: "Alice Dupont",
        title: "Entretien Technique",
        type: "confirm-interview" as PersonActionType,
    }
]

interface RecentActionPoolProps{}


const RecentActionPool: React.FC<RecentActionPoolProps> = ({}) => {
    const {t} = useTranslation();
    const [recentActions, setRecentAction] = useState(mockData);
    return (
        <div className={styles.container}>
            <SectionHeader
                title={t("global.actions.recentAction")}
                action={<ViewAllLink />}
            />
            <div className={styles.items}>
                {
                    recentActions.map((item, index)=>(
                        <ActionItem
                            key={index}
                            type={item.type}
                            title={item.title}
                            delay={item.delay}
                            person={item.person}
                        />
                    ))
                }
            </div>
        </div>
    );
}
 
export default RecentActionPool;


/**-- Item -- */

interface ActionItemProps{
    person?: string|null;
    title: string;
    delay: string;
    type: PersonActionType;
    onClick?: React.MouseEventHandler<HTMLDivElement>
}


const actionConfigs: Record<PersonActionType, { icon: string; className: string }> = {
    "postulate": { icon: "👤", className: styles.postulate },
    "create-interview": { icon: "↗️", className: styles.createInterview },
    "publish-offer": { icon: "📄", className: styles.publishOffer },
    "confirm-interview": { icon: "✅", className: styles.confirmInterview },
};

const ActionItem: React.FC<ActionItemProps> = ({
    title,
    type,
    person,
    delay,
    onClick
}) => {
    const {t} = useTranslation();
    const config = actionConfigs[type];
    
    //-- Get title
    const renderTitle = () => {
        switch (type) {
            case "postulate":
                return t("notification.postulate", { person, offer: title });
            case "create-interview":
                return t("notification.createInterview", { person, interview: title });
            case "publish-offer":
                return t("notification.publishOffer", { title });
            case "confirm-interview":
                return t("notification.confirmInterview", { person });
            default:
                return "";
        }
    };

    return (
        <div 
            onClick={onClick}
            className={`${styles.item} ${onClick ? styles.clickable : ""}`}
        >
            {/* Icon  */}
            <div className={`${styles.svg} ${config?.className}`}>
                <div>{config?.icon}</div>
            </div>
            
            <div className={styles.main}>
                <span className={styles.title}>
                    {renderTitle()}
                </span>
                <span className={styles.delayTime}>
                    {delay}
                </span>
            </div>
        </div>
    );
}
 