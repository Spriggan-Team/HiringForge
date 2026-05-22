


//Custom - React Component
import HardPassword from "../../../../layout/components/form/input/password/hard.password";
import ConfirmPassword from "../../../../layout/components/form/input/password/confirm/confirm.password";
import BrandButton from "../../../../layout/components/buttons/brand.button";
import BasicInput from "../../../../layout/components/form/input/basic.input";

//SVG Components
import PersonSVG from '../../../../assets/svg/person/person-2-svgrepo-com.svg';
import EmailSVG from '../../../../assets/svg/email/email-1-svgrepo-com.svg';

//Styles
import styles from "./style.module.css"


const IdentityDetails  = () => {
    return (
        <div className={styles.container}>
            <div className={styles.inputSection}>
                <span>Information du compte</span>
                <BasicInput svg={PersonSVG} padding={2}  />
                <BasicInput svg={EmailSVG} padding={2}  />
                <HardPassword />
                <ConfirmPassword />
            </div>
        
            <div className={styles.nextSection}>
                <BrandButton />
                <div>
                    <p>En créant un compte vous accepter nos conditions générales <br /> et notre politique de confidentilaités</p>
                </div>
            </div>
        </div>
    );
}
 
export default IdentityDetails ;