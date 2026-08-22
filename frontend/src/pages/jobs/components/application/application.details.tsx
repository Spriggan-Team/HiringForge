

import React from 'react';
import styles from './ApplicationDetailModal.module.css'
import type { Application } from '../../../../features/application/application';



interface ApplicationDetailModalProps {
    imageUrl?: string;
    resumeUrl?: string;
    application: Application;
    onClose: () => void;
}

export const ApplicationDetailModal: React.FC<ApplicationDetailModalProps> = ({ 
    application,
    onClose,
    resumeUrl,
    imageUrl
}) => {
    const getInitials = (name: string) => {
        return name
            .split(' ')
            .map((part) => part[0])
            .join('')
            .toUpperCase()
            .slice(0, 2);
    };


    return (
        <div className={styles.container}>
            <div className={styles.header}>
                {imageUrl ? (
                    <img 
                        src={imageUrl} 
                        alt={application.candidate} 
                        className={styles.avatar}
                    />
                ) : (
                    <div className={styles.avatarFallback}>
                        {getInitials(application.candidate)}
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
                    <p className={styles.detailValue}>
                        {new Date(application.appliedAt).toLocaleDateString('fr-FR', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric',
                        })}
                    </p>
                </div>
                <div>
                    <strong>Statut :</strong>
                    <p className={styles.detailValue}>{application.status}</p>
                </div>
            </div>

            {/** Resume  */}
            <div className={styles.resume}>
                <h4 className={styles.resumeTitle}>
                    Curriculum Vitae
                </h4>

                {resumeUrl ? (
                    <a
                        href={resumeUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        className={styles.resumeButton}
                    >
                        Voir le CV
                    </a>
                ) : (
                    <p className={styles.resumeUnavailable}>
                        Aucun CV disponible.
                    </p>
                )}
            </div>

            <div  className={styles.actions}>
                <button type="button" onClick={onClose} className={styles.closeButton}>
                    Fermer
                </button>
            </div>
        </div>
    );
};