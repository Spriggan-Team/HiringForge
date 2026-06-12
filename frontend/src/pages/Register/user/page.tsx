

import { Link, useNavigate } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { useCallback, useEffect, useRef, useState } from "react";
import { useAppContext } from "../../../hooks/context";


//--Config Object
import RouteScheme from "../../../route.scheme";


//-- CORE COMPONENTS 
import AccountAccess from "./components/access/account.access";


//-- Custom - React Component
import Gauge from "../../../layout/components/progress/gauge/gauge";
import SideForm from "./components/sideform/side.form";
import StageTitle from "../../../layout/components/indicators/stage/stage.title";
import StatusItem from "../../../layout/components/indicators/statusItem/status.item";
import LanguageSelector from "../../../layout/components/selectors/language/language.selctor";
import  CompanyIdentityDetail from "./components/identity/company/company.identity.details";


//-- services
import AuthServices from "../../../api/services/auth/auth";
import { objectToFormData } from "../../../utils/convertor";
import { AccountAlreadyRegistered, CompanyAlreadyRegistered, InvalidOTP, RessourceCreationFailed } from "../../../api/services/auth/exceptions";

//-- SVG - Components
import SecureAccount from "./components/security/LockAccount";

//-- Images - Ressources
import OfficeWorkerImage from "/src/assets/images/office-worker.png"


//-- CSS Styles
import  styles from "./style.module.css"
import AuthSwitcher from "../../../layout/components/navigation/auth/auth.switcher";
import AppIdentity from "../../../layout/components/identity/app.identity";
import UserProfileIdentity from "./components/identity/user/user.profile";




const REGISTERING_TOTAL_STEP = 4;


export interface AsideFormState{
        country: string,
        postalCode: string,
        city: string,
        street: string,
        logo: File | null,
        images:  File[],
}


