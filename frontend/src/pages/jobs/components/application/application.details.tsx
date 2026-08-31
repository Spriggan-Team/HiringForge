

import React from 'react';
import styles from './ApplicationDetailModal.module.css'
import type { Application } from '../../../../features/application/application';
import { InterviewStatus, InterviewType } from '../../../../features/interviews/interviews';



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

            {/** Interviews */}
            {application.interviews && application.interviews.length > 0 && (
                <div className={styles.interviewsContainer}>
                    <h4 className={styles.interviewsTitle}>Entretiens programmés</h4>
                    {application.interviews.map((interview) => {
                        const scheduleAt = new Date(interview.startDate);
                        
                        return (
                            <div 
                                key={interview.id} 
                                className={`${styles.interviewCard} ${getInterviewStatusClassName(interview.status as string)}`}
                            >
                                <span className={styles.title}>
                                    {getInterviewsTag(interview.type)}
                                </span>
                                <span className={styles.date}>
                                    Programmé le : {scheduleAt.toLocaleString('fr-FR', {
                                        day: 'numeric',
                                        month: 'short',
                                        year: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    })}
                                </span>
                                <span className={styles.duration}>
                                    Durée : {interview.minutes} min
                                </span>
                            </div>
                        );
                    })}
                </div>
            )}
            <div  className={styles.actions}>
                <button type="button" onClick={onClose} className={styles.closeButton}>
                    Fermer
                </button>
            </div>
        </div>
    );
};


const getInterviewsTag = (text: string)=>{
    switch(text){
        case InterviewType.RH_INTERVIEWS:
            return "Entretien RH"
        case InterviewType.TECHNICAL_INTERVIEWS:
            return "Entretien technique"
        default:
            return "Entretien"
    }
}


const getInterviewStatusClassName = (status: string)=>{
        switch(status){
            case InterviewStatus.CANCELLED:
                return styles.interviewCancel
            case InterviewStatus.COMPLETED:
                return styles.interviewComplete
            case InterviewStatus.CLOSED:
                return styles.interviewClosed
            case InterviewStatus.MISSED:
                return styles.interviewMissed
            case InterviewStatus.IN_PROGRESS:
                return styles.interviewInprogess
            default:
                return ""
    }
}