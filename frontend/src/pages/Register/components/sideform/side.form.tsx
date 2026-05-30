import { useEffect, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import DownloadButton from "../../../../layout/components/buttons/download/download.button";
import BasicInput from "../../../../layout/components/form/input/basic.input";

import LogoSVG from '/src/assets/custom-logo.svg';
import AddSVG from "/src/assets/svg/add/add-svgrepo-com.svg";

import styles from "./style.module.css";
import type { AsideFormState } from "../../page";



interface ImageObject {
    file: File;
    previewUrl: string;
}


interface SideFormProps {
    form: AsideFormState;
    setForm: React.Dispatch<React.SetStateAction<AsideFormState>>;
}


const SideForm: React.FC<SideFormProps> = ({ form, setForm }) => {
    const { t } = useTranslation();

    const [currentLogoImg, setCurrentLogoImg] = useState(""); 
    const [images, setImages] = useState<ImageObject[]>([]); 
    
    const imagesRef = useRef<ImageObject[]>([]); 
    const addImageInputRef = useRef<HTMLInputElement | null>(null);

    //-- clear ram
    useEffect(() => {
        return () => {
            if (currentLogoImg) URL.revokeObjectURL(currentLogoImg);
            imagesRef.current.forEach((img) => URL.revokeObjectURL(img.previewUrl));
        };
    }, []); 

    // Synchronize ref while unmounting
    useEffect(() => {
        imagesRef.current = images;
    }, [images]);

    return ( 
        <div>
            <div className={styles.sideForm}>
                {/* TOP */}
                <div className={styles.top}>
                    <h3>{t("register.form.aside.title")}</h3>
                    
                    {/* DOWNLOAD LOGO SECTION */}
                    <div className={styles.downloadLogoSection}>
                        <h4 className={styles.logoTitle}>
                            {t("global.logo.text")} <span className="faint-txt">({t("global.validation.required")})</span>
                        </h4>
                        <div className={styles.downloadBox}>
                            {currentLogoImg ? (
                                <div className={styles.logoPreview}>
                                    <button 
                                        type="button"
                                        className={styles.removeBtn} 
                                        onClick={() => {
                                            URL.revokeObjectURL(currentLogoImg);
                                            setCurrentLogoImg("");
                                            //-- parent form state 
                                            setForm(prev => ({ ...prev, logo: null }));
                                        }}
                                    >
                                        x
                                    </button>
                                    <img src={currentLogoImg} alt="Logo preview" />
                                </div>
                            ) : (
                                <LogoSVG width={45} height={45} />
                            )}
                            <DownloadButton
                                onNext={(file) => {
                                    if (file) {
                                        //-- revoke previous img
                                        if (currentLogoImg) URL.revokeObjectURL(currentLogoImg);

                                        const url = URL.createObjectURL(file);
                                        setCurrentLogoImg(url);
                                        setForm(prev => ({
                                            ...prev,
                                            logo: file
                                        }));
                                    }
                                }}
                            />
                            <span className={styles.subtxt}>PNG, JPG ... </span>
                        </div>
                    </div>

                    {/* IMAGES DOWNLOAD SECTION */}
                    <div className={styles.downloadImageSection}>
                        <h3>
                            {t("register.form.aside.downloadAssets.companyPhoto.tagline")}
                            <span className="faint-txt">&nbsp;({t("global.validation.optionnal")})</span>
                        </h3>
                        <div className={styles.images}>
                            {Array.isArray(images) && images.length > 0 && (
                                images.map((image, index) => (
                                    <div key={`${image.file.name}-${index}`} style={{ position: "relative" }}>
                                        <button 
                                            type="button"
                                            className={styles.removeBtn}
                                            onClick={() => { 
                                                //-- free space
                                                URL.revokeObjectURL(image.previewUrl);
                                                
                                                //-- display images
                                                setImages((prev) => prev.filter((_, i) => i !== index));
                                                
                                                //--update parent state
                                                setForm((prev) => ({
                                                    ...prev,
                                                    images: prev.images.filter((_, i) => i !== index)
                                                }));
                                            }}
                                        >
                                            x
                                        </button>
                                        <img alt="uploadIme" src={image.previewUrl} />
                                    </div>
                                ))
                            )}   
                            <div 
                                className={styles.addBox}
                                onClick={() => { if (addImageInputRef.current) addImageInputRef.current.click(); }}
                            >
                                <AddSVG width={45} height={45}/>
                                <span>{t("register.form.aside.downloadAssets.pictures.tagline")}</span>
                                <input 
                                    ref={addImageInputRef}
                                    accept="image/*"
                                    style={{ display: "none" }}
                                    type="file"
                                    multiple
                                    onChange={(event) => {
                                        const files = event.target.files;
                                        if (files && files.length > 0) {
                                            const filesArray = Array.from(files);

                                            const newImagesMapped = filesArray.map((file) => ({
                                                file: file,
                                                previewUrl: URL.createObjectURL(file) 
                                            }));

                                            setImages((prev) => [...prev, ...newImagesMapped]);

                                            setForm((previous) => ({
                                                ...previous, 
                                                images: [...previous.images, ...filesArray]
                                            }));
                                        }
                                        event.target.value = "";
                                    }}
                                />
                            </div>
                        </div>
                    </div>
                </div>

                {/* BOTTOM */}
                <div className={styles.bottom}>
                    <h3>{t("register.form.aside.addressDetails.title")}</h3>
                    <div className={styles.geoposSection}>
                        <BasicInput 
                            placeholder="France"
                            value={form.country || ""}
                            onChange={(e) =>
                                setForm(prev => ({ ...prev, country: e.target.value }))
                            }
                            backgroundColor="#FBFAFE" width="100%"
                            label={t("register.form.aside.addressDetails.inputs.country.label")}
                        />
                        <div className={styles.inpts}>
                            <BasicInput 
                                placeholder="75002"
                                label={t("register.form.aside.addressDetails.inputs.postalCode.label")}
                                backgroundColor="#FBFAFE" width="100%"
                                className="faint-border"
                                value={form.postalCode || ""}
                                onChange={(e) =>
                                    setForm(prev => ({ ...prev, postalCode: e.target.value }))
                                }
                            />
                            <BasicInput 
                                width="100%" 
                                placeholder="Paris"
                                className="faint-border"
                                backgroundColor="#FBFAFE"
                                value={form.city || ""}
                                onChange={(e) =>
                                    setForm(prev => ({ ...prev, city: e.target.value }))
                                }
                                label={t("register.form.aside.addressDetails.inputs.city.label")}
                            />
                        </div>
                        <BasicInput 
                            width="100%" 
                            className="faint-border" 
                            backgroundColor="#FBFAFE" 
                            value={form.street || ""}
                            onChange={(e) =>
                                setForm(prev => ({ ...prev, street: e.target.value }))
                            }
                            placeholder="32 rue st michelle"
                            label={t("register.form.aside.addressDetails.inputs.street.label")}
                        />
                    </div>
                </div>

                {/* OVER - ABSOLUTE ELEMENT */}
                <div className={styles.bage}>
                    {/* <SecurityBadge /> */}
                </div>
            </div>
        </div>
    );
};

export default SideForm;