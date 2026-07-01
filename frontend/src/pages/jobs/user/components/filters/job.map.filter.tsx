





import { useState } from "react";
import { useTranslation } from "react-i18next";

//-- Custom Component
import Title from "../../../../../layout/components/text/title/title";
import CheckBoxInput from "../../../../../layout/components/form/input/checkbox/checkbox.input";
import SliderInput from "../../../../../layout/components/form/input/slider/slider.input";
import CustomMapContainer from "../../../../../layout/components/cards/map/Map";

//-- CSS Styles
import styles from "./JobMapFilters.module.css"


interface JobFilterWithMapProps {
    onPublishStateSelected?: (value: JobPublishedState) => void;
    onOfferStateSelected?: (value: JobOfferState) => void;

    onCandidateCountChange?: (count: number) => void;
    onSalaryChange?: (value: number) => void;

    onChange?: (value: JobPublishedState | JobOfferState | number) => void;
}

interface JobPublishedState {
    draft: boolean;
    closed: boolean;
    published: boolean;
}

interface JobOfferState {
    active: boolean;
    pending: boolean;
}

const JobMapFilters: React.FC<JobFilterWithMapProps> = ({
    onPublishStateSelected,
    onOfferStateSelected,
    onCandidateCountChange,
    onSalaryChange,
    onChange,
}) => {
    const { t } = useTranslation();
    const [publishedState, setPublishedState] = useState<JobPublishedState>({
        draft: true,
        closed: false,
        published: true,
    })

    const [offerState, setOfferState] = useState<JobOfferState>({
        active: true,
        pending: true,
    })

    const [salaryDistance, setSalaryDistance] = useState(50);
    const [candidateDistance, setCandidateDistance] = useState(50);


    return (
        <div className={styles.container}>
            {/**-- Job Filter --*/}
            <div className={`${styles.filterBox} card`} >
                <div className={styles.filter}>
                    <Title title={t("global.filters.title")}/>
                </div>
                
                <div className={styles.content}>
                    <fieldset className={styles.fieldset}>
                        <legend>{t("global.jobs.publicationState.title")}</legend>
                        <div className={styles.checkbox}>
                            <CheckBoxInput 
                                checked={publishedState.closed}
                                text={t("global.jobs.publicationState.closed")}
                                onChange={(value) => {
                                    const newState = { ...publishedState, closed: value };

                                    setPublishedState(newState);
                                    onPublishStateSelected?.(newState);
                                    onChange?.(newState);
                                }}
                            />
                            <CheckBoxInput
                                checked={publishedState.published}
                                text={t("global.jobs.publicationState.publish")}
                                onChange={(value) => {
                                    const newState = { ...publishedState, draft: value };

                                    setPublishedState(newState);
                                    onPublishStateSelected?.(newState);
                                    onChange?.(newState);
                                }}
                            />
                            <CheckBoxInput 
                                checked={publishedState.draft}
                                text={t("global.jobs.publicationState.draft")}
                                onChange={(value) => {
                                    const newState = { ...publishedState, draft: value };

                                    setPublishedState(newState);
                                    onPublishStateSelected?.(newState);
                                    onChange?.(newState);
                                }}
                            />
                        </div>
                    </fieldset>

                    <fieldset className={styles.fieldset}>
                        <legend>{t("global.jobs.offerStatus.title")}</legend>
                        <div className={styles.checkbox}>
                            <CheckBoxInput
                                checked={offerState.active}
                                text={t("global.jobs.offerStatus.active")}
                                onChange={(value) => {
                                    const newState = { ...offerState, active: value };

                                    setOfferState(newState);
                                    onOfferStateSelected?.(newState);
                                    onChange?.(newState);
                                }}
                            />
                            <CheckBoxInput
                                checked={offerState.pending}
                                text={t("global.jobs.offerStatus.pending")}
                                onChange={(value) => {
                                    const newState = { ...offerState, pending: value };

                                    setOfferState(newState);
                                    onOfferStateSelected?.(newState);
                                    onChange?.(newState);
                                }}
                            />
                        </div>
                    </fieldset>

                    <div className={styles.sliderSection}>
                        <SliderInput
                            devise=" $"
                            minLabel="1200 $"
                            maxLabel="6000 $"
                            min={1200} max={6000}
                            value={salaryDistance}
                            label={t("global.salary.title")}
                            onChange={(value) => {
                                setSalaryDistance(value);
                                onSalaryChange?.(value);
                                onChange?.(value);
                            }}
                        />
                        <div>
                            <SliderInput
                                min={0} max={264000}
                                value={candidateDistance}
                                label={t("global.candidate.candidateLabel_other")}
                                onChange={(value) => {
                                    setCandidateDistance(value);
                                    onCandidateCountChange?.(value);
                                    onChange?.(value);
                                }}
                            />
                        </div>
                    </div>
                </div>

            </div>

            {/**-- Map --*/}
            <div className={styles.mapContainer}>
                <CustomMapContainer className={styles.map} height="200px" width="100%" />
            </div>
        </div>
    );
}
 
export default JobMapFilters;