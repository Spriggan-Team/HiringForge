import React from 'react';
import type { TFunction } from 'i18next';

import styles from '../ApplicationsTable.module.css';

interface ApplicationTableHeaderProps {
  totalCount: number;
  t: TFunction;
}


// Sub-component for rendering the card title, badge counter, and table header cells
export const ApplicationTableHeader: React.FC<ApplicationTableHeaderProps> = ({
  totalCount,
  t,
}) => {
  return (
    <>
      <div className={styles.tableHeader}>
        <h2>Candidatures</h2>
        <span className={styles.badgeCount}>{totalCount} total</span>
      </div>
      <thead>
        <tr>
          <th>{t('global.candidate.candidateLabel_one')}</th>
          <th>{t('global.text.email')}</th>
          <th>Score</th>
          <th>{t('global.text.postulationDate')}</th>
          <th>{t('global.text.status')}</th>
          <th className={styles.textRight}>{t('global.text.actions')}</th>
        </tr>
      </thead>
    </>
  );
};