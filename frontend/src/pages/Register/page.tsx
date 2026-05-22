
import { useState } from "react";


//-- CORE COMPONENTS 
import IdentityDetails from "./components/identity/Identity.details";

//Custom - React Component
import BasicInput from '../../layout/components/form/input/basic.input';
import DownloadButton from "../../layout/components/buttons/download/download.button";
import SecurityBadge from "../../layout/components/badges/security.badge";
import SimpleButton from "../../layout/components/buttons/simple/simple.button";


//SVG - Components
import LogoSVG from '../../assets/custom-logo.svg';
import BrowserSVG from '../../assets/svg/net/internet-svgrepo-com.svg';

//-- CSS Styles
import styles from "./style.module.css"
import StageTitle from "./components/stage/stage.title";


const Register = () => {
    const [step, setStep] = useState();
    const [language, setLanguage] = useState("Français");

    return (
        <div className={styles.container}>
            <nav>
                <div className={styles.leading}>
                    <LogoSVG width={45} />
                    <h1>DigitalCop ATS</h1>
                </div>

                <div className={styles.actions}>
                    <div className={styles.translateSection}>
                        <BrowserSVG />
                        <span>{language}</span>
                    </div>

                    <div className={styles.logInBtn}>
                        <span>Déja un compte ?</span><span>Se connecter</span>
                    </div>
                </div>         
            </nav>

           {/* MAIN CONTENT */}

            <div className={styles.mainContainer}>
                
                {/* PROCESS DESCRIPTION */}
                <div className={styles.infoBox}>
                    <StageTitle step={1} txt="" />
                    <StageTitle step={2} txt="" />
                    <StageTitle step={3} txt="" />
                </div>
                
                {/* MAIN FORM */}

                <div className={styles.mainForm}>
                    <div className={styles.header}>
                        <h3>Créer votre compte entreprise</h3>
                        <div className={styles.desc}>
                            <p>Rejoindre digitalCop en quelques étapes</p>
                            <div className={styles.progessContainer}>
                                <span>Etape 1 sur {step}</span>
                                <div></div>
                            </div>
                        </div>
                    </div>
                    <IdentityDetails />
                </div>


                {/* SIDE FORM */}

                <div className={styles.sideForm}>
                    {/* TOP */}
                    <div className={styles.top}>
                        <h2>Branding de l'entreprise</h2>
                         {/* DOWNLOAD LOGO SECTION */}
                        <div className={styles.downloadLogoSection}>
                            <span>Logo</span>
                            <div className={styles.downloadBox}>
                                <LogoSVG />
                                <DownloadButton />
                                <span>PNG, JPG ... </span>
                            </div>
                        </div>

                         {/* IMAGES DOWNLOAD SECTION */}
                        <div className={styles.downloadImageSection}>
                            <h2>Photo de présentation <span>(optionnel)</span></h2>
                            <div>
                                <img 
                                    alt=""
                                    src="https://images.unsplash.com/photo-1779243829348-85bf26cff23b?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                />
                                <img 
                                    alt=""
                                    src="https://images.unsplash.com/photo-1773332585788-9104ec6f38ef?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDF8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                />
                                <div className={styles.addBox}>
                                    <span>Ajouter des photos</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    {/* BOTTOM */}
                    <div className={styles.bottom}>
                        {/* ADDRESS SECTION */}
                        <h2>Addrèsse de l'entreprise</h2>
                        <div>
                            <BasicInput label="Pays" placeholder="France" />
                            <div>
                                <BasicInput label="Code postal" placeholder="75002" />
                                <BasicInput label="Ville" placeholder="Paris" />
                            </div>
                            <BasicInput label="Address" placeholder="32 rue st michelle" />
                        </div>
                    </div>

                    {/* OVER - ABSOLUTE ELEMENT */}
                    <div>
                        <SecurityBadge />
                    </div>
                </div>

            </div>

            {/*  FOOTER */}
            <div>
                <SimpleButton />
                <div>
                    <span> Etape1 </span>
                    <span> Etape2 </span>
                    <span> Etape3 </span>
                </div>
                <SimpleButton />
            </div>
        </div>
    );
}
 
export default Register;