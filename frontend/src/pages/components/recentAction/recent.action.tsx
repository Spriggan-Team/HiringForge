import { useTranslation } from "react-i18next";

//-- Services
import type { PersonActionType } from "../../../features/shared/account";

//-- CSS Module
import styles from "./RecentAction.module.css"
import type { AppNotificationType } from "../../../features/notfication/notification";


/**-- Item -- */


interface ActionItemProps{
    person?: string|null;
    title: string;
    delay: string;
    type: AppNotificationType;
    className?:string;
    onClick?: React.MouseEventHandler<HTMLDivElement>
}


const actionConfigs: Partial<Record<AppNotificationType, { icon: string; className: string }>> = {
  JOB_APPLIED: { icon: "👤", className: styles.postulate },
  INTERVIEW_SCHEDULED: { icon: "↗️", className: styles.createInterview },
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
            case 'JOB_APPLIED':
                return t("notification.postulate", { person, offer: title });
            case 'INTERVIEW_SCHEDULED':
                return t("notification.createInterview", { person, interview: title });
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