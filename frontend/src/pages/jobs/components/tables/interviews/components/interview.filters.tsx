import type React from "react";

import { InterviewStatus, type InterviewStatusValue } from "../../../../../../features/interviews/interviews";
import { FilterDropdown } from "../../../../../../layout/components/menu/dropdown/filter.dropdawn";
import { useState } from "react";


interface InterviewFiltersProps
{
    onFilterValueChange: (param: {statuses: InterviewStatusValue[]})=> void
}


const InterviewFilters: React.FC<InterviewFiltersProps>  = ({
    onFilterValueChange
}) => {
    //-- handle filters
    const handleFilterChange = (newValues: string[]) => {
        setSelectedStatuses(newValues);
        onFilterValueChange({ statuses: newValues as InterviewStatus[] })
    };

    //- Status
    const [selectedStatuses, setSelectedStatuses] = useState<string[]>([]);

    return (
        <FilterDropdown 
          label="status"
          options={[
            { label: "Planifié", value: InterviewStatus.SCHEDULED},
            { label: "Fermé", value: InterviewStatus.CLOSED},
            { label: "Terminé", value: InterviewStatus.COMPLETED},
          ]}
          placement='bottom-right'
          selectedValues={selectedStatuses}
          onChange={(values)=> handleFilterChange(values)}
        />
    );
}
 
export default InterviewFilters;