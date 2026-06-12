
import { useTranslation } from "react-i18next";
import FormWrapper, { FormHint, FormInputs, FormSubmitSection, FormTitle } from "../../../../../layout/components/form/form.wrapper";

//-- Custom Components
import BasicInput from "../../../../../layout/components/form/input/basic.input";
import BrandButton from "../../../../../layout/components/buttons/brand.button";

//-- SVG Components
import PasswordSVG from "/src/assets/svg/security/password-protection-privacy-access-verification-code-svgrepo-com.svg"

//-- CSS styles
import styles from "./style.module.css"


/**
 * perfomr auth validation 
 */

interface SecureAccountProps{
    formData: FormData;
    onNext?: (event: React.SubmitEvent<HTMLFormElement>) => void;
}


const inputColor = "#FDFDFE";


const SecureAccount: React.FC<SecureAccountProps> = ({
    formData,
    onNext
}) => {
    const {t} = useTranslation();

    return (
        <FormWrapper formData={formData} handleNext={onNext}>
            <FormTitle title={t("userRegister.form.step2.title")}/>
            <FormInputs>
                <BasicInput
                    required
                    svg={PasswordSVG}
                    type="password"
                    className="faint-border"
                    inputName="verificationCode"
                    extraInputProps={{ defaultValue: formData.get("verificationCode")?.toString() }}
                    label={t("userRegister.form.step2.inputs.verificationCode.label")} 
                    padding={5}  width="100%" backgroundColor={inputColor}
                /> 
            </FormInputs>
            <FormSubmitSection>
                <BrandButton
                    type="submit"
                    text={t("userRegister.buttons.logbtn")}
                />
                <FormHint text={t("userRegister.form.step1.policyText")} />
            </FormSubmitSection>
        </FormWrapper>
    );
}
 
export default SecureAccount;