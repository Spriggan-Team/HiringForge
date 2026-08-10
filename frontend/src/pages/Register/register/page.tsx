


import { useTranslation } from "react-i18next";
import { useNavigate } from "react-router-dom";

//-- services & Config
import RouteScheme from "../../../route.scheme";
import { navigateTo } from "../../../App";

//-- Custom Components
import InfoPill from "../../../layout/components/badges/pill/info.pill";
import AppIdentity from "../../../layout/components/identity/app.identity";
import AuthSwitcher from "../../../layout/components/navigation/auth/auth.switcher";
import LanguageSelector from "../../../layout/components/selectors/language/language.selctor";
import IllustratorPannel from "./components/panel/illustrator.panel";
import ProcessChecklistCard from "../../../layout/components/cards/processChecklistCard/process.checklist.card";
import Separator from "../../../layout/components/separator/separator";
import FeatureBadge from "../../../layout/components/badges/featureBadge/feature.badge";


//-- SVG Components
import CompanySVG from "/src/assets/svg/person/company-svgrepo-com.svg"
import CandidateSVG from "/src/assets/svg/person/candidate-for-elections-svgrepo-com.svg"
import SecuritySVG from "/src/assets/svg/security/secure-svgrepo-com.svg"
import ConfigSVG from "/src/assets/svg/menu/config-svgrepo-com.svg"
import SimpleHandLikeSVG from "/src/assets/svg/check/simple-like-hand-line-drawing-svgrepo-com.svg"


//--Images
import WebPageImage from "/src/assets/images/Job-pages.png"


//-- styles
import styles from "./styles.module.css"



const RegisterationEntry = () => {
    const { t } = useTranslation();
    const navigate = useNavigate();

    return (
        <div className={styles.container}>
            
            <nav className={styles.navbar}>
                <AppIdentity onClick={()=>navigateTo(navigate, RouteScheme.jobs)} />
                <div className={styles.actions}>
                    <LanguageSelector />
                    <AuthSwitcher />
                </div>
            </nav>
                
            <main className={styles.main}>
                {/** Head Section */}
                <div className={styles.headSection}>
                    <div>
                        <InfoPill text={t("register.subtext.createAccountBadge")}/>
                        <div className={styles.welcomeTxt}>
                            <p>
                                {t("register.welcome.head")}
                                <br /> <span className={styles.appName} >{t("global.appName")}</span>
                            </p>
                            <p className={styles.selectProfileTxt}>{t("register.text.selectProfile")}</p>
                            <p className={styles.description}>{t("register.welcome.description")}</p>
                        </div>
                    </div>

                    <div>
                        <IllustratorPannel image={WebPageImage} width={750} height={450} />
                    </div>
                </div>

                {/** Separator Section */}
                <div className={styles.separators}>
                    <Separator />
                    <span className="boldSecondaryTxt">{t("register.text.separatorTitle")}</span>
                    <Separator />
                </div>

                {/** Card Section */}
                <div className={styles.cardSection}>
                    <ProcessChecklistCard
                        svg={CompanySVG}
                        title={t("register.cardSection.recruiter.title")}
                        description={t('register.cardSection.recruiter.description')}
                        
                        primaryColor="#0C51FB"
                        secondaryColor="#E4EDFC"
                        
                        checkList={[
                            t("register.cardSection.recruiter.checkList.1"),
                            t("register.cardSection.recruiter.checkList.2"),
                            t("register.cardSection.recruiter.checkList.3"),
                            t("register.cardSection.recruiter.checkList.4"),
                        ]}
                        buttonText={t("register.cardSection.recruiter.action")}

                        onClick={()=> navigate(RouteScheme.userRegister)}

                    />
                    <ProcessChecklistCard
                        svg={CandidateSVG}
                        title={t("register.cardSection.candidate.title")}
                        description={t('register.cardSection.candidate.description')}
                        
                        primaryColor="#37B778"
                        secondaryColor="#DEF5EB"

                        checkList={[
                            t("register.cardSection.candidate.checkList.1"),
                            t("register.cardSection.candidate.checkList.2"),
                            t("register.cardSection.candidate.checkList.3"),
                            t("register.cardSection.candidate.checkList.4"),
                        ]}

                        fillButton={false}
                        fillButtonForegroundColor="transparent"

                        buttonText={t("register.cardSection.candidate.action")}
                        onClick={()=> navigate(RouteScheme.candidateRegister)}
                    />
                    <ProcessChecklistCard
                        svg={CandidateSVG}
                        title={t("register.cardSection.director.title")}
                        description={t('register.cardSection.candidate.description')}
                        
                        primaryColor="#743AEF"
                        secondaryColor="#F2EDFC"

                        checkList={[
                            t("register.cardSection.director.checkList.1"),
                            t("register.cardSection.director.checkList.2"),
                            t("register.cardSection.director.checkList.3"),
                            t("register.cardSection.director.checkList.4"),
                        ]}

                        fillButton={false}
                        fillButtonForegroundColor="#F2EDFC"

                        buttonText={t("register.cardSection.director.action")}
                        onClick={()=>navigate(RouteScheme.directorRegister)}
                    />
                </div>
                
                {/** Bottom Badge */}
                <div className={styles.featureSection}>
                    <FeatureBadge
                        svg={SecuritySVG}
                        title={t("register.features.security.title")}
                        description={t("register.features.security.subtext")}
                    />
                    <FeatureBadge
                        svg={ConfigSVG}
                        title={t("register.features.configuration.title")}
                        description={t("register.features.configuration.subtext")}
                    />
                    <FeatureBadge
                        svg={SimpleHandLikeSVG}
                        title={t("register.features.interface.title")}
                        description={t("register.features.interface.subtext")}
                    />
                </div>
            </main>
        </div>
    );
}
 
export default RegisterationEntry;