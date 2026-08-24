
import { useTranslation } from "react-i18next";

// SVG - COmponents
import PasswordSVG from "/src/assets/svg/security/password-svgrepo-com.svg?react"
import SafeSecureSVG from "/src/assets/svg/security/security-safe-svgrepo-com.svg?react"

import styles from "./style.module.css"


const SecurityBadge = () => {
    const {t} = useTranslation();

    return ( 
        <div className={`${styles.container} faint-border`}>
            <PasswordSVG  width={25} height={25}/>
            <span className={styles.txt}>{t("global.messages.secureData")}</span>
            <SafeSecureSVG width={25} height={25}/>
        </div>
    );
}
 
export default SecurityBadge;