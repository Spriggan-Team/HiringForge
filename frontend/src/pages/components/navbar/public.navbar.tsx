import React, { useEffect, useRef, useState } from 'react'
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

//-- Services
import { useAppContext } from '../../../hooks/context';
import { navigateTo } from '../../../App';
import CandidatesQueries from '../../../api/services/candidate/queries';
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

    const imageUrlRef  = useRef<string>(null)
    const [imageUrl, setImageUrl] = useState("");
    const [imgError, setImgError] = useState(false);


    //-- Generate initial
    const initials = currentActor
        ? `${currentActor.firstName?.[0] ?? ''}${currentActor.lastName?.[0] ?? ''}`.toUpperCase() || 'C'
        : 'C';

    useEffect(() => {
        if (!currentActor || currentActor.type !== "candidate") {
            setImageUrl("");
            return;
        }

        let objectUrl: string | null = null;

        const loadImage = async () => {
            try {
                setImgError(false);

                const blob = await CandidatesQueries.getCandidateProfileImage(
                    currentActor.id
                );

                objectUrl = URL.createObjectURL(blob);

                setImageUrl(objectUrl);
            } catch (error) {
                setImageUrl("");
                setImgError(true);

                console.warn("Unable to load candidate profile image", error);
            }
        };

        loadImage();

        return () => {
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
            }
        };
    }, [currentActor]);


    //-- Render

    return (
        <nav 
            className={`${styles.container} ${className}`}
        >
            <nav 
                className={styles.navbar}
                style={{ backgroundColor }}
            >
                {/** Leading */}
                <AppIdentity onClick={()=> navigateTo(navigate, RouteScheme.jobs)} />
                

                
                <div className={styles.actions}>
                                                            {/** Options */}
                    {
                        currentActor ? (
                            <div className={styles.navigation}>
                                <span
                                    className={styles.navItem}
                                    onClick={()=> navigateTo(navigate, RouteScheme.candidateApplications)}
                                >
                                    {t("global.menu.myApplications")}
                                </span>
                                
                                <span
                                    className={styles.navItem}
                                    onClick={()=>navigateTo(navigate, RouteScheme.candidateOffers)}
                                >
                                    {t('global.menu.myOffers')}
                                </span>
                                
                                <span
                                    className={styles.navItem}
                                    onClick={()=>navigateTo(navigate, RouteScheme.candidateInterviews)}
                                >
                                    {t('global.menu.MyInterviews')}
                                </span>
                            </div>
                        ) :<></>
                    }
                    <LanguageSelector />
                        {
                            currentActor ?
                                currentActor.type === "candidate" ? (
                                    <div 
                                        style={{cursor: 'pointer'}}
                                        onClick={()=>navigateTo(navigate, RouteScheme.candidateProfile)}
                                        className={styles.avatarWrapper}
                                    >
                                        {imageUrl && !imgError ? (
                                            <img
                                                src={imageUrl}
                                                alt="Profil candidat"
                                                className={styles.avatarImg}
                                                onError={() => setImgError(true)}
                                            />
                                        ) : (
                                            <div className={styles.avatarFallback}>
                                                {initials}
                                            </div>
                                        )}
                                    </div>
                                ) 
                                : currentActor.type === "user" ? (
                                    <button
                                        className={styles.navToSpaceBtn}
                                        onClick={() => navigateTo(navigate, RouteScheme.userJobs)}
                                    >
                                        {t("global.buttons.goToMySpace", "Mon espace")}
                                    </button>
                                ) 
                                : <></>
                            : <AuthSwitcher />
                        }
                </div>         
            </nav>
        </nav>
    );
}
 
export default PublicNavBar;