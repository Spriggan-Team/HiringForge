import React from 'react';
import type { TFunction } from 'i18next';


import MatchScoreCircle from '../../../../../../layout/components/progress/circle/match.circle';
import type { Application, ApplicationStatusValue } from '../../../../../../features/application/application';


import { StatusDropdown } from './status.dropdown';
import { EyeIcon } from '../../../../../../layout/components/icons/eye.icon';

import styles from '../ApplicationsTable.module.css';


interface ApplicationRowProps {
  application: Application;
  isUpdating: boolean;
  isEyeOpen: boolean;
  t: TFunction;
  imageUrl?: string;
  onToggleEye: (application: Application) => void;
  onRequestStatusChange: (
    applicationId: string,
    candidateName: string,
    currentStatus: ApplicationStatusValue,
    newStatus: ApplicationStatusValue
  ) => void;
  onReject: (id: string) => void;
  getInitials: (name: string) => string;
}


// Sub-component responsible for rendering an individual table row
export const ApplicationRow: React.FC<ApplicationRowProps> = ({
  t,
  application,
  isUpdating,
  isEyeOpen,
  imageUrl,
  onToggleEye,
  onRequestStatusChange,
  onReject,
  getInitials,
}) => {
  return (
    <tr className={isUpdating ? styles.rowDisabled : ''}>
      {/* Candidate */}
      <td data-label="Candidat">
        <div className={styles.candidateCell}>
          {imageUrl  ? (
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
          <span className={styles.candidateName}>{application.candidate}</span>
        </div>
      </td>

      {/* Email */}
      <td data-label="Email">
        <span className={styles.emailText}>{application.email}</span>
      </td>

      {/* Match Score */}
      <td data-label="Score">
        <MatchScoreCircle score={application.matchScore} />
      </td>

      {/* Applied Date */}
      <td data-label="Date de postulation">
        {new Date(application.appliedAt).toLocaleDateString('fr-FR', {
          day: 'numeric',
          month: 'short',
          year: 'numeric',
        })}
      </td>

      {/* Status */}
      <td data-label="Statut">
        <StatusDropdown
          currentStatus={application.status}
          onStatusChange={(newStatus) =>
            onRequestStatusChange(
              application.id,
              application.candidate,
              application.status,
              newStatus
            )
          }
          disabled={isUpdating}
        />
      </td>

      {/* Actions */}
      <td data-label="Actions" className={styles.actionsCell}>
        <div className={styles.actionGroup}>
          <button
            type="button"
            onClick={() => onToggleEye(application)}
            className={styles.btnSecondary}
            title={isEyeOpen ? 'Masquer les détails' : 'Voir les détails'}
            disabled={isUpdating}
          >
            <EyeIcon isOpen={isEyeOpen} />
          </button>
          <button
            type="button"
            onClick={() => onReject(application.id)}
            className={styles.btnDanger}
            title="Supprimer"
            disabled={isUpdating}
          >
            {t('global.actions.reject')}
          </button>
        </div>
      </td>
    </tr>
  );
};