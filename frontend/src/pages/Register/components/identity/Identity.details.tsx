import { useTranslation } from "react-i18next";

//--Custom components
import BasicInput from "../../../../layout/components/form/input/basic.input";

//-- SVG Components
import PersonSVG from '/src/assets/svg/person/person-2-svgrepo-com.svg';

//-- custom styles
import styles from "./style.module.css"
import FormWrapper, { FormInputs } from "../../../../layout/components/form/form.wrapper";



const inputColor = "#FDFDFE";


interface IdentityDetailProps{
    formData: FormData;
    onNext: (event: React.SubmitEvent<HTMLFormElement>)=>void;
}

const IdentityDetail: React.FC<IdentityDetailProps> = ({
    formData,
}) => {
    const {t} = useTranslation()
    return (
        <div className={styles.container}>
            <FormWrapper formData={formData}>
                <FormInputs>
                    <BasicInput
                            svg={PersonSVG} 
                                inputName="name"
                                className="faint-border"
                                placeholder={t("register.form.step2.inputs.name.placeholder")}
                                label={t("register.form.step2.inputs.name.label")} 
                                extraInputProps={{ defaultValue: formData.get("name")?.toString() ?? undefined }}
                                padding={5}  width="100%" backgroundColor={inputColor}
                    />
                    <BasicInput
                                svg={PersonSVG}
                                inputName="name"
                                className="faint-border"
                                placeholder={t("register.form.step2.inputs.siret.label")}
                                label={t("register.form.step2.inputs.siret.label")} 
                                padding={5}  width="100%" backgroundColor={inputColor}
                                extraInputProps={{ defaultValue: formData.get("siret")?.toString() ?? undefined }}
                    />
                </FormInputs>
            </FormWrapper>
        </div>
    );
}
 
export default IdentityDetail;