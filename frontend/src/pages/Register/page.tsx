
import { useEffect, useRef, useState } from "react";
import { Link } from "react-router-dom";
import { useTranslation } from "react-i18next";

//--Config Object
import RouteScheme from "../../route.scheme";


//-- CORE COMPONENTS 
import AccountAccess from "./components/access/account.access";


//-- Custom - React Component
import BasicInput from '../../layout/components/form/input/basic.input';
import DownloadButton from "../../layout/components/buttons/download/download.button";
import SecurityBadge from "../../layout/components/badges/security.badge";
import SimpleButton from "../../layout/components/buttons/simple/simple.button";
import StageTitle from "../../layout/components/indicators/stage/stage.title";
import StatusItem from "../../layout/components/indicators/statusItem/status.item";
import Gauge from "../../layout/components/progress/gauge/gauge";


//-- SVG - Components
import LogoSVG from '/src/assets/custom-logo.svg';
import BrowserSVG from '/src/assets/svg/net/internet-svgrepo-com.svg';
import DownArrowSVG from '/src/assets/svg/arrows/down-arrow-5-svgrepo-com.svg';
import AddSVG from "/src/assets/svg/add/add-svgrepo-com.svg"

//-- Images - Ressources
import OfficeWorkerImage from "../../assets/images/office-worker.png"

//-- CSS Styles
import styles from "./style.module.css"
import  IdentityDetail from "./components/identity/identity.details";


const REGISTERING_TOTAL_STEP = 3;

interface ImageObject {
    file: File;
    previewUrl: string;
}

