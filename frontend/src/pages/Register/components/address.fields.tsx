import React from 'react';
import { useTranslation } from 'react-i18next';


import styles from './AddressFields.module.css';
import type { ParseKeys } from 'i18next';
import BasicInput from '../../../layout/components/form/input/basic.input';

export interface AddressData {
  country?: string;
  postalCode?: string;
  city?: string;
  street?: string;
}


interface AddressFieldsProps {
  address: AddressData;
  onChange: (updatedFields: Partial<AddressData>) => void;
  titleKey?: ParseKeys; //-- Dynamic title
  requirements?:{
    city?: boolean,
    postalCode?: boolean,
    street?: boolean,
    country?: boolean
  }
}

export const AddressFields: React.FC<AddressFieldsProps> = ({
  address,
  onChange,
  titleKey = "userRegister.form.aside.addressDetails.title" as ParseKeys,
  requirements = {
    city: true,
    postalCode: true,
    street: true,
    country: true
  }
}) => {
  const { t } = useTranslation();

  return (
    <div className={styles.addressContainer}>
      <h3>{t(titleKey)}</h3>

      <div className={styles.geoposSection}>
        {/* Country */}
        <BasicInput
          required={requirements.country}
          placeholder="France"
          className={styles.faintBorder}
          value={address.country || ""}
          onChange={(e) => onChange({ country: e.target.value })}
          backgroundColor="#FBFAFE"
          width="100%"
          label={t("global.address.country")}
        />

        <div className={styles.inpts}>
          {/*  Postal Code */}
          <BasicInput
            required={requirements.postalCode}
            placeholder="75002"
            label={t("global.address.postalCode")}
            backgroundColor="#FBFAFE"
            width="100%"
            className={styles.faintBorder}
            value={address.postalCode || ""}
            onChange={(e) => onChange({ postalCode: e.target.value })}
          />

          {/* City */}
          <BasicInput
            required={requirements.city}
            width="100%"
            placeholder="Paris"
            className={styles.faintBorder}
            backgroundColor="#FBFAFE"
            value={address.city || ""}
            onChange={(e) => onChange({ city: e.target.value })}
            label={t("global.address.city")}
          />
        </div>

        {/* Street */}
        <BasicInput
          required={requirements.street}
          width="100%"
          className={styles.faintBorder}
          backgroundColor="#FBFAFE"
          value={address.street || ""}
          onChange={(e) => onChange({ street: e.target.value })}
          placeholder="32 rue st michelle"
          label={t("global.address.street")}
        />
      </div>
    </div>
  );
};

export default AddressFields;