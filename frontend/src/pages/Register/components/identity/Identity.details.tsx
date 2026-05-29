import { useTranslation } from "react-i18next";

//--Custom components
import BasicInput from "../../../../layout/components/form/input/basic.input";

//-- SVG Components
import PersonSVG from '/src/assets/svg/person/person-2-svgrepo-com.svg';

//-- custom styles
import styles from "./style.module.css"



const inputColor = "#FDFDFE";


interface IdentityDetailProps{

}

const IdentityDetail: React.FC<IdentityDetailProps> = ({
    
}) => {
    const {t} = useTranslation()
    return (
        <div className={styles.container}>
            <BasicInput
                        svg={PersonSVG} 
                        inputName="name"
                        className="faint-border"
                        label={t("register.form.step1.inputs.name.label")} 
                        placeholder="DigitalCop Company"  
                        padding={5}  width="100%" backgroundColor={inputColor}
            />
        </div>
    );
}
 
export default IdentityDetail;