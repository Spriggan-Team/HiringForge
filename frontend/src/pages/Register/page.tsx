


//-- Custom Components
import { useTranslation } from "react-i18next";
import AppIdentity from "../../layout/components/identity/app.identity";
import AuthSwitcher from "../../layout/components/navigation/auth/auth.switcher";
import LanguageSelector from "../../layout/components/selectors/language/language.selctor";

//-- styles
import styles from "./styles.module.css"


const RegisterationEntry = () => {
    const { t } = useTranslation();

    return (
        <div className={styles.container}>
            
            <nav className={styles.navbar}>
                <AppIdentity />
                <div className={styles.actions}>
                    <LanguageSelector />
                    <AuthSwitcher />
                </div>
            </nav>
                
            <main>
                <div className={styles.headSection}>
                    <div className={styles.welcomeTxt}>
                        <span>{t("register.welcome.head")} {t("global.appName")}</span>
                        <p>{t("register.welcome.description")}</p>
                    </div>
                </div>
            </main>
        </div>
    );
}
 
export default RegisterationEntry;