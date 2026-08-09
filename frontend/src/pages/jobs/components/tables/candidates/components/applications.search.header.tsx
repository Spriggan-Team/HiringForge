import React from 'react';

import SearchSVGComponent from "/src/assets/svg/menu/search-svgrepo-com.svg"
import BasicInput from '../../../../../../layout/components/form/input/basic.input';

import styles from '../ApplicationsTable.module.css';
import type { TFunction } from 'i18next';

interface ApplicationSearchHeaderProps {
  search: string;
  t: TFunction;
  onSearchChange: (value: string) => void;
}


// Sub-component dedicated to handling the search input
export const ApplicationSearchHeader: React.FC<ApplicationSearchHeaderProps> = ({
  t,
  search,
  onSearchChange,
}) => {
  return (
    <div>
      <BasicInput
        value={search}
        placeholder={t('applications.inputs.searchForCandidates')}
        backgroundColor="white"
        svg={SearchSVGComponent}
        className={`${styles.input} input`}
        onChange={(e) => onSearchChange(e.target.value)}
      />
    </div>
  );
};