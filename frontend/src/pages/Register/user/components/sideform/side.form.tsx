import { useEffect, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

import DownloadButton from "../../../../../layout/components/buttons/download/download.button";
import BasicInput from "../../../../../layout/components/form/input/basic.input";
import type { AsideFormState } from "../../page";

import LogoSVG from '/src/assets/custom-logo.svg?react';
import AddSVG from "/src/assets/svg/add/add-svgrepo-com.svg?react";

import styles from "./style.module.css";
import type { ParseKeys } from "i18next";
import MediaUploader from "../../../../components/uploader/media.uploader";
import AddressFields from "../../../components/address.fields";



interface ImageObject {
    file: File;
    previewUrl: string;
}


interface SideFormProps {
    form: AsideFormState;
    asideFormRef?: React.RefObject<HTMLFormElement | null>;
    setForm: React.Dispatch<React.SetStateAction<AsideFormState>>;
    handleSubmit?: React.SubmitEventHandler;
}


const SideForm: React.FC<SideFormProps> = ({ 
    form, setForm, 
    asideFormRef, handleSubmit 
}) => {
    const { t } = useTranslation();

    const [currentLogoImg, setCurrentLogoImg] = useState(""); 
    const [images, setImages] = useState<ImageObject[]>([]); 
    
    const imagesRef = useRef<ImageObject[]>([]); 
    const addImageInputRef = useRef<HTMLInputElement | null>(null);

    //-- clear image ressources  (ram)
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

    const handleFormSubmit = (event: React.SubmitEvent<HTMLFormElement>) => {
        event.preventDefault();
        event.stopPropagation();

        const asideForm = asideFormRef?.current;
        if (!asideForm) {
            console.warn("The aside form has still not completely been mounted");
            return;
        }

        if (handleSubmit) {
            handleSubmit(event);
        }
    };

    return ( 
        <div>
            <div className={styles.sideForm}>
                <form 
                    ref={asideFormRef} action=""
                    onSubmit={handleFormSubmit}
                >
                    {/* TOP */}
                    <div className={styles.top}>
                        <h3>{t("userRegister.form.aside.title")}</h3>
                        
                        {/* DOWNLOAD LOGO SECTION */}
                        <MediaUploader
                            file={form.logo}
                            onFileChange={(file) => setForm(prev => ({ ...prev, logo: file }))}
                            titleKey={"global.logo.text" as ParseKeys}
                        />

                        {/* IMAGES DOWNLOAD SECTION */}
                        <div className={styles.downloadImageSection}>
                            <h3>
                                {t("userRegister.form.aside.downloadAssets.companyPhoto.tagline")}
                                <span className={styles.faintTxt}>&nbsp;({t("global.validation.optionnal")})</span>
                            </h3>
                            <div className={styles.images}>
                                {Array.isArray(images) && images.length > 0 && (
                                    images.map((image, index) => (
                                        <div key={`${image.file.name}-${index}`} className={styles.imageWrapper}>
                                            <button 
                                                type="button"
                                                className={styles.removeBtn}
                                                onClick={() => { 
                                                    URL.revokeObjectURL(image.previewUrl);
                                                    
                                                    //-- file to delete
                                                    const fileToRemove = image.file;

                                                    setImages((prev) => prev.filter((img) => img.file !== fileToRemove));
                                                    
                                                    setForm((prev) => ({
                                                        ...prev,
                                                        //-- secure filter (with reference)
                                                        images: prev.images.filter((file) => file !== fileToRemove)
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
                                    <span>{t("userRegister.form.aside.downloadAssets.pictures.tagline")}</span>
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

                    {/* BOTTOM  (ADDRESS) */}
                    <AddressFields
                        address={{
                            country: form.country,
                            postalCode: form.postalCode,
                            city: form.city,
                            street: form.street,
                        }}
                        onChange={(fields) => setForm(prev => ({ ...prev, ...fields }))}
                        titleKey={"userRegister.form.aside.addressDetails.title" as ParseKeys}
                    />

                    {/* OVER - ABSOLUTE ELEMENT */}
                    <div className={styles.bage}>
                        {/* <SecurityBadge /> */}
                    </div>
                </form>
            </div>
        </div>
    );
};

export default SideForm;