
import { useTranslation } from "react-i18next";
import FormWrapper, { FormHint, FormInputs, FormSubmitSection, FormTitle } from "../../../../../layout/components/form/form.wrapper";

//-- Custom Components
import BasicInput from "../../../../../layout/components/form/input/basic.input";
import BrandButton from "../../../../../layout/components/buttons/brand.button";

//-- SVG Components
import PasswordSVG from "/src/assets/svg/security/password-protection-privacy-access-verification-code-svgrepo-com.svg?react"

//-- CSS styles
import styles from "./style.module.css"


/**
 * perfomr auth validation 
 */

interface SecureAccountProps{
    formData: FormData;
    inputName?: string;
    inputIcon?:  React.FC<React.SVGProps<SVGSVGElement>>;
    inputBackgroundColor?: string;
    onNext?: (event: React.SubmitEvent<HTMLFormElement>) => void;
}




const SecureAccount: React.FC<SecureAccountProps> = ({
    formData,
    onNext,
    inputName = "verificationCode",
    inputBackgroundColor = "#FDFDFE",
    inputIcon = PasswordSVG,
}) => {
    const {t} = useTranslation();

    return (
        <FormWrapper formData={formData} handleNext={onNext}>
            <FormTitle title={t("register.emailVerification.title")}/>
            <FormInputs>
                <BasicInput
                    required
                    svg={inputIcon}
                    type="password"
                    className="faint-border"
                    inputName={inputName}
                    extraInputProps={{ 
                        defaultValue: formData.get(inputName)?.toString()
                    }}
                    label={t("register.emailVerification.inputs.verificationCode.label")} 
                    padding={5}  
                    width="100%" 
                    backgroundColor={inputBackgroundColor}
                /> 
            </FormInputs>
            <FormSubmitSection>
                <BrandButton
                    type="submit"
                    text={t("register.buttons.secureAccount")}
                />
                <FormHint  text={t("global.policyText")} />
            </FormSubmitSection>
        </FormWrapper>
    );
}
 
export default SecureAccount;