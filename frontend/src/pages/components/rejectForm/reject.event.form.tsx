

import React, { useEffect, useState } from 'react'

import CustomTextarea from '../../../layout/components/form/input/textarea/custom.textarea';
import InputLabel from '../../../layout/components/form/input/input.label';

import styles from './RejectEventForm.module.css'

/**
 * ------------------
 * component 
 * ------------------
 */

interface RejectEventFormProps {
    initialValue?: string;
    placeholder?: string;
    onConfirm: (reason: string) => void;
}

const RejectEventForm: React.FC<RejectEventFormProps> = ({
    initialValue = "",
    placeholder,
    onConfirm
}) => {
    const [localReason, setLocalReason] = useState<string>(initialValue);

    return (
        <div className={styles.container}>
            <InputLabel 
                label='Reason'
            />
            <CustomTextarea 
                value={localReason}
                placeholder={placeholder}
                setValue={setLocalReason}
            />
            <div className={styles.buttonSection}>
                <button
                    type="button"
                    className={styles.confirmRefuseBtn}
                    onClick={() => {
                        onConfirm(localReason);
                    }}
                >
                    Confirm Refusal
                </button>
            </div>
        </div>
    );
};

export default RejectEventForm;