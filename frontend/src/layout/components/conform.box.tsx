import React from 'react';
import styles from './ConfirmModal.module.css';

export interface ConfirmModalProps {
    title: string;
    message: React.ReactNode;
    warningText?: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'danger' | 'primary' | 'warning';
    onConfirm: () => void;
    onCancel: () => void;
}

export const ConfirmModal: React.FC<ConfirmModalProps> = ({
    title,
    message,
    warningText,
    confirmText = 'Confirmer',
    cancelText = 'Annuler',
    variant = 'primary',
    onConfirm,
    onCancel,
}) => {
    return (
        <div className={styles.modalContent}>
            <div className={styles.modalHeader}>
                <h3>{title}</h3>
            </div>
            
            <div className={styles.modalBody}>
                <div className={styles.message}>{message}</div>
                
                {warningText && (
                    <div className={`${styles.warningBox} ${styles[`warning_${variant}`]}`}>
                        ⚠️ {warningText}
                    </div>
                )}
            </div>

            <div className={styles.modalActions}>
                <button 
                    type="button" 
                    className={styles.btnCancel} 
                    onClick={onCancel}
                >
                    {cancelText}
                </button>
                <button 
                    type="button" 
                    className={`${styles.btnConfirm} ${styles[`btn_${variant}`]}`} 
                    onClick={onConfirm}
                >
                    {confirmText}
                </button>
            </div>
        </div>
    );
};