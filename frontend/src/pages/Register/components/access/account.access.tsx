import { useState } from "react";
import { useTranslation } from "react-i18next";
import { useAppContext } from "../../../../hooks/context";

//-- Custom - React Component
import HardPassword from "../../../../layout/components/form/input/password/hard.password";
import ConfirmPassword from "../../../../layout/components/form/input/password/confirm/confirm.password";
import BrandButton from "../../../../layout/components/buttons/brand.button";
import BasicInput from "../../../../layout/components/form/input/basic.input";
import FormWrapper, { FormHint, FormInputs, FormTitle, SubmitSection } from "../../../../layout/components/form/form.wrapper";

//-- SVG Components
import EmailSVG from '/src/assets/svg/email/email-1-svgrepo-com.svg';
import LeftToRightArrowSVG from '/src/assets/svg/arrows/back-arrow-direction-down-right-left-up-svgrepo-com.svg';

//-- Styles
import styles from "./style.module.css"




const inputColor = "#FDFDFE";


interface AccountAccessProps{
    formData: FormData;
    onNext?: (event: React.SubmitEvent<HTMLFormElement>, isPasswordConfirm?: boolean)=>void;
}


const AccountAccess: React.FC<AccountAccessProps>  = ({
    formData, onNext
}) => {
    const {t} = useTranslation();
    const {setPopup} = useAppContext();

    const [password, setPassword] = useState<string>("");
    const [isPasswordStrong, setIsPasswordStrong] = useState(false);
    const [isPasswordConfirm, setIsPasswordConfirm] = useState<boolean>(false);


    const handleNext = (event: React.SubmitEvent<HTMLFormElement>) => {
        //-- strong password
        if(!isPasswordStrong){
            setPopup({status: "error", message: t("register.form.step1.inputs.password.error")})
            setPopup(null);
            return;
        }

        //-- is password confirm
        if(!isPasswordConfirm){
            setPopup({status: "error", message: t("register.form.step1.inputs.confirmPassword.error")})
            setPopup(null);
            return;
        }

        if(onNext){
            onNext(event, isPasswordConfirm);
        }
        console.log(Object.fromEntries(formData.entries()))
    };

    return (
        <div className={styles.container}>
            <FormWrapper formData={formData} handleNext={handleNext}>
                <FormTitle title={t("register.form.step1.title")} />
                <FormInputs>
                    <BasicInput 
                        padding={5}  
                        width="100%" 
                        svg={EmailSVG}
                        inputName="email"
                        className="faint-border" 
                        label={t("register.form.step1.inputs.email.label")} 
                        placeholder={t("register.form.step1.inputs.email.placeholder")}
                        backgroundColor={inputColor}
                        required
                        extraInputProps={{ defaultValue: formData.get("email")?.toString() ?? undefined }}
                    />

                    <HardPassword 
                        type="password" 
                        width={"100%"} padding={5}
                        className="faint-border"
                        inputName="password"
                        backgroundColor={inputColor}
                        onChange={(event)=> setPassword(event.target.value)}
                        label={t("register.form.step1.inputs.password.label")}
                        setter={setIsPasswordStrong}
                        required
                        extraInputProps={{ defaultValue: formData.get("password")?.toString() ?? undefined }}
                    />
                    <ConfirmPassword
                        padding={5}
                        width={"100%"}
                        type="password"
                        password={password}
                        className="faint-border" 
                        backgroundColor={inputColor}
                        label={t("register.form.step1.inputs.confirmPassword.label")}
                        setConfirm={setIsPasswordConfirm}
                        required
                        defaultValue={ formData.get("password")?.toString() ?? undefined}
                    />
                </FormInputs>
                <SubmitSection>
                    <BrandButton 
                        type="submit"
                        svg={LeftToRightArrowSVG}
                        text={t("register.buttons.logbtn")}
                    />
                    <FormHint text={t("register.form.step1.policyText")} />
                </SubmitSection>
            </FormWrapper>
        </div>
    );
}
 
export default AccountAccess ;