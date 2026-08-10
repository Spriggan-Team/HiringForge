import React, { useState } from 'react'
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

//-- Services
import { useAppContext } from '../../../hooks/context';
import { navigateTo } from '../../../App';
import RouteScheme from '../../../route.scheme';

//-- Custom Components
import LanguageSelector from '../../../layout/components/selectors/language/language.selctor';
import AppIdentity from '../../../layout/components/identity/app.identity';
import AuthSwitcher from '../../../layout/components/navigation/auth/auth.switcher';

//-- Styles 

import styles from "./PublicNavBar.module.css"


interface PublicNavBarProps{
    className?: string
    backgroundColor?: string;
}


const PublicNavBar: React.FC<PublicNavBarProps> = ({
    className,
    backgroundColor
}) => {
    const {t} = useTranslation();
    const navigate = useNavigate();
    const {currentActor} = useAppContext();
    const [imgError, setImgError] = useState(false);

    //-- Generate initial
    const getInitials = () => {
        if(currentActor){
            const first = currentActor.firstName?.charAt(0) || '';
            const last =  currentActor.lastName.charAt(0) ||  '';
            return `${first}${last}`.toUpperCase() || 'C';
        }
        return 'C'
    };

    console.log(currentActor)

    return (
        <div 
            className={`${styles.container} ${className}`}
        >
            <nav 
                className={styles.navbar}
                style={{ background: backgroundColor }}
            >
                <AppIdentity />
                <div className={styles.actions}>
                    <LanguageSelector />
                        {
                            currentActor ?
                                currentActor.type === "candidate" ? (
                                    <div className={styles.avatarWrapper}>
                                        {currentActor.avatarUrl && !imgError ? (
                                            <img
                                                src={currentActor.avatarUrl}
                                                alt="Profil candidat"
                                                className={styles.avatarImg}
                                                onError={() => setImgError(true)}
                                            />
                                        ) : (
                                            <div className={styles.avatarFallback}>
                                                {getInitials()}
                                            </div>
                                        )}
                                            </div>
                                    ) : currentActor.type === "user" ? (
                                            <button
                                                className={styles.navToSpaceBtn}
                                                onClick={() => navigateTo(navigate, RouteScheme.userJobs)}
                                            >
                                                {t("global.buttons.goToMySpace", "Mon espace")}
                                            </button>
                                    ) : <></>
                            : <AuthSwitcher />
                        }
                </div>         
            </nav>
        </div>
    );
}
 
export default PublicNavBar;