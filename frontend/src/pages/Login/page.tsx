import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

//-- Services
import RouteScheme from '../../route.scheme';
import { useAppContext } from '../../hooks/context';
import { HttpBadResponse } from '../../api/exceptions';

//-- Services
import AuthServices from "../../api/services/auth/auth";


//-- Exception
import { AccountNotFound, InvalidCredentials } from '../../api/services/auth/exceptions';


//-- Custom - React Component
import BasicInput from '../../layout/components/form/input/basic.input';

//-- SVG - Components
import LogoSVG from '/src/assets/custom-logo.svg';
import EmailSVG from '/src/assets/svg/email/email-1-svgrepo-com.svg';

//-- CSS - Styles
import styles from './style.module.css'
import { AccountRole } from '../../core/enums/AccountRole';



const Login = () => {
    const { t } = useTranslation();
    const [animateBtn, setAnimateBtn] = useState(false);

    const [email, setEmail] = useState("");
    const [password, setPassword]  = useState("");

    const [mode, setMode] = useState<"Login" | "ResetPassword">("Login");
    
    const navigation = useNavigate();
    const { setPopup, setLoading } = useAppContext();

    //-- Langin handler
    const handleLogin = async ()=>{
        try {
            setLoading({ state: true, subtitle: t('login.messages.loading') });
            console.log({email, password});

            const response = await AuthServices.login(email, password);
            const role = response.data.role;

            localStorage.setItem("token", response.data.token);
            localStorage.setItem("role", role);

            setLoading({ state: false  })
            setPopup({ status: "success", message: t("login.apiResponse.success") });
            
            if(role === AccountRole.USER){
                
            }
            navigation(RouteScheme.home)
        }
        catch (error) {
            setLoading({ state: false })

            if(error instanceof Error){
                if(error instanceof HttpBadResponse){
                    if(error instanceof AccountNotFound)
                        setPopup({ status: "error", message: t("login.apiResponse.error.notFound") });
                    if(error instanceof InvalidCredentials)
                        setPopup({status: "error", message: t("login.apiResponse.error.invalidCredentials")})
                }
                console.log("Something went wrong:", error.message);
                console.log("Stack:", error.stack);         
            }
            else{
                setPopup({
                    status: "error",
                    message: t("global.messages.error")
                });
                console.log("Unknown error:", error);
            }
        }
    }

    const handleResetPassword = ()=>{
        
    }

    return ( 
        <div className={styles.container}>
            <div className={styles.card}>
                
                <div className={styles.header}>
                    <LogoSVG className={styles.logo} width={113} height={113} />
                    <div className={styles.upperH}>
                        <h1 className={styles.title} >DigitalCop ATS</h1>
                        <p className={styles.undertxt}>{t("login.tagline")}</p>
                    </div>
                </div>

                <div className={styles.inputSection}>
                    <BasicInput 
                        width="100%"
                        svg={EmailSVG}
                        onChange={(e)=> setEmail(e.target.value)}
                        label={t("login.inputs.email.label")}
                        placeholder= {t("login.inputs.email.placeholder")}
                    />
                    <BasicInput 
                        width="100%"
                        onChange={(e) => setPassword(e.target.value)}
                        label={t("login.inputs.password.label")}
                        type='password'
                    />
                </div>

                <div style={{ width: "100%", display: "flex", justifyContent: "center"}}>
                    <button 
                        className={`${styles.logInBtn} ${animateBtn ? styles.animate : ""}`}
                        onClick={()=>{
                            setAnimateBtn(false);
                            requestAnimationFrame(()=>{
                                setAnimateBtn(true);
                                handleLogin();
                                setTimeout(()=>{
                                    setAnimateBtn(true);
                                }, 900)
                            })
                        }}
                    >
                        {t("login.buttons.logbtn")}
                    </button>
                </div>

                <div className={styles.options}>
                    <Link to={RouteScheme.forgottenPassword}>{t("login.links.forgottenPassword")}</Link>
                    <Link to={RouteScheme.register}>{t("login.links.signIn")}</Link>
                </div>

                <div className={styles.footer}>
                    <h5>{t("global.allRightsReserved")}</h5>
                </div>
            </div>
        </div>
    );
}
 
export default Login;