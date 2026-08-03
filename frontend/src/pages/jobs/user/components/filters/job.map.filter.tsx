
import { useTranslation } from "react-i18next";

//-- Custom Component
import Title from "../../../../../layout/components/text/title/title";
import CheckBoxInput from "../../../../../layout/components/form/input/checkbox/checkbox.input";
import SliderInput from "../../../../../layout/components/form/input/slider/slider.input";
import CustomMapContainer from "../../../../../layout/components/cards/map/Map";

//-- CSS Styles
import styles from "./JobMapFilters.module.css"
import type { FilterState, JobOfferState, JobPublishedState } from "../../../../../features/shared/global";



interface JobFilterWithMapProps {
    filters: FilterState;
    onFilterChange: (filters: FilterState) => void;
    
    // Callbacks optionnels
    onPublishStateSelected?: (value: JobPublishedState) => void;
    onOfferStateSelected?: (value: JobOfferState) => void;
    onCandidateCountChange?: (count: number) => void;
    onSalaryChange?: (value: number) => void;
}

const JobMapFilters: React.FC<JobFilterWithMapProps> = ({
    filters,
    onFilterChange,
    onPublishStateSelected,
    onOfferStateSelected,
    onCandidateCountChange,
    onSalaryChange,
}) => {
    const { t } = useTranslation();

    //  Centralized Update Handlers
    const handlePublishedChange = (key: keyof JobPublishedState, value: boolean) => {
        const newPublishedState = {
            ...filters.publishedState,
            [key]: value,
        };

        onFilterChange({
            ...filters,
            publishedState: newPublishedState,
        });

        onPublishStateSelected?.(newPublishedState);
    };


    const handleOfferStatusChange = (key: keyof JobOfferState, value: boolean) => {
        const newOfferState = {
            ...filters.offerState,
            [key]: value,
        };

        onFilterChange({
            ...filters,
            offerState: newOfferState,
        });

        onOfferStateSelected?.(newOfferState);
    };


    const handleSalaryChange = (value: number) => {
        onFilterChange({
            ...filters,
            salary: value,
        });

        onSalaryChange?.(value);
    };


    const handleCandidateCountChange = (value: number) => {
        onFilterChange({
            ...filters,
            candidateCount: value,
        });

        onCandidateCountChange?.(value);
    };


    return (
        <div className={styles.container}>
            {/** -- Job Filter -- */}
            <div className={`${styles.filterBox} card`}>
                <div className={styles.filter}>
                    <Title title={t("global.filters.title")} />
                </div>

                <div className={styles.content}>
                    {/* Statut de Publication */}
                    <fieldset className={styles.fieldset}>
                        <legend>{t("global.jobs.publicationState.title")}</legend>
                        <div className={styles.checkbox}>
                            <CheckBoxInput
                                checked={filters.publishedState.closed}
                                text={t("global.jobs.publicationState.closed")}
                                onChange={(val) => handlePublishedChange('closed', val)}
                            />
                            <CheckBoxInput
                                checked={filters.publishedState.published}
                                text={t("global.jobs.publicationState.published")}
                                onChange={(val) => handlePublishedChange('published', val)}
                            />
                            <CheckBoxInput
                                checked={filters.publishedState.draft}
                                text={t("global.jobs.publicationState.draft")}
                                onChange={(val) => handlePublishedChange('draft', val)}
                            />
                        </div>
                    </fieldset>

                    {/* Statut de l'Offre */}
                    <fieldset className={styles.fieldset}>
                        <legend>{t("global.jobs.offerStatus.title")}</legend>
                        <div className={styles.checkbox}>
                            <CheckBoxInput
                                checked={filters.offerState.active}
                                text={t("global.jobs.offerStatus.active")}
                                onChange={(val) => handleOfferStatusChange('active', val)}
                            />
                            <CheckBoxInput
                                checked={filters.offerState.pending}
                                text={t("global.jobs.offerStatus.pending")}
                                onChange={(val) => handleOfferStatusChange('pending', val)}
                            />
                            <CheckBoxInput
                                checked={filters.offerState.inactive}
                                text={t("global.jobs.offerStatus.inactive")}
                                onChange={(val) => handleOfferStatusChange('inactive', val)}
                            />
                        </div>
                    </fieldset>

                    {/* Sliders */}
                    <div className={styles.sliderSection}>
                        <SliderInput
                            devise=" $"
                            minLabel="1200 $"
                            maxLabel="6000 $"
                            min={1200}
                            max={6000}
                            value={filters.salary}
                            label={t("global.salary.title")}
                            onChange={handleSalaryChange}
                        />
                        <div>
                            <SliderInput
                                min={0}
                                max={264000}
                                value={filters.candidateCount}
                                label={t("global.candidate.candidateLabel_other")}
                                onChange={handleCandidateCountChange}
                            />
                        </div>
                    </div>
                </div>
            </div>

            {/** -- Map -- */}
            <div className={styles.mapContainer}>
                <CustomMapContainer className={styles.map} height="200px" width="100%" />
            </div>
        </div>
    );
};
 
export default JobMapFilters;