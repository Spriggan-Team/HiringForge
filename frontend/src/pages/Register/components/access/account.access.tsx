import { useState } from "react";
import type { ParseKeys, TFunction } from "i18next";
import { useTranslation } from "react-i18next";
import { useAppContext } from "../../../../hooks/context";

//-- Custom - React Component
import HardPassword from "../../../../layout/components/form/input/password/hard.password";
import ConfirmPassword from "../../../../layout/components/form/input/password/confirm/confirm.password";
import BrandButton from "../../../../layout/components/buttons/brand.button";
import BasicInput from "../../../../layout/components/form/input/basic.input";
import FormWrapper, { FormHint, FormInputs, FormTitle, FormSubmitSection } from "../../../../layout/components/form/form.wrapper";

//-- SVG Components
import EmailSVG from '/src/assets/svg/email/email-1-svgrepo-com.svg?react';

//-- Styles
import styles from "./style.module.css"




const inputColor = "#FDFDFE";


interface AccountAccessProps {
  t: TFunction;
  formData: FormData;
  i18nPrefix?: string;
  inputBackgroundColor?: string;
  submitButtonTextKey?: ParseKeys;
  onNext?: (event: React.SubmitEvent<HTMLFormElement>, isPasswordConfirm: boolean) => void;
}


const AccountAccess: React.FC<AccountAccessProps> = ({
  t,
  formData,
  i18nPrefix = "register.accountInformation",
  inputBackgroundColor = "#FDFDFE",
  submitButtonTextKey = "register.buttons.logbtn" as ParseKeys,
  onNext,
}) => {
  const { setPopup } = useAppContext();

  const [isPasswordStrong, setIsPasswordStrong] = useState(false);
  const [isPasswordConfirm, setIsPasswordConfirm] = useState<boolean>(false);
  const [password, setPassword] = useState<string>(formData.get("password")?.toString() ?? "");


  const handleNext = (event: React.SubmitEvent<HTMLFormElement>) => {
    event.preventDefault();

    // -- MDP validaton
    if (!isPasswordConfirm) {
      setPopup({ 
        status: "error", 
        message: t(`${i18nPrefix}.inputs.confirmPassword.error` as any) 
      });
      return; 
    }

    // -- Strong password validation
    if (!isPasswordStrong) {
      setPopup({ 
        status: "error", 
        message: t(`${i18nPrefix}.inputs.password.error` as any) 
      });
      return;
    }

    if (onNext) {
      onNext(event, isPasswordConfirm);
    }
  };

  return (
    <div className={styles.container}>
      <FormWrapper formData={formData} handleNext={handleNext}>
        <FormTitle title={t(`${i18nPrefix}.title` as any)} />
        
        <FormInputs>
          <BasicInput 
            padding={5}  
            width="100%" 
            svg={EmailSVG}
            inputName="email"
            className="faint-border" 
            label={t(`${i18nPrefix}.inputs.email.label` as any)} 
            placeholder={t(`${i18nPrefix}.inputs.email.placeholder` as any)}
            backgroundColor={inputBackgroundColor}
            required
            defaultValue={formData.get("email")?.toString() ?? ""}
          />

          <HardPassword 
            type="password" 
            width="100%" 
            padding={5}
            className="faint-border"
            inputName="password"
            value={password}
            backgroundColor={inputBackgroundColor}
            onChange={(event) => setPassword(event.target.value)}
            label={t(`${i18nPrefix}.inputs.password.label` as any)}
            setter={setIsPasswordStrong}
            required
          />

          <ConfirmPassword
            required
            padding={5}
            width="100%"
            type="password"
            password={password}
            className="faint-border" 
            backgroundColor={inputBackgroundColor}
            setConfirm={setIsPasswordConfirm}
            label={t(`${i18nPrefix}.inputs.confirmPassword.label` as any)}
            validTxt={t(`${i18nPrefix}.inputs.confirmPassword.valid` as any)}
            invalidTxt={t(`${i18nPrefix}.inputs.confirmPassword.invalid` as any)}
          />
        </FormInputs>

        <FormSubmitSection>
          <BrandButton 
            type="submit"
            text={t(submitButtonTextKey)}
          />
          <FormHint text={t(`global.policyText` as any)} />
        </FormSubmitSection>
      </FormWrapper>
    </div>
  );
};

export default AccountAccess;