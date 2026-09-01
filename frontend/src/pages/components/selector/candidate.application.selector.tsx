
import type {  CandidateSearchItem } from "../../../features/shared/global";
import { SearchAutocomplete } from "../../../layout/components/form/input/autocomplete/search.autocomplete";
import { candidateSearchService } from "../../../services/CandidateSearchService";

import styles from "./CandidateApplicationSelector.module.css"

export interface CandidateApplicationSelectorProps {
  value: CandidateSearchItem | null;
  onChange: (
    candidate: CandidateSearchItem | null
  ) => void;
}

export function CandidateApplicationSelector({
  value,
  onChange,
}: CandidateApplicationSelectorProps) {
  return (
    <div className={styles.container}>
      <SearchAutocomplete<CandidateSearchItem>
        onSearch={(query) =>
          candidateSearchService.search(query)
        }
        onSelect={onChange}
        placeholder="Tapez le nom d'une candidature"
        debounceMs={300}
        maxResults={5}
      />

      {value && (
        <div>
          <small>
            Candidat sélectionné :
          </small>

          <strong>
            {value.label}
          </strong>
        </div>
      )}
    </div>
  );
}