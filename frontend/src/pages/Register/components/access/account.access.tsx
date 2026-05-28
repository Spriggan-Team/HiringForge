import { useTranslation } from "react-i18next";


//-- Custom - React Component
import HardPassword from "../../../../layout/components/form/input/password/hard.password";
import ConfirmPassword from "../../../../layout/components/form/input/password/confirm/confirm.password";
import BrandButton from "../../../../layout/components/buttons/brand.button";
import BasicInput from "../../../../layout/components/form/input/basic.input";

//-- SVG Components
import EmailSVG from '/src/assets/svg/email/email-1-svgrepo-com.svg';
import LeftToRightArrowSVG from '/src/assets/svg/arrows/back-arrow-direction-down-right-left-up-svgrepo-com.svg';

//-- Styles
import styles from "./style.module.css"
import { useState } from "react";
import { useAppContext } from "../../../../hooks/context";




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
    const [isPasswordConfirm, setIsPasswordConfirm] = useState<boolean>(false);


    const handleNext = (event: React.SubmitEvent<HTMLFormElement>) => {
        event.preventDefault();
    
        const formElements = event.currentTarget.elements;

        for (let i = 0; i < formElements.length; i++) {
            const element = formElements[i] as HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement;
            
            if (element.name && element.type !== "submit" && element.type !== "button") {
                if ((element.type === "checkbox" || element.type === "radio") && !(element as HTMLInputElement).checked) {
                    continue; 
                }
                
                formData.append(element.name, element.value);
            }
        }
        if(!isPasswordConfirm){
            setPopup({status: "error", message: t("register.form.step1.inputs.confirmPassword.error")})
            setTimeout(()=> setPopup(null), 2000);
            return;
        }
        if(onNext){
            onNext(event, isPasswordConfirm);
        }
        console.log("Résultat :", Object.fromEntries(formData.entries()));
    };

    return (
        <div className={styles.container}>
            <form className={styles.form} action="" onSubmit={handleNext}>
                <div className={styles.inputSection}>
                    <span className={styles.title}>{t("register.form.step1.title")}</span>
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
                    />

                    <HardPassword 
                        type="password" 
                        width={"100%"} padding={5}
                        className="faint-border"
                        inputName="password"
                        backgroundColor={inputColor}
                        onChange={(event)=> setPassword(event.target.value)}
                        label={t("register.form.step1.inputs.password.label")}
                        required
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
                    />
                </div>
                
                <div className={styles.nextSection}>
                    <BrandButton 
                        type="submit"
                        svg={LeftToRightArrowSVG}
                        text={t("register.buttons.logbtn")}
                    />
                    <div className={styles.undertext}>
                        <p>{t("register.form.step1.policyText")}</p>
                    </div>
                </div>
            </form>
        
        </div>
    );
}
 
export default AccountAccess ;