const Register = () => {
    const { t } = useTranslation()

    const formData = useRef<FormData>(new FormData());
    const [currentStep, setCurrentStep] = useState({ max: 1, current: 1});
    const [language, setLanguage] = useState("Français");

    
    
    const imagesRef = useRef<ImageObject[]>([]); //-- contains all images
    const [currentLogoImg, setCurrentLogoImg] = useState(""); 
    const [images, setImages] = useState<ImageObject[]>([]); //-- contains a set of images

    
    useEffect(() => {
        imagesRef.current = images;
    }, [images]);

    useEffect(() => {
        return () => {
            URL.revokeObjectURL(currentLogoImg);
            imagesRef.current.forEach((img) => URL.revokeObjectURL(img.previewUrl));
        };
    }, []); 


    const addImageInputRef = useRef<HTMLInputElement | null>(null);

    return (
        <div className={styles.container}>
            <nav className={styles.navbar}>
                <div className={styles.leading}>
                    <LogoSVG width={45} height={45} />
                    <h1>DigitalCop ATS</h1>
                </div>

                <div className={styles.actions}>
                    <div className={`${styles.translateSection} faint-border`}>
                        <BrowserSVG className={styles.browserSVG} width={25} height={25}  />
                        <span>{language}</span>
                        <DownArrowSVG className={styles.arrowSVG} width={25} height={25} />
                    </div>

                    <div className={styles.logInBtn}>
                        <span>{t("register.subtext.alreadyHaveAccount")}</span><Link to={RouteScheme.login}>{t("register.buttons.logbtn")}</Link>
                    </div>
                </div>         
            </nav>

           {/* MAIN CONTENT */}

            <div className={`faint-border ${styles.mainContainer}`}>
                
                {/* PROCESS DESCRIPTION */}
                <div className={styles.infoBox}>
                    <div className={styles.stagesSection}>
                        <StageTitle step={1} txt={t("register.processDescription.one")} active = {currentStep.current === 1} />
                        <StageTitle step={2} txt={t("register.processDescription.two")} active = {currentStep.current === 2} />
                        <StageTitle step={3} txt={t("register.processDescription.three")} active = {currentStep.current === 3}  />
                    </div>
                    <div className={styles.statusItemSection}>
                        <StatusItem text={t("register.processDescription.overall.0")}/>
                        <StatusItem text={t("register.processDescription.overall.1")}/>
                        <StatusItem text={t("register.processDescription.overall.2")}/>
                    </div>
                    <img src={OfficeWorkerImage} alt="" />
                </div>
                
                {/* MAIN FORM */}

                <div className={styles.mainForm}>
                    <div className={styles.header}>
                        <h3 className={styles.formTitle}>{t("register.form.title")}</h3>
                        <div className={styles.desc}>
                            <p>{t("register.form.subtitle")}</p>
                            <div className={styles.progessContainer}>
                                <span>{t("register.form.currentStep", {count: currentStep, totalCount: 3})}</span>
                                <Gauge 
                                    width={"50%"} height={2.5}
                                    activeColor="#003DE7"
                                    foregroundColor="#D9D9D9"
                                    percent={currentStep.current / REGISTERING_TOTAL_STEP} 
                                />
                            </div>
                        </div>
                    </div>
                    <div className={styles.form}>
                        {currentStep.current == 1 ?
                            <AccountAccess formData={formData.current}  onNext={()=>{setCurrentStep(prev => ({...prev, current: 2}))}} />
                            : currentStep.current == 2 ?
                                <IdentityDetail />
                                : <></>    
                        }
                    </div>
                </div>


                {/* SIDE FORM */}

                <div className={styles.sideForm}>
                    {/* TOP */}
                    <div className={styles.top}>
                        <h3>{t("register.form.aside.title")}</h3>
                         {/* DOWNLOAD LOGO SECTION */}
                        <div className={styles.downloadLogoSection}>
                            <h4 className={styles.logoTitle}>{t("global.logo.text")} <span className="faint-txt">({t("global.validation.required")})</span></h4>
                            <div className={styles.downloadBox}>
                                { currentLogoImg ? (
                                    <div className={styles.logoPreview}>
                                        <button 
                                            className={styles.removeBtn} 
                                            onClick={()=>{
                                                setCurrentLogoImg("")
                                                formData.current.delete("logo")
                                            }}
                                        >
                                            x
                                        </button>
                                        <img src={currentLogoImg} />
                                    </div>) 
                                    :   <LogoSVG width={45} height={45} />
                                }
                                <DownloadButton
                                    onNext={(file)=> {
                                        if(file){
                                            const url = URL.createObjectURL(file);
                                            setCurrentLogoImg(url);
                                            formData.current.set("logo", file);
                                        }
                                    }}
                                />
                                <span className={styles.subtxt}>PNG, JPG ... </span>
                            </div>
                        </div>

                         {/* IMAGES DOWNLOAD SECTION */}
                        <div className={styles.downloadImageSection}>
                            <h3>{t("register.form.aside.downloadAssets.companyPhoto.tagline")}<span className="faint-txt">&nbsp;({t("global.validation.optionnal")})</span></h3>
                            <div className={styles.images}>
                                { Array.isArray(images) && images.length > 0 && (
                                    images.map((image, index)=> (
                                        <div key={`${image.file.name}-${index}`} style={{position: "relative"}}>
                                            <button 
                                                className={styles.removeBtn}
                                                onClick={()=>{ 
                                                    setImages((prev)=> prev.filter((_, i)=> i !== index ))
                                                }}
                                            >
                                                x
                                            </button>
                                            <img  alt="uploadIme" src={image.previewUrl} />
                                        </div>
                                    ))
                                )}   
                                <div 
                                    className={styles.addBox}
                                    onClick={()=>{ if(addImageInputRef.current) addImageInputRef.current.click() }}
                                >
                                    <AddSVG width={45} height={45}/>
                                    <span>{t("register.form.aside.downloadAssets.pictures.tagline")}</span>
                                    <input 
                                        ref={addImageInputRef}
                                        accept="image/*"
                                        style={{ display: "contents" }}
                                        type="file"
                                        multiple
                                        onChange={(event) => {
                                            const files = event.target.files;
                                            if (files) {
                                                const newImages = Array.from(files).map((file) => ({
                                                    file: file,
                                                    previewUrl: URL.createObjectURL(file) 
                                                }));
                                                setImages((previous)=>([...previous, ...newImages]))
                                            }
                                            event.target.value = ""
                                        }}
                                    />
                                </div>
                            </div>
                        </div>

                    </div>

                    {/* BOTTOM */}
                    <div className={styles.bottom}>
                        {/* ADDRESS SECTION */}
                        <h3>{t("register.form.aside.addressDetails.title")}</h3>
                        <div className={styles.geoposSection}>
                            <BasicInput backgroundColor="#FBFAFE" width="100%" label={t("register.form.aside.addressDetails.inputs.country.label")} placeholder="France" />
                            <div className={styles.inpts}>
                                <BasicInput className="faint-border" backgroundColor="#FBFAFE" width="100%" label={t("register.form.aside.addressDetails.inputs.postalCode.label")}  placeholder="75002" />
                                <BasicInput className="faint-border" backgroundColor="#FBFAFE" width="100%"  label={t("register.form.aside.addressDetails.inputs.city.label")} placeholder="Paris" />
                            </div>
                            <BasicInput className="faint-border" backgroundColor="#FBFAFE" width="100%" label={t("register.form.aside.addressDetails.inputs.address.label")}placeholder="32 rue st michelle" />
                        </div>
                    </div>

                    {/* OVER - ABSOLUTE ELEMENT */}
                    <div className={styles.bage}>
                        {/* <SecurityBadge /> */}
                    </div>
                </div>

            </div>


        </div>
    );
}
 
export default Register;

{/*  FOOTER */}
{/* <div className={styles.footer}>
    <SimpleButton />
    <div>
        <span> Etape1 </span>
        <span> Etape2 </span>
        <span> Etape3 </span>
    </div>
    <SimpleButton />
</div> */}