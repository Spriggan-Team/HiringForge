import React from "react";

import styles from "../Interviews.module.css";
import type { Interview } from "../interviews.section";
import { InterviewStatus, InterviewType } from "../../../../../../features/interviews/interviews";
import { formatDateTimeSafely, safeParsingDate } from "../../../../../../utils/format";


interface InterviewRowProps {
  interview: Interview;
  isUpdating: boolean;
  onCancel: (id: string) => void;
  getInitials: (name: string) => string;
  onDelete: (id: string)=>void;
}



const now = new Date();

const InterviewRow: React.FC<InterviewRowProps> = React.memo(({
  interview,
  isUpdating,
  onCancel,
  onDelete,
  getInitials,
}) => {
  console.log("Interview", interview)
  const startDate = safeParsingDate(interview.scheduledAt);
  const endDate = startDate
    ? new Date(startDate.getTime() + interview.minutes * 60 * 1000)
    : null;

  return (
    <tr className={isUpdating ? styles.rowDisabled : ""}>
      {/* Candidat */}
      <td data-label="Candidat">
        <div className={styles.candidateCell}>
          {interview.avatarUrl ? (
            <img
              src={interview.avatarUrl}
              alt={interview.candidate}
              className={styles.avatar}
            />
          ) : (
            <div className={styles.avatarFallback}>
              {getInitials(interview.candidate)}
            </div>
          )}
          <div>
            <span className={styles.candidateName}>{interview.candidate}</span>
            <span className={styles.emailText}>{interview.email}</span>
          </div>
        </div>
      </td>

      {/* Offre */}
      <td data-label="Offre d'emploi">
        <span className={styles.jobTitle}>{interview.job?.jobTitle}</span>
      </td>

      {/* Date & Heure */}
      <td data-label="Date & Heure">
        <div className={styles.dateCell}>
          <span className={styles.dateText}>
            {
              startDate
                ? startDate.toLocaleDateString("fr-FR", {
                    day: "numeric",
                    month: "short",
                    year: "numeric",
                  })
                : "Date invalide"
            }
          </span>
          <span className={styles.timeText}>
            {
              startDate &&
                startDate.toLocaleTimeString("fr-FR", {
                  hour: "2-digit",
                  minute: "2-digit",
                })
            }
          </span>
        </div>
      </td>

      {/* Lieu / Lien */}
      <td data-label="Lieu / Lien">
        {interview.locationOrLink ? (
          interview.locationOrLink.startsWith("http") ? (
            <a
              href={interview.locationOrLink}
              target="_blank"
              rel="noopener noreferrer"
              className={styles.linkText}
            >
              Rejoindre la visio 🔗
            </a>
          ) : (
            <span className={styles.locationText}>
              📍 {interview.locationOrLink}
            </span>
          )
        ) : (
          <span className={styles.mutedText}>Non spécifié</span>
        )}
      </td>

      {/* Statut */}
      <td data-label="Statut">
        <span className={`${styles.status} ${styles[`status${interview.status}`]}`}>
          {interview.status as string}
        </span>
      </td>

        
      {/* Actions */}
      <td data-label="Actions" className={styles.actionsCell}>
        <div className={styles.actionGroup}>
          {
            interview.candidateApproval != null ?  
              // manually closed
                (
                      <button
                        type="button"
                        onClick={() => onCancel(interview.id)}
                        className={styles.btnDanger}
                        title="Annuler l'entretien"
                        disabled={isUpdating}
                      >
                        Cancel
                      </button>
                )
              :   endDate && now >= endDate ? (
                    <span>Aucune actions</span>
                  ) : (
                    <button
                      type="button"
                      onClick={() => onDelete(interview.id)}
                      className={styles.btnDanger}
                      title="Annuler l'entretien"
                      disabled={isUpdating}
                    >
                      Delete
                    </button>
                  )
          }
        </div>
      </td>
    </tr>
  );
});

InterviewRow.displayName = "InterviewRow";

export default InterviewRow;