const UserRegister = () => {
    const { t } = useTranslation();
    const { setLoading, setPopup  } = useAppContext();

    const navigation = useNavigate();
    const formData = useRef<FormData>(new FormData());

    const [currentStep, setCurrentStep] = useState({ max: 1, current: 1 });

    const asideFormRef = useRef<HTMLFormElement | null>(null);
    const [asideFormState, setAsideFormState] = useState<AsideFormState>({
        country: "",
        postalCode: "",
        city: "",
        street: "",
        logo: null as File | null,
        images: [] as File[],
    });


    /** Generate otp compte */
    const sendOTPCode = useCallback(async ()=>{
        //--Set loading & execute api request
        setLoading({state: true, subtitle: t("userRegister.form.step2.next.loadingMessage")});
        try{
            await AuthServices.askVerificationCode(formData.current.get("email") as string, "SIGNUP");
            setLoading({state: false, subtitle: undefined});

            //--Switch to next step & update popup+
            setCurrentStep(prev => ({ current: 2, max: 2 > prev.max ? 2 : prev.max }));
            setPopup({status: "success", message: t("userRegister.apiResponse.verifyMailBox.success")})
        }
        catch(error){
            setLoading({state: false, subtitle: undefined});
            
            //-- message error
            if (error instanceof Error) {
                if(error instanceof AccountAlreadyRegistered){   
                    setPopup({ status: "warning", message: t("userRegister.apiResponse.codeVerification.error.accountAlreadyResgistered") });
                    navigation(RouteScheme.login);
                    return;
                }                
                setPopup({
                    status: "error",
                    message: t("global.messages.error")
                })
                console.log("Something went wrong:", error.message);
                console.log("Stack:", error.stack);
            }
            else {
                console.log("Unknown error:", error);
            }
        }
    },[setLoading, setCurrentStep, setPopup])

    
    /** Complete registeration */
    const handleCompletion = useCallback(async ()=>{
        //-- Ensure aside form data validation
        const asideForm = asideFormRef.current;
        if (!asideForm) {
            console.warn("The aside form has still not completely been mounted");
            return;
        }

        setLoading({ state: true, subtitle: t("userRegister.form.messages.loadingMessage") });

        try{
            asideForm.requestSubmit();
            // Fixed: Check if form IS invalid, then exit early
            if (asideForm.invalid) {
                console.warn("Invalid state: Please check the aside form and make sure all required fields are provided");
                setLoading({ state: false });
                return;
            }

            //-- clear html gost
            const nativeFormData = formData.current;
            nativeFormData.delete('images'); // html ghost
            nativeFormData.delete('images[]');

            //-- Data consolidation
            const data: FormData = objectToFormData(asideFormState, nativeFormData, { images: "images[]" });
            // console.log("Mes images réelles dans FormData : ",formData.current.getAll('images[]') );
            
            const res = await AuthServices.performUserRegister(data);
            setLoading({state: false});
            console.log("Ressource ", res);
            
            //-- client notification & notice
            setPopup({
                status: "success",
                message: t("userRegister.apiResponse.registering.success")
            });

            localStorage.setItem("userId", JSON.stringify(res?.data.id))
            navigation(RouteScheme.login);
        }
        catch(error){
            setLoading({ state: false });
            if (error instanceof Error) {
                //-- console log
                console.log("Error name", error.name, "\n");
                console.log("Something went wrong:", error.message, "\n");
                console.log("Stack:", error.stack, "\n");

                //-- Domain fallback (messages)
                if(error instanceof InvalidOTP){
                    setPopup({ status: "error", message: t("userRegister.apiResponse.codeVerification.expired") });
                }
                else if(error instanceof AccountAlreadyRegistered)
                    setPopup({ status: "warning", message: t("userRegister.apiResponse.registering.warning.accountAlreadyRegistered") })
                else if(error instanceof RessourceCreationFailed)
                    setPopup({ status: "error", message: t("userRegister.apiResponse.registering.error.failedRegisteration") });
                else if(error instanceof CompanyAlreadyRegistered)
                    setPopup({ status: "error", message: t("userRegister.apiResponse.registering.warning.companyAlreadyRegistered") });
            }
            else {
                setPopup({
                    status: "success",
                    message: t("global.messages.error")
                });
                console.log("Unknown error:", error);
            }
        }
    }, [asideFormState, setLoading, setPopup, setAsideFormState])


    return (
        <div className={styles.container}>
            <nav className={styles.navbar}>
                <AppIdentity />
                <div className={styles.actions}>
                    <LanguageSelector />
                    <AuthSwitcher />
                </div>         
            </nav>

           {/* MAIN CONTENT */}
            <div className={styles.mainContainerWrapper}>
                <div className={`faint-border ${styles.mainContainer}`}>
                
                    {/* PROCESS DESCRIPTION */}
                    <div className={styles.infoBox}>
                        <StageSection currentStep={currentStep} setCurrentStep={setCurrentStep} />
                        <div className={styles.statusItemSection}>
                            <StatusItem text={t("userRegister.processDescription.overall.1")}/>
                            <StatusItem text={t("userRegister.processDescription.overall.2")}/>
                            <StatusItem text={t("userRegister.processDescription.overall.3")}/>
                        </div>
                        <img src={OfficeWorkerImage} alt="" />
                    </div>
                    
                    {/* MAIN FORM */}

                    <div className={styles.mainForm}>
                        <div className={styles.header}>
                            <h3 className={styles.formTitle}>{t("userRegister.form.title")}</h3>
                            <div className={styles.desc}>
                                <p>{t("userRegister.form.subtitle")}</p>
                                <div className={styles.progessContainer}>
                                    <span>{t("userRegister.form.currentStep", {count: currentStep.current, totalCount: REGISTERING_TOTAL_STEP})}</span>
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
                                    onNext={async ()=>{
                                        await sendOTPCode();
                                    }}
                                />
                                :  currentStep.current == 2 ?
                                    <SecureAccount 
                                        formData={formData.current}
                                        onNext={()=>{
                                            setCurrentStep(prev => ({ current: 3, max: 3 > prev.max ? 3 : prev.max }));
                                        }}
                                    />
                                    : currentStep.current == 3 ?
                                       <UserProfileIdentity
                                            formData={formData.current}
                                            onNext={()=>{
                                                setCurrentStep(prev => ({ current: 4, max: 4 > prev.max ? 4 : prev.max }));
                                            }}
                                       />
                                    : currentStep.current === 4 ?
                                         <CompanyIdentityDetail
                                            formData={formData.current} 
                                            onNext={handleCompletion}
                                        /> 
                                        : <></>
                            }
                        </div>
                    </div>


                    {/* SIDE FORM */}
                    <SideForm asideFormRef={asideFormRef} form={asideFormState} setForm={setAsideFormState}   />
                </div>
            </div>

        </div>
    );
}
 
export default UserRegister;




/** Step Section */

interface StageSectionProps{
    currentStep: { current: number, max: number };
    setCurrentStep: React.Dispatch<React.SetStateAction<StageSectionProps['currentStep']>>;
}

const StageSection: React.FC<StageSectionProps> = ({
    currentStep, setCurrentStep
}) => {
    const { t } = useTranslation();

    return (
    <div className={styles.stagesSection}>
            <StageTitle
                step={1}
                active={currentStep.current === 1}
                textColor={currentStep.current > 1 ?  "#94a3b8" : undefined}
                backgroundColor={currentStep.current != 1 ? "#e0e7ff": undefined}
                txt={t("userRegister.processDescription.one")}
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
                txt={t("userRegister.processDescription.two")}
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
                txt={t("userRegister.processDescription.three")}
                textColor={currentStep.max > 3 && currentStep.current != 3 ?  "#94a3b8" : undefined}
                backgroundColor={currentStep.max > 3 && currentStep.current != 3 ? "#e0e7ff": undefined}
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
            <StageTitle
                step={4}
                active={currentStep.current === 4}
                txt={t("userRegister.processDescription.four")}
                textColor={currentStep.max == 4 && currentStep.current < 4 ?  "#94a3b8" : undefined}
                backgroundColor={currentStep.max === 4 && currentStep.current < 4 ? "#e0e7ff": undefined}
                disableCursorPointer={
                    currentStep.max < 4
                }
                onClick={()=>{
                    if(currentStep.max >= 4){
                        setCurrentStep(prev => ({
                            ...prev,
                            current: 4
                        }))
                    }
                }}
            />
        </div>
    );
}
 
