import { useTranslation } from "react-i18next";

//-- Services
import type { PersonActionType } from "../../../features/shared/account";

//-- CSS Module
import styles from "./RecentAction.module.css"


/**-- Item -- */


interface ActionItemProps{
    person?: string|null;
    title: string;
    delay: string;
    type: PersonActionType;
    className?:string;
    onClick?: React.MouseEventHandler<HTMLDivElement>
}


const actionConfigs: Record<PersonActionType, { icon: string; className: string }> = {
    "postulate": { icon: "👤", className: styles.postulate },
    "create-interview": { icon: "↗️", className: styles.createInterview },
    "publish-offer": { icon: "📄", className: styles.publishOffer },
    "confirm-interview": { icon: "✅", className: styles.confirmInterview },
};


const RecentAction: React.FC<ActionItemProps> = ({
    title,
    type,
    person,
    delay,
    onClick,
    className
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
            className={`${styles.item} ${onClick ? styles.clickable : ""} ${className}`}
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
 

export default RecentAction;