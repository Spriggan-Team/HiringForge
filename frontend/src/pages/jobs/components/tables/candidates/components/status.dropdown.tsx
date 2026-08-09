

import React, { useState, useRef, useEffect } from 'react';
import { ALLOWED_STATUS_TRANSITIONS, JobApplicationStatus, type ApplicationStatusValue } from "../../../../../../features/application/application";
import { useTranslation } from 'react-i18next';

import styles from './StatusDropdown.module.css';


interface StatusDropdownProps {
    currentStatus: ApplicationStatusValue;
    onStatusChange: (newStatus: ApplicationStatusValue) => void;
    disabled?: boolean;
}

// Statuses that the recruiter CANNOT select manually
const NON_MANUAL_STATUSES: ApplicationStatusValue[] = [
    JobApplicationStatus.HIRED,
    JobApplicationStatus.INTERVIEW_SCHEDULED, 
    JobApplicationStatus.IN_INTERVIEW,
    JobApplicationStatus.APPLIED,
    JobApplicationStatus.WITHDRAWN,
    JobApplicationStatus.REJECTED,
    JobApplicationStatus.OFFER_DECLINED,
    JobApplicationStatus.OFFER_PENDING,
    JobApplicationStatus.OFFER_ACCEPTED,
    JobApplicationStatus.APPLIED,
    JobApplicationStatus.RECEIVED,
];


interface StatusDropdownProps {
    currentStatus: ApplicationStatusValue;
    onStatusChange: (newStatus: ApplicationStatusValue) => void;
    disabled?: boolean;
}

export const StatusDropdown: React.FC<StatusDropdownProps> = ({
    currentStatus,
    onStatusChange,
    disabled = false,
}) => {
    const { t } = useTranslation();

    const [isOpen, setIsOpen] = useState(false);
    const containerRef = useRef<HTMLDivElement>(null);

    // Filter job status
    const allowedTransitions = (ALLOWED_STATUS_TRANSITIONS[currentStatus] || []).filter(
        (status) => !NON_MANUAL_STATUSES.includes(status)
    );

    const availableOptions: ApplicationStatusValue[] = [
        currentStatus,
        ...allowedTransitions,
    ];

    // Disables the dropdown if no manual transition is possible
    const isTerminal = allowedTransitions.length === 0;

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const handleSelect = (newStatus: ApplicationStatusValue) => {
        setIsOpen(false);
        if (newStatus !== currentStatus) {
            onStatusChange(newStatus);
        }
    };

    return (
        <div className={styles.dropdownContainer} ref={containerRef}>
            <button
                type="button"
                className={`${styles.dropdownTrigger} ${styles[`status_${currentStatus}`]}`}
                onClick={() => !disabled && !isTerminal && setIsOpen(!isOpen)}
                disabled={disabled || isTerminal}
            >
                <span className={styles.statusDot} />
                <span className={styles.statusLabel}>{currentStatus}</span>
                {!isTerminal && (
                    <svg
                        className={`${styles.chevron} ${isOpen ? styles.chevronOpen : ''}`}
                        width="12"
                        height="12"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2.5"
                    >
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                )}
            </button>

            {isOpen && (
                <div className={styles.dropdownMenu}>
                    <div className={styles.dropdownHeader}>{t('applications.update.changeStatus')} :</div>
                    {availableOptions.map((option) => {
                        const isCurrent = option === currentStatus;
                        return (
                            <button
                                key={option}
                                type="button"
                                className={`${styles.dropdownItem} ${isCurrent ? styles.activeItem : ''}`}
                                onClick={() => handleSelect(option)}
                            >
                                <span className={`${styles.statusDot} ${styles[`dot_${option}`]}`} />
                                {option}
                                {isCurrent && <span className={styles.currentBadge}>(actuel)</span>}
                            </button>
                        );
                    })}
                </div>
            )}
        </div>
    );
};