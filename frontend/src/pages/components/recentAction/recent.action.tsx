import { useTranslation } from "react-i18next";


//-- Services
import { Notifications, type NotificationTypes, type NotificationValueType } from "../../../features/notfication/notification";

// SVG - Components
import UserPlus from "/src/assets/svg/notifications/user-plus-alt-1-svgrepo-com.svg?react"
import CalendarCheck from "/src/assets/svg/notifications/calendar-check-svgrepo-com.svg?react"
import UserX from "/src/assets/svg/notifications/user-block-svgrepo-com.svg?react"
import FileCheck from "/src/assets/svg/notifications/file-check-alt-svgrepo-com.svg?react";
import AlertSVG from "/src/assets/svg/security/alert-rhombus-svgrepo-com.svg?react"
import FileX from "/src/assets/svg/notifications/file-xmark-alt-1-svgrepo-com.svg?react"


//-- CSS Module
import styles from "./RecentAction.module.css"
import type React from "react";
import type { ParseKeys } from "i18next";
import { formatRemainingTime } from "../../../utils/format";



/**
 * ----------------------
 * Config
 * ---------------------
 */


interface NotificationConfig{
  icon: React.FC<React.SVGProps<SVGSVGElement>>;
  className: string;
  translationKey: ParseKeys;
  getParams: (notification: NotificationTypes) => Record<string, unknown>;
}



export const NOTIFICATION_CONFIGS: Record<NotificationValueType, NotificationConfig> = {
  [Notifications.JOB_APPLIED]: {
    icon: UserPlus,
    className: styles.postulate,
    translationKey: 'notifications.postulate',
    getParams: (n) => {
      if (n.type !== Notifications.JOB_APPLIED) return {};
      return {
        person: getPersonName(n, n.data.candidateName),
        offer: n.data.jobTitle,
      };
    },
  },

  [Notifications.INTERVIEW_SCHEDULED]: {
    icon: CalendarCheck,
    className: styles.createInterview,
    translationKey: 'notifications.createInterview',
    getParams: (n) => {
      if (n.type !== Notifications.INTERVIEW_SCHEDULED) return {};
      return {
        person: getPersonName(n, n.data.recruiterName),
        interview: n.data.jobTitle,
      };
    },
  },

  [Notifications.CANDIDATE_REJECTED]: {
    icon: UserX,
    className: styles.rejected,
    translationKey: 'notifications.candidateRejected',
    getParams: (n) => {
      if (n.type !== Notifications.CANDIDATE_REJECTED) return {};
      return {
        company: n.data.companyName,
        offer: n.data.jobTitle,
      };
    },
  },

  [Notifications.EMPLOYMENT_OFFER_GENERATED]: {
    icon: FileCheck,
    className: styles.employmentOffer,
    translationKey: 'notifications.employmentOfferGenerated',
    getParams: (n) => {
      if (n.type !== Notifications.EMPLOYMENT_OFFER_GENERATED) return {};
      return {
        company: n.data.companyName,
        offer: n.data.jobTitle,
      };
    },
  },

  [Notifications.EMPLOYMENT_OFFER_CANCELLED]: {
    icon: FileX,
    className: styles.employmentOfferCancelled,
    translationKey: 'notifications.employmentOfferCancelled',
    getParams: (n) => {
      if (n.type !== Notifications.EMPLOYMENT_OFFER_CANCELLED) return {};
      return {
        company: n.data.companyName,
        offer: n.data.jobTitle,
      };
    },
  },

  [Notifications.SYSTEM_ALERT]: {
    icon: AlertSVG,
    className: styles.systemAlert,
    translationKey: 'notifications.systemAlert',
    getParams: (n) => {
      if (n.type !== Notifications.SYSTEM_ALERT) return {};
      return {
        message: n.data.message,
      };
    },
  },

  [Notifications.JOB_APPLICATIONS_STATUS_SHIFT]: {
    icon: CalendarCheck,
    className: styles.statusShift,
    translationKey: 'notifications.applicationStatusShift',
    getParams: (n) => ({}),
  },
};



//-- Utils for correct name retreival
const getPersonName = (notification: NotificationTypes, fallbackName?: string): string => {
  if (notification.account?.firstName || notification.account?.lastName) {
    return `${notification.account.firstName} ${notification.account.lastName}`.trim();
  }
  return fallbackName ?? '';
};


/**
 * ----------------------
 * Component
 * ---------------------
 */
interface ActionItemProps {
  notification: NotificationTypes;
  className?: string;
  onClick?: (notification: NotificationTypes) => void;
}

const RecentAction: React.FC<ActionItemProps> = ({
    notification,
    onClick,
    className
}) => {
    const {t} = useTranslation();
    const config = NOTIFICATION_CONFIGS[notification.type];

    if (!config) {
        return null;
    }
    const Icon = config.icon;

    // console.log("Notification Details ", notification);
    return (
        <div 
            onClick={() => onClick?.(notification)}
            className={`${styles.item} ${onClick ? styles.clickable : ""} ${className}`}
        >
            {/* Icon  */}
            <div className={`${styles.svg} ${config?.className}`}>
                <div>
                    {<Icon height={25} width={25} /> }
                </div>
            </div>
            
            <div className={styles.main}>
                <span className={styles.title}>
                    {t(config.translationKey, config.getParams(notification))}
                </span>
                <span className={styles.delayTime}>
                    {formatRemainingTime(notification.createdAt)}
                </span>
            </div>
        </div>
    );
}
 

export default RecentAction;