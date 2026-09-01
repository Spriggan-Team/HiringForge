import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

//-- Services
import RouteScheme from '../../route.scheme';
import { useAppContext } from '../../hooks/context';

//-- Services
import { navigateTo } from '../../App';
import AuthServices from "../../api/services/auth/auth";

//-- Exception
import { AccountNotFound, InvalidCredentials } from '../../api/services/auth/exceptions';
import { InvalidOTP } from '../../api/services/exceptions';

//-- Custom - React Component
import BasicInput from '../../layout/components/form/input/basic.input';

//-- SVG - Components
import LogoSVG from '/src/assets/custom-logo.svg?react';
import EmailSVG from '/src/assets/svg/email/email-1-svgrepo-com.svg?react';
import ConfirmPassword from '../../layout/components/form/input/password/confirm/confirm.password';
import PasswordSVG from "/src/assets/svg/security/password-protection-privacy-access-verification-code-svgrepo-com.svg?react"

//-- CSS - Styles
import styles from './style.module.css'
import { AccountRole } from '../../core/enums/AccountRole';
import { useAppNavigate } from '../../hooks/navigation';



const Login = () => {
    const { t } = useTranslation();
    const navigate = useAppNavigate();
    const { setPopup, setLoading } = useAppContext();

    const [animateBtn, setAnimateBtn] = useState(false);
    const [mode, setMode] = useState<"Login" | "ResetPassword">("Login");
    const [phase, setPhase] = useState<number>(0);


    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [isPasswordConfirm, setIsPasswordConfirm] = useState<boolean>(false);
    const [verificationCode, setVerificationCode] = useState("");


    /** API CALL HANDLERS */
    const handleLogin = async (clearEmail: string) => {
        const clearPassword = password.trim();
        if (!clearPassword) {
            setPopup({ status: "warning", message: t("login.messages.inputsWarning") });
            return;
        }

        setLoading({ state: true, subtitle: t("login.messages.loading") });
        const response = await AuthServices.login(clearEmail, clearPassword);
        const { role, token  } = response.data;

        localStorage.setItem("token", token);
        localStorage.setItem("role", role);

        setLoading({ state: false });
        setPopup({ status: "success", message: t("login.apiResponse.success") });

        if(role === AccountRole.USER){
            navigate(RouteScheme.userHome, { reloadNavState: true });
        }
        else if(role === AccountRole.CANDIDATE){
            navigate(RouteScheme.jobs, { reloadNavState: true  })
        }
    };


    const handleVerificationCodeRequest = async (clearEmail: string) => {
        setLoading({ state: true, subtitle: t("forgottenPassword.messages.verificationCode") });
        await AuthServices.askVerificationCode(clearEmail, "PASSWORD_RESET");

        setPhase(2);
        setLoading({ state: false });
        setPopup({status: "success", message: t("forgottenPassword.apiResponse.verificationCode.success")})
    };


    const handleResetPassword = async () => {
        if (!isPasswordConfirm) {
            setPopup({ status: "warning", message: t("forgottenPassword.messages.passwordMismatch") });
            return;
        }

        setLoading({ state: true, subtitle: t("forgottenPassword.messages.passwordModification") });
        await AuthServices.resetPassword({ email: email.trim(), password: password.trim(), verificationCode });
        setLoading({ state: false });

        setMode("Login");
        setPhase(0);
    };


    const handleSubmit = async (e: React.SubmitEvent) => {
        e.preventDefault();
        setAnimateBtn(true);
        
        const clearEmail = email.trim();
        if (!clearEmail) {
            setPopup({ status: "warning", message: t("forgottenPassword.inputs.email.required") });
            return;
        }

        try {
            if (mode === "Login") {
                await handleLogin(clearEmail);
            }
            else if (mode === "ResetPassword") {
                if (phase === 1) await handleVerificationCodeRequest(clearEmail);
                if (phase === 2) await handleResetPassword();
            }
        }
        catch (error) {
            setLoading({ state: false });
            if (error instanceof AccountNotFound) {
                setPopup({ status: "error", message: t("login.apiResponse.error.notFound") });
            }
            else if(error instanceof InvalidOTP){
                setPopup({ status: "error", message: t("forgottenPassword.apiResponse.verificationCode.error") });
            }
            else if (error instanceof InvalidCredentials) {
                setPopup({ status: "error", message: t("login.apiResponse.error.invalidCredentials") });
            }
            else{
                setPopup({ status: "error", message: t("global.messages.error") });
                console.error("Authentication error:", error);
            }
        }
        finally {
            setTimeout(() => setAnimateBtn(false), 1000);
        }
    };


    //** UI mode handler */
    const toggleMode = () => {
        if (mode === "ResetPassword") {
            setPhase(0);
            setMode("Login");
        } else {
            setPhase(1);
            setMode("ResetPassword");
        }
    };

    return (
        <div className={styles.container}>
            {/* Standard html form used to support native submit actions (Enter key) */}
            <form className={styles.card} onSubmit={handleSubmit}>
                <div 
                    className={styles.header}
                    onClick={()=>navigateTo(navigate, RouteScheme.jobs)}
                >
                    <LogoSVG className={styles.logo} width={113} height={113} />
                    <div className={styles.upperH}>
                        <h1 className={styles.title}>DigitalCop ATS</h1>
                        <p className={styles.undertxt}>
                            {mode === "ResetPassword" ? t("forgottenPassword.tagline") : t("login.tagline")}
                        </p>
                    </div>
                </div>

                <div className={styles.inputSection}>
                    {mode === "Login" ? (
                        <>
                            <BasicInput
                                width="100%"
                                svg={EmailSVG}
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                label={t("login.inputs.email.label")}
                                placeholder={t("login.inputs.email.placeholder")}
                            />
                            <BasicInput
                                width="100%"
                                type="password"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                label={t("login.inputs.password.label")}
                            />
                        </>
                    ) : phase === 1 ? (
                        <BasicInput
                            width="100%"
                            value={email}
                            svg={EmailSVG}
                            label={t("login.inputs.email.label")}
                            onChange={(e) => setEmail(e.target.value)}
                            placeholder={t("login.inputs.email.placeholder")}
                        />
                    ) : (
                        phase === 2 && (
                            <>
                                <BasicInput
                                    width="100%"
                                    type="password"
                                    value={password}
                                    onChange={(e) => setPassword(e.target.value)}
                                    label={t("login.inputs.password.label")}
                                />
                                <ConfirmPassword
                                    width="100%"
                                    type="password"
                                    password={password}
                                    setConfirm={setIsPasswordConfirm}
                                    label={t("forgottenPassword.inputs.confirmPassword.label")}
                                    validTxt={t("register.accountInformation.inputs.confirmPassword.label")}
                                    invalidTxt={t("register.accountInformation.inputs.confirmPassword.invalid")}
                                />
                                <BasicInput
                                    required
                                    width="100%"
                                    svg={PasswordSVG}
                                    type="password"
                                    value={verificationCode}
                                    className="faint-border"
                                    inputName="verificationCode"
                                    onChange={(e) => setVerificationCode(e.target.value)}
                                    label={t("forgottenPassword.inputs.verificationCode.label")}
                                />
                            </>
                        )
                    )}
                </div>
                
                {/** BOTTOM (BUTTON & LINK) */}
                <div className={styles.btnWrapper}>
                    <button type="submit" className={`${styles.logInBtn} ${animateBtn ? styles.animate : ""}`}>
                        {mode === "Login"
                            ? t("global.buttons.connexion")
                            : mode === "ResetPassword"
                            ? phase == 1 ? 
                                t("forgottenPassword.buttons.sentOTPCode")
                                : phase === 2 
                                    ? t("forgottenPassword.buttons.resetBtn") : ""
                            : ""
                        }
                    </button>
                </div>

                <div className={styles.options}>
                    <button type="button" className={styles.linkBtn} onClick={toggleMode}>
                        {mode === "Login" ? t("login.links.forgottenPassword") : t("forgottenPassword.links.login")}
                    </button>
                    <Link to={RouteScheme.register}>{t("login.links.signIn")}</Link>
                </div>

                <div className={styles.footer}>
                    <h5>{t("global.allRightsReserved")}</h5>
                </div>
            </form>
        </div>
    );
};

export default Login;