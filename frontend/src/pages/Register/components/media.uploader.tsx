import React, { useEffect, useMemo } from 'react';
import type { ParseKeys } from 'i18next';
import { useTranslation } from 'react-i18next';

import DownloadButton from '../../../layout/components/buttons/download/download.button';

import LogoSVG from '/src/assets/custom-logo.svg';

import styles from './MediaUploader.module.css';


interface MediaUploaderProps {
  file: File | null;
  onFileChange: (file: File | null) => void;
  titleKey?: ParseKeys; // title of the section
  buttonTaglineKey?: ParseKeys; // text on import button
  defaultIcon?: React.ReactNode; // display default svg if needed
}



export const MediaUploader: React.FC<MediaUploaderProps> = ({
  file,
  onFileChange,
  titleKey = "global.logo.text" as ParseKeys,
  buttonTaglineKey = "userRegister.form.aside.downloadAssets.logo.tagline" as ParseKeys,
  defaultIcon,
}) => {
  const { t } = useTranslation();

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
            <img src={previewUrl} alt="Preview" />
          </div>
        ) : (
          defaultIcon || <LogoSVG width={45} height={45} />
        )}

        <DownloadButton
          txt={t(buttonTaglineKey)}
          onNext={(uploadedFile) => {
            if (uploadedFile) onFileChange(uploadedFile);
          }}
        />
        <span className={styles.subtxt}>PNG, JPG ...</span>
      </div>
    </div>
  );
};

export default MediaUploader;