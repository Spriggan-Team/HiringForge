
import { useTranslation } from "react-i18next";


//--Custom components
import BasicInput from "../../../../../../layout/components/form/input/basic.input";
import FormWrapper, { FormHint, FormInputs, FormSubmitSection, FormTitle } from "../../../../../../layout/components/form/form.wrapper";
import VideoInput from "../../../../../../layout/components/form/input/video/video.input";
import BrandButton from "../../../../../../layout/components/buttons/brand.button";


//-- SVG Components
import PersonSVG from '/src/assets/svg/person/person-2-svgrepo-com.svg';


//-- custom styles
import styles from "./style.module.css"
import CustomTextarea from "../../../../../../layout/components/form/input/textarea/custom.textarea";



const inputColor = "#FDFDFE";


interface IdentityDetailProps{
    formData: FormData;
    onNext: (event: React.SubmitEvent<HTMLFormElement>) => void;
}


const CompanyIdentityDetail: React.FC<IdentityDetailProps> = ({
    formData,
    onNext
}) => {
    const {t} = useTranslation()

    return (
        <div className={styles.container}>
            <FormWrapper formData={formData} handleNext={onNext} >
                <FormInputs>
                    <FormTitle title={t("userRegister.form.title")}/>
                    <BasicInput
                        svg={PersonSVG} 
                        inputName="companyName"
                        className="faint-border"
                        placeholder={t("userRegister.form.companyInfo.inputs.name.placeholder")}
                        label={t("userRegister.form.companyInfo.inputs.name.label")} 
                        extraInputProps={{ defaultValue: formData.get("companyName")?.toString() ?? undefined }}
                        padding={5}  width="100%" backgroundColor={inputColor}
                        required
                    />
                    <BasicInput
                        svg={PersonSVG}
                        inputName="siret"
                        className="faint-border"
                        placeholder={t("userRegister.form.companyInfo.inputs.siret.label")}
                        label={t("userRegister.form.companyInfo.inputs.siret.label")} 
                        padding={5}  width="100%" backgroundColor={inputColor}
                        extraInputProps={{ defaultValue: formData.get("siret")?.toString() ?? undefined }}
                        required
                    />
                    <VideoInput
                        title={t("userRegister.form.companyInfo.inputs.video.label")}
                        onChange={(file)=>{ formData.set("videoPresentation", file)}}
                        subtitle={t("userRegister.form.companyInfo.inputs.video.placeholder")}
                        defaultFile={formData.get("videoPresentation") ? formData.get("videoPresentation") as File : null }
                        
                    />
                    <CustomTextarea
                        inputName="description"
                        placeholder={t("userRegister.form.companyInfo.inputs.desc.placeholder")}
                    />
                </FormInputs>
                <FormSubmitSection>
                    <BrandButton
                        type="submit"
                        text={t("global.buttons.connexion")}
                    />
                    <FormHint text={t("global.policyText")} />
                </FormSubmitSection>
            </FormWrapper>
        </div>
    );
}
 
export default CompanyIdentityDetail;