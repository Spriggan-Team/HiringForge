import React from "react";
import type { TFunction } from "i18next";


import styles from "./InterviewToolbar.module.css";


interface InterviewToolbarProps {
  t: TFunction;
  searchQuery: string;
  onSearchChange: (value: string) => void;
  onOpenGenerateModal: () => void;
  totalCount: number;
}

export const InterviewToolbar: React.FC<InterviewToolbarProps> = ({
  t,
  searchQuery,
  onSearchChange,
  onOpenGenerateModal,
  totalCount,
}) => {
  return (
    <div className={styles.toolbarContainer}>
      <div className={styles.tableHeader}>
        <h2>Entretiens à venir</h2>
        <span className={styles.badgeCount}>{totalCount} au total</span>
      </div>

      <div className={styles.toolbarActions}>
        <input
          type="text"
          placeholder="Rechercher un candidat, un email..."
          value={searchQuery}
          onChange={(e) => onSearchChange(e.target.value)}
          className={styles.searchInput}
        />
        <button
          type="button"
          onClick={onOpenGenerateModal}
          className={styles.btnPrimary}
        >
          + {t('interviews.generate')}
        </button>
      </div>
    </div>
  );
};