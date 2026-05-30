
import { useTranslation } from "react-i18next";


//--Custom components
import BasicInput from "../../../../layout/components/form/input/basic.input";
import FormWrapper, { FormHint, FormInputs, FormSubmitSection, FormTitle } from "../../../../layout/components/form/form.wrapper";
import VideoInput from "../../../../layout/components/form/input/video/video.input";
import BrandButton from "../../../../layout/components/buttons/brand.button";


//-- SVG Components
import PersonSVG from '/src/assets/svg/person/person-2-svgrepo-com.svg';
import LeftToRightArrowSVG from '/src/assets/svg/arrows/back-arrow-direction-down-right-left-up-svgrepo-com.svg';


//-- custom styles
import styles from "./style.module.css"
import CustomTextarea from "../../../../layout/components/form/input/textarea/custom.textarea";



const inputColor = "#FDFDFE";


interface IdentityDetailProps{
    formData: FormData;
    onNext: (event: React.SubmitEvent<HTMLFormElement>) => void;
}


const IdentityDetail: React.FC<IdentityDetailProps> = ({
    formData,
    onNext
}) => {
    const {t} = useTranslation()

    return (
        <div className={styles.container}>
            <FormWrapper formData={formData} handleNext={onNext} >
                <FormInputs>
                    <FormTitle title={t("register.form.step2.title")}/>
                    <BasicInput
                        svg={PersonSVG} 
                        inputName="name"
                        className="faint-border"
                        placeholder={t("register.form.step2.inputs.name.placeholder")}
                        label={t("register.form.step2.inputs.name.label")} 
                        extraInputProps={{ defaultValue: formData.get("name")?.toString() ?? undefined }}
                        padding={5}  width="100%" backgroundColor={inputColor}
                        required
                    />
                    <BasicInput
                        svg={PersonSVG}
                        inputName="siret"
                        className="faint-border"
                        placeholder={t("register.form.step2.inputs.siret.label")}
                        label={t("register.form.step2.inputs.siret.label")} 
                        padding={5}  width="100%" backgroundColor={inputColor}
                        extraInputProps={{ defaultValue: formData.get("siret")?.toString() ?? undefined }}
                        required
                    />
                    <VideoInput
                        title={t("register.form.step2.inputs.video.label")}
                        onChange={(file)=>{ formData.set("videoPresentation", file)}}
                        subtitle={t("register.form.step2.inputs.video.placeholder")}
                    />
                    <CustomTextarea
                        inputName="description"
                        placeholder={t("register.form.step2.inputs.desc.placeholder")}
                    />
                </FormInputs>
                <FormSubmitSection>
                    <BrandButton
                        type="submit"
                        svg={LeftToRightArrowSVG}
                        text={t("register.buttons.logbtn")}
                    />
                    <FormHint text={t("register.form.step1.policyText")} />
                </FormSubmitSection>
            </FormWrapper>
        </div>
    );
}
 
export default IdentityDetail;