

import { Link } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { useRef, useState } from "react";
import { useAppContext } from "../../hooks/context";


//--Config Object
import RouteScheme from "../../route.scheme";


//-- CORE COMPONENTS 
import AccountAccess from "./components/access/account.access";


//-- Custom - React Component
import Gauge from "../../layout/components/progress/gauge/gauge";
import SideForm from "./components/sideform/side.form";
import StageTitle from "../../layout/components/indicators/stage/stage.title";
import StatusItem from "../../layout/components/indicators/statusItem/status.item";


//-- SVG - Components
import LogoSVG from '/src/assets/custom-logo.svg';
import BrowserSVG from '/src/assets/svg/net/internet-svgrepo-com.svg';
import DownArrowSVG from '/src/assets/svg/arrows/down-arrow-5-svgrepo-com.svg';


//-- Images - Ressources
import OfficeWorkerImage from "../../assets/images/office-worker.png"


//-- CSS Styles
import  styles from "./style.module.css"
import  IdentityDetail from "./components/identity/identity.details";
import  SecureAccount from "./components/security/LockAccount";
import AuthServices from "../../api/services/auth";
import { objectToFormData } from "../../utils/convertor";



const REGISTERING_TOTAL_STEP = 3;


export interface AsideFormState{
        country: string,
        postalCode: string,
        city: string,
        street: string,
        logo: File | null,
        images:  File[],
}


const Register = () => {
    const { t } = useTranslation();
    const {  setPopup  } = useAppContext();

    const formData = useRef<FormData>(new FormData());

    const [language, setLanguage] = useState("Français");
    const [currentStep, setCurrentStep] = useState({ max: 1, current: 1 });

    const [asideFormState, setAsideFormState] = useState<AsideFormState>({
        country: "",
        postalCode: "",
        city: "",
        street: "",
        logo: null as File | null,
        images: [] as File[],
    });
    

    return (
        <div className={styles.container}>
            <nav className={styles.navbar}>
                <div className={styles.leading}>
                    <LogoSVG width={45} height={45} />
                    <h1>DigitalCop ATS</h1>
                </div>

                <div className={styles.actions}>
                    <div className={`${styles.translateSection} faint-border`}>
                        <BrowserSVG className={styles.browserSVG} width={25} height={25}  />
                        <span>{language}</span>
                        <DownArrowSVG className={styles.arrowSVG} width={25} height={25} />
                    </div>

                    <div className={styles.logInBtn}>
                        <span>{t("register.subtext.alreadyHaveAccount")}</span><Link to={RouteScheme.login}>{t("register.buttons.logbtn")}</Link>
                    </div>
                </div>         
            </nav>

           {/* MAIN CONTENT */}

            <div className={`faint-border ${styles.mainContainer}`}>
                
                {/* PROCESS DESCRIPTION */}
                <div className={styles.infoBox}>
                    <div className={styles.stagesSection}>
                        <StageTitle
                            step={1}
                            active={currentStep.current === 1}
                            textColor={currentStep.current > 1 ?  "#94a3b8" : undefined}
                            backgroundColor={currentStep.current != 1 ? "#e0e7ff": undefined}
                            txt={t("register.processDescription.one")}
                            onClick={()=>{
                                if(currentStep.max >= 1){
                                    setCurrentStep(prev => ({
                                        ...prev,
                                        current: 1
                                    }))
                                }
                            }}
                        />

                        <StageTitle
                            step={2}
                            active={currentStep.current === 2}
                            textColor={currentStep.max > 2 && currentStep.current != 2 ?  "#94a3b8" : undefined}
                            backgroundColor={currentStep.max > 2 && currentStep.current != 2 ? "#e0e7ff" : undefined}
                            txt={t("register.processDescription.two")}
                            disableCursorPointer={
                                currentStep.max < 2
                            }
                            onClick={()=>{
                                if(currentStep.max >= 2){
                                    setCurrentStep(prev => ({
                                        ...prev,
                                        current: 2
                                    }))
                                }
                            }}
                        />

                        <StageTitle
                            step={3}
                            active={currentStep.current === 3}
                            txt={t("register.processDescription.three")}
                            textColor={currentStep.max == 3 && currentStep.current < 3 ?  "#94a3b8" : undefined}
                            backgroundColor={currentStep.max == 3 && currentStep.current < 3 ? "#e0e7ff": undefined}
                            disableCursorPointer={
                                currentStep.max < 3
                            }
                            onClick={()=>{
                                if(currentStep.max >= 3){
                                    setCurrentStep(prev => ({
                                        ...prev,
                                        current: 3
                                    }))
                                }
                            }}
                        />
                    </div>
                    <div className={styles.statusItemSection}>
                        <StatusItem text={t("register.processDescription.overall.0")}/>
                        <StatusItem text={t("register.processDescription.overall.1")}/>
                        <StatusItem text={t("register.processDescription.overall.2")}/>
                    </div>
                    <img src={OfficeWorkerImage} alt="" />
                </div>
                
                {/* MAIN FORM */}

                <div className={styles.mainForm}>
                    <div className={styles.header}>
                        <h3 className={styles.formTitle}>{t("register.form.title")}</h3>
                        <div className={styles.desc}>
                            <p>{t("register.form.subtitle")}</p>
                            <div className={styles.progessContainer}>
                                <span>{t("register.form.currentStep", {count: currentStep.current, totalCount: 3})}</span>
                                <Gauge 
                                    width={"50%"} height={2.5}
                                    activeColor="#003DE7"
                                    foregroundColor="#D9D9D9"
                                    percent={currentStep.current / REGISTERING_TOTAL_STEP} 
                                />
                            </div>
                        </div>
                    </div>
                    <div className={styles.form}>
                        {currentStep.current == 1 ?
                            <AccountAccess 
                                formData={formData.current}
                                onNext={()=>{
                                    setCurrentStep(prev => ({ current: 2, max: 2 > prev.max ? 2 : prev.max }))
                                }}
                            />
                            : currentStep.current == 2 ?
                                <IdentityDetail
                                    formData={formData.current} 
                                    onNext={async ()=>{
                                        try{
                                            await AuthServices.askVerificationCode(formData.current.get("email") as string, "SIGNUP");
                                            setCurrentStep(prev => ({ current: 3, max: 3 > prev.max ? 3 : prev.max }))
                                            setPopup({status: "error", message: t("register.apiResponse.verifyMailBox.success")})
                                        }
                                        catch(error){
                                            setPopup({
                                                status: "error",
                                                message: t("global.messages.error")
                                            })
                                        }
                                    }}
                                />
                                : currentStep.current == 3 ?
                                    <SecureAccount 
                                        formData={formData.current}
                                        onNext={async ()=>{
                                            try{
                                                const res = await AuthServices.register(objectToFormData(asideFormState, formData.current))
                                                setPopup({status: "error", message: t("register.apiResponse.registering.success")})
                                                localStorage.setItem("userId", JSON.stringify(res?.id))
                                            }
                                            catch(error){}
                                        }}
                                    />
                                    :<></>    
                        }
                    </div>
                </div>


                {/* SIDE FORM */}
                <SideForm form={asideFormState} setForm={setAsideFormState}   />
            </div>
        </div>
    );
}
 
export default Register;

{/*  FOOTER */}
{/* <div className={styles.footer}>
    <SimpleButton />
    <div>
        <span> Etape1 </span>
        <span> Etape2 </span>
        <span> Etape3 </span>
    </div>
    <SimpleButton />
</div> */}