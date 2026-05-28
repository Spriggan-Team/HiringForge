
//-- Custom Components
import BasicInput, { type BasicInputProps } from '../../basic.input';

//-- SVG Components
import OKCircleSVG from '../../../../../../assets/svg/check/ok-circle-svgrepo-com.svg';

//-- CSS Styles
import styles from './styles.module.css'
import { useEffect, useLayoutEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';



type ConfirmPasswordProps = BasicInputProps & {
                                password: string;
                                setConfirm?: (b: boolean)=> void;
                            }



const ConfirmPassword: React.FC<ConfirmPasswordProps> = ({
    password,
    setConfirm,
    ...props
}) => {
    const { t } = useTranslation();
    const [value, setValue] = useState<string>("");
    const [isConfirm, setIsConfirm] = useState<boolean>(false);

    useLayoutEffect(() => {
        const isValid = password !== "" && value === password;
        setIsConfirm(isValid);
        // console.log({password, value,isValid})
        
        if (setConfirm) {
            setConfirm(isValid);
        }
    }, [value, password, setConfirm]);

    return ( 
        <div className={styles.container}>
            <BasicInput 
                {...props}
                value={value} // Très important pour l'input contrôlé
                onChange={(e) => setValue(e.target.value)} // On récupère la valeur en temps réel
                leadingSVG={isConfirm ? OKCircleSVG : () => null}
                enableViewToggle={!isConfirm}
            />
            <div className={`${styles.txtSection} ${isConfirm ? styles.visible : styles.hidden}`}>
                <div className={styles.circle}/>
                <span className={styles.subtxt}>{t("register.form.step1.inputs.confirmPassword.subtext")}</span>
            </div>
        </div>
    );
};
 
export default ConfirmPassword;