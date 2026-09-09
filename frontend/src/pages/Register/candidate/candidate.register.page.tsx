

import type { ParseKeys } from 'i18next';
import { useCallback, useMemo, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';

//-- Hooks & Services
import { useSendOTP } from '../../../hooks/handler';
import { useAppContext } from '../../../hooks/context';
import { InvalidOTP, ResourceCreationFailed } from '../../../api/services/exceptions';
import { AccountAlreadyRegistered, CompanyAlreadyRegistered } from '../../../api/services/auth/exceptions';
import AuthServices from '../../../api/services/auth/auth';
import RouteScheme from '../../../route.scheme';
import { navigateTo } from '../../../App';


//-- Custom components 
import RegisterationHeader from '../components/registeration.header';
import  RegisterationSteps, { type StepItem } from '../components/registeration.steps';
import MediaUploader from '../components/media.uploader';


import AccountAccess from '../components/access/account.access';
import SecureAccount from '../user/components/security/LockAccount';
import ProfileIdentityForm from '../components/profile.identity.form';
import AddressFields, { type AddressData } from '../components/address.fields';

//-- SVG Components
import CVFileSVG  from "/src/assets/svg/cv-file-interface-symbol-svgrepo-com.svg?react"


//-- Styles
import styles from './CandidateRegisterPage.module.css';




export const CANDIDATES_STEPS: StepItem[] = [
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
];



export const CandidateRegisterPage: React.FC = () => {
  const { t } = useTranslation();
  
  const navigation = useNavigate();
  const { setLoading, setPopup, setCountdown } = useAppContext();

  const [currentStep, setCurrentStep] = useState({ current: 1, max: 1 });

  //-- Form State
  const asideFormRef = useRef<HTMLFormElement | null>(null);
  const formData= useRef<FormData>(new FormData());

  const [cvFile, setCvFile] = useState<File | null>(null);

  const [address, setAddress] = useState<AddressData>({
    country: (formData.current.get("country") as string) || "",
    postalCode: (formData.current.get("postalCode") as string) || "",
    city: (formData.current.get("city") as string) || "",
    street: (formData.current.get("street") as string) || "",
  });

  //-- Send OTP
  const { sendOTPCode } = useSendOTP({
      onSuccess: () => {
        // Action spécifique à ce composant
        setCurrentStep(prev => ({ current: 2, max: Math.max(2, prev.max) }));
        setCountdown({
          onExpire: ()=> setCountdown(null)
        });
      }
  });

  
  const handleAccountAccessNext = useCallback(async () => {
    const email = formData.current.get("email") as string;
    if (email) {
      await sendOTPCode(email);
    }
  }, [sendOTPCode]);

  
  const handleSecureAccountNext = useCallback(() => {
    setCurrentStep(prev => ({ current: 3, max: Math.max(3, prev.max) }));
  }, []);


  //-- 
  const handleAddressChange = useCallback((updatedFields: Partial<AddressData>) => {
    // Update UI
    setAddress((prev) => ({ ...prev, ...updatedFields }));

    //-- Form Data
    Object.entries(updatedFields).forEach(([key, value]) => {
      if (value !== undefined) {
        formData.current.set(key, String(value));
      }
    });
  }, []);



  //-- Handle submit
  const handleSubmit = async () => {
    try{
      const asideForm = asideFormRef.current;
      if (!asideForm) {
          console.warn("The aside form has still not completely been mounted");
          return;
      }
      asideForm?.requestSubmit();
      setLoading({ state: true, subtitle: t("register.messages.loadingMessage") });
      
      if (asideForm.invalid) {
          console.warn("Invalid state: Please check the aside form and make sure all required fields are provided");
          setLoading({ state: false });
          return;
      }

      const data = formData.current;
      console.log("DATA", data )
      await AuthServices.performRegister(data, "candidate");
      setLoading({ state: false });
      
      //-- client notification & notice
      setPopup({
          status: "success",
          message: t("register.apiResponse.registering.success")
      });

      setCountdown(null); //-- clear countdonw
      navigateTo(navigation, RouteScheme.login);
    }
    catch(error){
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
      setLoading({ state: false });
    }
  };


  //--- RENDER
  const renderStepContent = useMemo(() => {
    switch (currentStep.current) {
      case 1:
        return (
          <AccountAccess
            t={t}
            submitButtonTextKey="global.buttons.next"
            formData={formData.current}
            onNext={handleAccountAccessNext}
          />
        );
      case 2:
        return (
          <SecureAccount
            formData={formData.current}
            onNext={handleSecureAccountNext}
          />
        );
      case 3:
        return (
          <ProfileIdentityForm
            formData={formData.current}
            onNext={() => {
              handleSubmit()
            }}
          />
        );
      default:
        return null;
    }
  }, [currentStep.current, t, handleAccountAccessNext, handleSecureAccountNext]);



  const handleCVChange = useCallback((file: File | null) => {
    if (file) {
      formData.current.set("cv", file);
    } else {
      formData.current.delete("cv");
    }
    setCvFile(file);
  }, []);




  return (
    <div className={styles.container}>
      <div className={styles.containerWrapper}>
        <div className={styles.mainContainer}>

          {/* INDICATEUR D'ÉTAPES */}
          <RegisterationSteps
            t={t}
            steps={CANDIDATES_STEPS}
            currentStep={currentStep}
            setCurrentStep={setCurrentStep}
          />

          {/*  GLOBAL FORM */}
          <div  className={styles.main}>
            
            {/* MAIN SECTION  */}
            <main className={styles.formSection}>
              <RegisterationHeader
                t={t}
                totalSteps={3}
                titleKey="candidateRegister.form.title"
                subtitleKey="candidateRegister.form.subtitle"
                currentStep={currentStep.current}
              />
              {renderStepContent}
            </main>

            {/* ASIDE (MEDIA + ADRESSE) */}
            <form 
              ref={asideFormRef}
              className={styles.asideSection}
              onSubmit={(e)=>e.preventDefault()}
            >
              <MediaUploader
                file={cvFile}
                buttonTaglineKey={'global.cv.label'}
                defaultIcon={<CVFileSVG />}
                inputAttributes={{
                  accept: "application/pdf"
                }}
                titleKey={"global.cv.file" as ParseKeys}
                onFileChange={handleCVChange}
              />

              <AddressFields
                address={address}
                requirements={{ street: false }}
                titleKey='global.address.label'
                onChange={handleAddressChange}
              />
            </form>

          </div>
        </div>
      </div>
    </div>
  );
};

export default CandidateRegisterPage;
 