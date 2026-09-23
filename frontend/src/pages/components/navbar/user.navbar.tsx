
import { useCallback, useEffect} from "react";
import type { TFunction } from "i18next";
import { useTranslation } from "react-i18next";

//-- Services
import { useAppContext, useCurrentUser } from "../../../hooks/context";
import AuthServices from "../../../api/services/auth/auth";
import RouteScheme from "../../../route.scheme";
import NotificationQueries from "../../../api/services/notification/queries";
import { useAppNavigate } from "../../../hooks/navigation";
import type { CurrentUser } from "../../../features/shared/account";

//-- Custom Components
import MenuDrawer, { MenuDrawerBody, MenuDrawerItem, MenuDrawerTrigger } from "../../../layout/components/menu/dropdown/menu.dropdown";
import BasicInput from "../../../layout/components/form/input/basic.input";
import SearchSVGComponent from "/src/assets/svg/menu/search-svgrepo-com.svg?react"
import NotificationRingSVGComponent from "/src/assets/svg/menu/alarm-alert-bell-notification-warning-svgrepo-com.svg?react"

//-- SVG Components
// import DownArrowSVGComponent from "/src/assets/svg/menu/down-arrow-5-svgrepo-com.svg?react"
import ProfileSVGComponent from "/src/assets/svg/person/profile-svgrepo-com.svg?react"

//-- CSS Styles
import styles from "./UserNavBar.module.css"



export interface NavBarProps{
    className?: string;
}



const UserNavBar: React.FC<NavBarProps> = ({
    className
}) => {
    const { t } = useTranslation();
    const user = useCurrentUser();
    const navigate = useAppNavigate();

    const { 
        navbar, 
        setPopup,
        kpiData,
        notificationCount,
        setNotificationCount
    } = useAppContext();


    //-- Text
    const postsTxt = t('userHome.header.leading.open_post', { count: kpiData?.publicOffers ??  0 });
    const candTxt = t('userHome.header.leading.candidature', {
        count:
            (kpiData?.applicationCount ?? 0) -
            (kpiData?.rejectedApplicationCount ?? 0),
    });
    
    const informationTxt = t('userHome.header.leading.summary_sentence', { 
        openPostsText: postsTxt, 
        candidaturesText: candTxt 
    });
    
    
    //-- Handlers
    const handleLogout = useCallback(async()=>{
        try{
            await AuthServices.logout();
        }
        catch(error){
            console.warn("Somethinf went wrong", error)
        }
        localStorage.clear();
        setPopup({ status: "success", message: t("global.messages.logoutSuccess") });
        navigate(RouteScheme.login);
    },[]);


    const countUnreadNotification = async ()=>{
        try{
            const count = await NotificationQueries.countUnreadNotification();
            setNotificationCount(count.unreadCount);
        }
        catch(error){
            console.log("Something went wrong while counting unread notification", error)
        }
    }

    useEffect(()=>{
        countUnreadNotification();
    },[]);

    console.log({user})

    return (
        <div className={`${styles.container} ${className}`}>
            
            <div className={styles.leadingSection}>
                {
                    (
                        <>
                            <h1 className={styles.title}>
                                {
                                    navbar?.title ? 
                                        navbar.title
                                        : navbar?.title === null ?
                                            null
                                            :t("global.messages.welcome", { name: user.company.name })
                                }
                            </h1>
                            { 
                                navbar?.description ?
                                typeof navbar.description === "string" ? 
                                    (
                                        <p className={styles.desc}>{navbar.description}</p>
                                    )
                                    : navbar.description
                                : navbar?.description === null 
                                    ? null : informationTxt
                            }
                        </>
                    )
                }
            </div>

            <div className={styles.actionSection}>
                <div className={styles.search}>
                    <BasicInput
                        svg={SearchSVGComponent}
                        backgroundColor="white"
                        className={`${styles.input} input`}
                        placeholder={t("userHome.inputs.search.placeholder")}
                    />
                </div>

                <div className={styles.notification}>
                    <NotificationRingSVGComponent
                        width={25}
                        height={25}
                        className={styles.notficationRing}
                    />

                    {notificationCount > 0 && (
                        <span className={styles.notificationBadge}>
                            {notificationCount > 99 ? '99+' : notificationCount}
                        </span>
                    )}
                </div>
                {/** Profil menu */}
                <ProfileMenu 
                    t={t}
                    user={user}
                    handleLogout={handleLogout}
                    handleNavigation={(route)=>{
                        navigate(route)
                    }}
                />
            </div>

        </div>
    );
}
 
export default UserNavBar;



/**  */

interface ProfileMenuProps{
    t: TFunction;
    user: CurrentUser;
    handleLogout: ()=> void;
    handleNavigation: (route: string) => void;
}

const ProfileMenu: React.FC<ProfileMenuProps> = ({ 
    t,
    user,
    handleLogout,
    handleNavigation
}) => {
    return (
        <div>
            
                {/** User profile */}
                <MenuDrawer>
                    {/* On laisse le Trigger gérer la flèche grâce à displayArrowDown (par défaut à true) */}
                    <MenuDrawerTrigger className={styles.profileTrigger}>
                        <div className={styles.profileInfo}>
                            <img 
                                alt="Photo de profil de Thomas"
                                className={styles.avatar}
                                src={user.avatarUrl ?? "/src/assets/images/pngtree-glitch-effect-avatar-profile-vector-png-image_15605578.png"} 
                            />
                            <div className={styles.profileDetails}>
                                <span className={styles.username}>Thomas</span>
                                <span className={styles.role}>Recruteur</span>
                            </div>
                        </div>
                    </MenuDrawerTrigger>

                    {/* menu body*/}
                    <MenuDrawerBody 
                        right={0}
                        className={styles.profileDropdown}
                        position="initial-absolute"
                    >
                        {/** Mon profil */}
                        <MenuDrawerItem
                            onClick={()=> handleNavigation(RouteScheme.userProfile)}
                            className={`${styles.drawerItem} ${styles.myProfile}`}
                        >
                            <ProfileSVGComponent height={15} width={15} />
                            <span>{t("global.menu.myProfile", "Mon profil")}</span>
                        </MenuDrawerItem>
                        {/** Logout */}
                        <MenuDrawerItem
                            onClick={handleLogout}
                            className={`${styles.drawerItem} ${styles.logoutItem}`}
                        >
                            <svg 
                                width="16" 
                                height="16" 
                                viewBox="0 0 24 24" 
                                fill="none" 
                                stroke="currentColor" 
                                strokeWidth="2" 
                                strokeLinecap="round" 
                                strokeLinejoin="round"
                            >
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <polyline points="16 17 21 12 16 7" />
                                <line x1="21" y1="12" x2="9" y2="12" />
                            </svg>
                            <span>{t("global.connexion.logout")}</span>
                        </MenuDrawerItem>
                    </MenuDrawerBody>
                </MenuDrawer>
        </div>
    );
}
