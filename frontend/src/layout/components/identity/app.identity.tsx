import React from 'react'

//- SVG Components
import LogoSVG from '/src/assets/custom-logo.svg';

//-- styles 
import styles from "./style.module.css"
import { useTranslation } from 'react-i18next';


interface AppIdentityProps{
    onClick?: ()=>void
}

const AppIdentity: React.FC<AppIdentityProps>  = ({
    onClick
}) => {
    const {t} = useTranslation();

    return ( 
        <div 
            onClick={()=> onClick?.()}
            className={styles.leading}
            style={{ cursor: onClick ? 'pointer' : 'default'}}
        >
            <LogoSVG width={45} height={45} />
            <h1>{t("global.appName")}</h1>
        </div>
    );
}
 
export default AppIdentity ;