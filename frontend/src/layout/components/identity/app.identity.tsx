
//- SVG Components
import LogoSVG from '/src/assets/custom-logo.svg';

//-- styles 
import styles from "./style.module.css"
import { useTranslation } from 'react-i18next';


const AppIdentity  = () => {
    const {t} = useTranslation();

    return ( 
        <div className={styles.leading}>
            <LogoSVG width={45} height={45} />
            <h1>{t("global.appName")}</h1>
        </div>
    );
}
 
export default AppIdentity ;