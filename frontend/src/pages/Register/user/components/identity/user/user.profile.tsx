
import type React from "react";

//-- CSS Styles
import styles from "./style.module.css"
import FormWrapper, { FormHint, FormInputs, FormSubmitSection, FormTitle } from "../../../../../../layout/components/form/form.wrapper";
import { useTranslation } from "react-i18next";

import BasicInput from "../../../../../../layout/components/form/input/basic.input";
import BrandButton from "../../../../../../layout/components/buttons/brand.button";
import ImageInput from "../../../../../../layout/components/form/input/image/image";


interface UserProfileIdentityProps{
    formData: FormData;
    onNext?: (e: React.SubmitEvent<HTMLFormElement>) => void ;
}

const inputColor = "#FDFDFE";


const UserProfileIdentity: React.FC<UserProfileIdentityProps> = ({
    formData, onNext
}) => {
    const {t} = useTranslation();

    return (
        <div className={styles.container}>
            <FormWrapper formData={formData} handleNext={onNext}>
                <FormTitle title={t("userRegister.form.step3.title")} />
                <FormInputs>
                    <BasicInput
                        inputName="firstName"
                        className="faint-border"
                        placeholder={t("userRegister.form.step3.inputs.firstName.placeholder")}
                        label={t("userRegister.form.step3.inputs.firstName.label")} 
                        padding={5}  width="100%" backgroundColor={inputColor}
                        extraInputProps={{ defaultValue: formData.get("firstName")?.toString() ?? undefined }}
                        required
                    />
                    <BasicInput
                        inputName="lastName"
                        className="faint-border"
                        placeholder={t("userRegister.form.step3.inputs.lastName.placeholder")}
                        label={t("userRegister.form.step3.inputs.lastName.label")} 
                        padding={5}  width="100%" backgroundColor={inputColor}
                        extraInputProps={{ defaultValue: formData.get("lastName")?.toString() ?? undefined }}
                        required
                    />
                    <ImageInput
                        onChange={(file)=> formData.set("profileImage", file)}
                        title={t("userRegister.form.step3.inputs.image.label")}
                        subtitle={t("userRegister.form.step3.inputs.image.placeholder")}
                        defaultFile={formData.get("profileImage") ? formData.get("profileImage") as File : null}
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
        </div>
    );
}
 
export default UserProfileIdentity;