

import { useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';

//-- Hooks
import { useSendOTP } from '../../../hooks/handler';

//-- Custom components 
import RegisterationHeader from '../components/registeration.header';
import  { type StepItem } from '../components/registeration.steps';


import AccountAccess from '../components/access/account.access';
import SecureAccount from '../user/components/security/LockAccount';
import ProfileIdentityForm from '../components/profile.identity.form';


import styles from './CandidateRegisterPage.module.css';



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
];


export const CandidateRegisterPage: React.FC = () => {
  const { t } = useTranslation();
  const [currentStep, setCurrentStep] = useState({ current: 1, max: 1 });


  //-- Form State
  const formData= useRef<FormData>(new FormData());

  const [avatar, setAvatar] = useState<File | null>(null);
  const [cvFile, setCvFile] = useState<File | null>(null);


  const { sendOTPCode } = useSendOTP({
      onSuccess: () => {
          // Action spécifique à ce composant
          setCurrentStep(prev => ({ current: 2, max: Math.max(2, prev.max) }));
      }
  });

  const handleSubmit = (e: React.SubmitEvent) => {
    e.preventDefault();
    // ...
  };


  return (
    <div className={`${styles.container}`}>
       <div className={styles.containerWrapper}>
          <div className={styles.mainForm}>
            {/** REGISTERATION STEPS */}

            {/** MainContent */}
            <div className={styles.form}>
                {/** Header */}
                <RegisterationHeader
                      t={t}
                      totalSteps={3}
                      titleKey={"userRegister.form.title"}
                      subtitleKey={"userRegister.form.subtitle"}
                      currentStep={currentStep.current}
                />
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
                                setCurrentStep(prev => ({ current: 3, max: Math.max(3, prev.max)}));
                            }}
                        />
                        : currentStep.current == 3 ?
                            <ProfileIdentityForm
                                formData={formData.current}
                                onNext={()=>{}}
                            />
                        : <></>
                }
            </div>
            {/** Aside */}
            <div>

            </div>
          </div>
       </div>
    </div>
  );
};

export default CandidateRegisterPage;
 