
import type React from "react";
import type { ParseKeys } from "i18next";


import FormWrapper, { FormHint, FormInputs, FormSubmitSection, FormTitle } from "../../../layout/components/form/form.wrapper";
import { useTranslation } from "react-i18next";

import BasicInput from "../../../layout/components/form/input/basic.input";
import BrandButton from "../../../layout/components/buttons/brand.button";
import ImageInput from "../../../layout/components/form/input/image/image";

//-- CSS Styles
import styles from "./ProfileIdentityForm.module.css"


interface UserProfileIdentityProps{
    formData: FormData;
    onNext?: (e: React.SubmitEvent<HTMLFormElement>) => void ;

    //-- fields name
    fieldNames?: {
        firstName?: string;
        lastName?: string;
        profileImage?: string;
    };

    /** bacground color for inputs */
    inputBackgroundColor?: string;

    //-- Profil image management
    showProfileImage?: boolean;
    isImageRequired?: boolean;

    //-- Text Btn submit
    submitButtonTextKey?: ParseKeys;
}



const ProfileIdentityForm: React.FC<UserProfileIdentityProps> = ({
    formData,
    onNext,
    fieldNames = {
        firstName: "firstName",
        lastName: "lastName",
        profileImage: "profileImage",
    },
    showProfileImage = true,
    isImageRequired = false,
    inputBackgroundColor = "#FDFDFE",
    submitButtonTextKey = "global.buttons.next",
}) => {
    const {t} = useTranslation();

    const firstNameKey = fieldNames.firstName || "firstName";
    const lastNameKey = fieldNames.lastName || "lastName";
    const profileImageKey = fieldNames.profileImage || "profileImage";


    return (
        <div className={styles.container}>
            <FormWrapper formData={formData} handleNext={onNext}>
                <FormTitle title={t("register.profileInformation.title")} />
                <FormInputs>
                    {/** FirstName */}
                    <BasicInput
                        inputName={firstNameKey}
                        className="faint-border"
                        placeholder={t("register.profileInformation.inputs.firstName.placeholder")}
                        label={t("register.profileInformation.inputs.firstName.label")} 
                        padding={5} 
                        width="100%"
                        backgroundColor={inputBackgroundColor}
                        extraInputProps={{ 
                            defaultValue: formData.get(firstNameKey)?.toString() ?? undefined
                        }}
                        required
                    />
                    {/**LastName */}
                    <BasicInput
                        inputName='lastName'
                        className="faint-border"
                        placeholder={t("register.profileInformation.inputs.lastName.placeholder")}
                        label={t("register.profileInformation.inputs.lastName.label")} 
                        padding={5} 
                        width="100%"
                        backgroundColor={inputBackgroundColor}
                        extraInputProps={{ 
                            defaultValue: formData.get(lastNameKey)?.toString() ?? undefined 
                        }}
                        required
                    />
                    {/** Image */}
                    {
                        showProfileImage && (
                            <ImageInput
                                required={isImageRequired}
                                onChange={(file)=>{
                                    if(file)
                                         formData.set(profileImageKey, file)
                                    else
                                        formData.delete(profileImageKey)
                                }}
                                title={t("register.profileInformation.inputs.image.label")}
                                subtitle={t("register.profileInformation.inputs.image.placeholder")}
                                defaultFile={
                                    formData.get(profileImageKey) ? 
                                        formData.get(profileImageKey) as File 
                                        : null
                                }
                            />
                        )
                    }
                </FormInputs>
                <FormSubmitSection>
                    <BrandButton
                        type="submit"
                        text={t(submitButtonTextKey)}
                    />
                    <FormHint text={t("global.policyText")} />
                </FormSubmitSection>
            </FormWrapper>
        </div>
    );
}
 
export default ProfileIdentityForm;