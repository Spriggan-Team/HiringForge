

import React from 'react';
import styles from './ApplicationDetailModal.module.css'

export interface Application {
    id: string;
    candidate: string;
    email: string;
    matchScore: number;
    status: string;
    avatarUrl?: string;
    appliedAt: string;
}

interface ApplicationDetailModalProps {
    application: Application;
    onClose: () => void;
}

export const ApplicationDetailModal: React.FC<ApplicationDetailModalProps> = ({ application, onClose }) => {
    return (
        <div className={styles.container}>
            <div className={styles.header}>
                {application.avatarUrl ? (
                    <img 
                        src={application.avatarUrl} 
                        alt={application.candidate} 
                        className={styles.avatar}
                    />
                ) : (
                    <div className={styles.avatarFallback}>
                        {application.candidate.substring(0, 2).toUpperCase()}
                    </div>
                )}
                <div>
                    <h3 className={styles.candidateName}>{application.candidate}</h3>
                    <p className={styles.email}>{application.email}</p>
                </div>
            </div>

            <hr className={styles.separator} />

            <div className={styles.details}>
                <div>
                    <strong>Match Score :</strong>
                    <p className={styles.detailValue}>{application.matchScore}%</p>
                </div>
                <div>
                    <strong>Date de postulation :</strong>
                    <p className={styles.detailValue}>{application.appliedAt}</p>
                </div>
                <div>
                    <strong>Statut :</strong>
                    <p className={styles.detailValue}>{application.status}</p>
                </div>
            </div>

            <div  className={styles.actions}>
                <button type="button" onClick={onClose} className={styles.closeButton}>
                    Fermer
                </button>
            </div>
        </div>
    );
};