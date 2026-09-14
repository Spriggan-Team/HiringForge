

import type { ParseKeys } from "i18next";
import { useNavigate } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { useCallback, useRef, useState } from "react";
import { useAppContext } from "../../../hooks/context";

//--Config Object
import RouteScheme from "../../../route.scheme";


//-- Custom - React Component
import AccountAccess from "../components/access/account.access";
import SideForm from "./components/sideform/side.form";
import  CompanyIdentityDetail from "./components/identity/company/company.identity.details";
import UserProfileIdentity from "../components/profile.identity.form";
import RegisterationHeader from "../components/registeration.header";
import RegisterationSteps, { type StepItem } from "../components/registeration.steps";

//-- services
import AuthServices from "../../../api/services/auth/auth";
import { objectToFormData } from "../../../utils/convertor";
import { AccountAlreadyRegistered, CompanyAlreadyRegistered } from "../../../api/services/auth/exceptions";
import { InvalidOTP, ResourceCreationFailed } from "../../../api/services/exceptions";
import { navigateTo } from "../../../App";

//-- hooks
import { useSendOTP } from "../../../hooks/handler";

//-- SVG - Components
import SecureAccount from "./components/security/LockAccount";


//-- CSS Styles
import  styles from "./style.module.css"




const REGISTERING_TOTAL_STEP = 4;

export const RECRUITER_STEPS: StepItem[] = [
  {
    id: 1,
    translationKey: "register.processDescription.one" ,
  },
  {
    id: 2,
    translationKey: "register.processDescription.two",
  },
  {
    id: 3,
    translationKey: "register.processDescription.three",
  },
  {
    id: 4,
    translationKey: "register.processDescription.four",
  },
];


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



    const { sendOTPCode } = useSendOTP({
        onSuccess: () => {
            setCurrentStep(prev => ({ current: 2, max: Math.max(2, prev.max) }));
        }
    });

    
    /** Complete registeration */
    const handleCompletion = useCallback(async ()=>{
        //-- Ensure aside form data validation
        const asideForm = asideFormRef.current;
        if (!asideForm) {
            console.warn("The aside form has still not completely been mounted");
            return;
        }

        setLoading({ state: true, subtitle: t("register.messages.loadingMessage") });

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
            
            const res = await AuthServices.performRegister(data);
            setLoading({state: false});
            console.log("Ressource ", res);
            
            //-- client notification & notice
            setPopup({
                status: "success",
                message: t("register.apiResponse.registering.success")
            });

            navigateTo(navigation, RouteScheme.login);
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
                    setPopup({ status: "error", message: t("register.apiResponse.codeVerification.expired") });
                }
                else if(error instanceof AccountAlreadyRegistered)
                    setPopup({ status: "warning", message: t("register.apiResponse.registering.warning.accountAlreadyRegistered") })
                else if(error instanceof ResourceCreationFailed)
                    setPopup({ status: "error", message: t("register.apiResponse.registering.error.failedRegisteration") });
                else if(error instanceof CompanyAlreadyRegistered)
                    setPopup({ status: "error", message: t("register.apiResponse.registering.warning.companyAlreadyRegistered") });
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
           {/* MAIN CONTENT */}
            <div className={styles.mainContainerWrapper}>
                <div className={`faint-border ${styles.mainContainer}`}>
                    {/* PROCESS DESCRIPTION */}
                    <RegisterationSteps 
                        t={t}
                        currentStep={currentStep}
                        setCurrentStep={setCurrentStep}
                        steps={RECRUITER_STEPS}
                    />

                    
                    {/* MAIN FORM */}
                    <div className={styles.mainForm}>
                        <RegisterationHeader
                            t={t}
                            titleKey={"userRegister.form.title" as ParseKeys}
                            subtitleKey={"userRegister.form.subtitle" as ParseKeys}
                            currentStep={currentStep.current}
                            totalSteps={REGISTERING_TOTAL_STEP}
                        />
                        <div className={styles.form}>
                            {currentStep.current == 1 ?
                                <AccountAccess 
                                    t={t}
                                    submitButtonTextKey={"global.buttons.next"}
                                    formData={formData.current}
                                    onNext={async ()=>{
                                        await sendOTPCode(formData.current.get("email") as string);
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
                    <SideForm 
                        form={asideFormState}
                        asideFormRef={asideFormRef}
                        setForm={setAsideFormState}
                    />
                </div>
            </div>

        </div>
    );
}
 
export default UserRegister;



