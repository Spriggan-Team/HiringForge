import { useState } from 'react';
import { Link } from 'react-router-dom';
import RouteScheme from '../../route.scheme';

//Custom - React Component
import BasicInput from '../../layout/components/form/input/basic.input';

//SVG - Components
import LogoSVG from '../../assets/custom-logo.svg';
import EmailSVG from '../../assets/svg/email/email-1-svgrepo-com.svg';
import PasswordDVG from '../../assets/svg/password-svgrepo-com.svg'

// CSS - Styles
import styles from './style.module.css'
import Separator from '../../layout/components/separator/separator';





const Login = () => {

    const [animate, setAnimate] = useState(false);

    return ( 
        <div className={styles.container}>
            <div className={styles.card}>
                
                <div className={styles.header}>
                    <LogoSVG className={styles.logo} width={113} height={113} />
                    <div className={styles.upperH}>
                        <h1 className={styles.title} >DigitalCop ATS</h1>
                        <p className={styles.undertxt}>Connexion à votre espace</p>
                    </div>
                </div>

                <div className={styles.inputSection}>
                    <BasicInput 
                        width="100%"
                        svg={EmailSVG}
                        label='Email'
                        placeholder='email@example.com'
                    />
                    <BasicInput 
                        width="100%"
                        svg={PasswordDVG} 
                        label='Password'
                        type='password'
                    />
                </div>

                <div style={{ width: "100%", display: "flex", justifyContent: "center"}}>
                    <button 
                        className={`${styles.logInBtn} ${animate ? styles.animate : ""}`}
                        onClick={()=>{
                            setAnimate(false);
                            requestAnimationFrame(()=>{
                                setAnimate(true);

                                setTimeout(()=>{
                                    setAnimate(true);
                                }, 900)
                            })
                        }}
                    >
                        Log in
                    </button>
                </div>

                <div className={styles.options}>
                    <Link to={RouteScheme.forgottenPassword}>Forgotten password ? </Link>
                    <Link to={RouteScheme.register}>Sign In</Link>
                </div>

                <div className={styles.footer}>
                    <Separator  />
                    <h5>2026 DigitalCop - All right reserved</h5>
                </div>
            </div>
        </div>
    );
}
 
export default Login;