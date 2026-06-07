
import { Link } from "react-router-dom";
import { useTranslation } from "react-i18next";
import RouteScheme from "../../../../route.scheme";

import styles from "./style.module.css"


const AuthSwitcher = () => {
    const { t } = useTranslation();

    return ( 
        <div className={styles.logInBtn}>
            <span>{t("register.subtext.alreadyHaveAccount")}</span><Link to={RouteScheme.login}>{t("register.buttons.logbtn")}</Link>
        </div>
    );
}
 
export default AuthSwitcher;