import React, { useState } from 'react';
import type { TFunction } from 'i18next';



import SearchSVGComponent from "/src/assets/svg/menu/search-svgrepo-com.svg?react"
import BasicInput from '../../../../../../layout/components/form/input/basic.input';

import { FilterDropdown } from '../../../../../../layout/components/menu/dropdown/filter.dropdawn';
import { JobApplicationStatus, type ApplicationStatusValue } from '../../../../../../features/application/application';

import styles from '../ApplicationsTable.module.css';
import localStyles from './ApplicationSearchHeader.module.css'



interface ApplicationSearchHeaderProps {
  search: string;
  t: TFunction;
  onSearchChange: (value: string) => void;
  onFilterValueChange: (value: { status: ApplicationStatusValue[] }) => void;
}


const STATUS_OPTIONS = [
  { label: 'Candidat envoyé', value: JobApplicationStatus.APPLIED },
  { label: 'Entretien', value: JobApplicationStatus.IN_INTERVIEW },
  { label: 'Accepté', value: JobApplicationStatus.HIRED },
  { label: 'Refusé', value: JobApplicationStatus.REJECTED },
];


// Sub-component dedicated to handling the search input
export const ApplicationSearchHeader: React.FC<ApplicationSearchHeaderProps> = ({
  t,
  search,
  onSearchChange,
  onFilterValueChange
}) => {
    //-- handle filters
  const handleFilterChange = (newValues: string[]) => {
    setSelectedStatuses(newValues);
    onFilterValueChange({status: newValues as ApplicationStatusValue[] })
  };

  //- Status
  const [selectedStatuses, setSelectedStatuses] = useState<string[]>([]);
  
  return (
    <div className={localStyles.applicationHeader}>
      <BasicInput
        value={search}
        placeholder={t('applications.inputs.searchForCandidates')}
        backgroundColor="white"
        svg={SearchSVGComponent}
        className={`${styles.input} input`}
        onChange={(e) => onSearchChange(e.target.value)}
      />
      <div>
        <div >
          <FilterDropdown
            label="Statuts"
            options={STATUS_OPTIONS}
            selectedValues={selectedStatuses}
            onChange={handleFilterChange}
            placement='bottom-right'
            multiple={true}
          />
        </div>
      </div>
    </div>
  );
};