import React, { useEffect, useMemo, type InputHTMLAttributes } from 'react';
import type { ParseKeys } from 'i18next';
import { useTranslation } from 'react-i18next';

import DownloadButton from '../../../layout/components/buttons/download/download.button';

import LogoSVG from '/src/assets/custom-logo.svg?react';
import PdfIcon from "/src/assets/images/pdf.png";

import styles from './MediaUploader.module.css';


interface MediaUploaderProps {
  file: File | null;
  onFileChange: (file: File | null) => void;
  titleKey?: ParseKeys; // title of the section
  buttonTaglineKey?: ParseKeys; // text on import button
  defaultIcon?: React.ReactNode; // display default svg if needed
  extensions?:string;
  inputAttributes?: InputHTMLAttributes<HTMLInputElement>
  
}



export const MediaUploader: React.FC<MediaUploaderProps> = ({
  file,
  onFileChange,
  titleKey = "global.logo.text" as ParseKeys,
  buttonTaglineKey = "userRegister.form.aside.downloadAssets.logo.tagline" as ParseKeys,
  defaultIcon,
  extensions= "PNG, JPG ...",
  inputAttributes
}) => {
  const { t } = useTranslation();

  const isImage = file?.type.startsWith("image/");
  const isPdf = file?.type === "application/pdf";

  //-- Url dynamic generation
  const previewUrl = useMemo(() => {
    return file ? URL.createObjectURL(file) : null;
  }, [file]);

  //-- clear memory
  useEffect(() => {
    return () => {
      if (previewUrl)
        URL.revokeObjectURL(previewUrl);
    };
  }, [previewUrl]);


  return (
    <div className={styles.downloadLogoSection}>
      <h4 className={styles.title}>
        {t(titleKey)}{" "}
        <span className={styles.faintTxt}>({t("global.validation.optionnal")})</span>
      </h4>

      <div className={styles.downloadBox}>
        {previewUrl ? (
          <div className={styles.logoPreview}>
            <button
              type="button"
              className={styles.removeBtn}
              onClick={() => onFileChange(null)}
            >
              x
            </button>
            {
              isImage ? (
                <img src={previewUrl} alt="Preview" />
              ) : isPdf ? (
                <img src={PdfIcon} width={45} height={45} />
              ) : <></>
            }
          </div>
        ) : (
          defaultIcon || <LogoSVG width={45} height={45} />
        )}

        <DownloadButton
          inputAttributes={inputAttributes}
          txt={t(buttonTaglineKey)}
          onNext={(uploadedFile) => {
            if (uploadedFile) onFileChange(uploadedFile);
          }}
        />

        <div style={{ display: "flex", flexDirection: "column", justifyContent: "center", alignItems: "center" }}>
          <span className={styles.subtxt}>{extensions}</span>
          <span className={styles.subtxt}>{file?.name}</span>
        </div>

      </div>
    </div>
  );
};

export default MediaUploader;