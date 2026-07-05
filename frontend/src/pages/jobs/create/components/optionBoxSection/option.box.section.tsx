import { useEffect, useMemo, useState } from "react";
import { useTranslation } from "react-i18next";
import { useNavigate } from "react-router-dom";

//-- Services
import { jobStatusStyles, useJob } from "../../../../../context/job.context";
import { formatSalary } from "../../../../../utils/format";
import { INITIAL_JOB_VIEW } from "../../../../../features/jobs/JobOffer";
import { navigateTo } from "../../../../../App";
import RouteScheme from "../../../../../route.scheme";

//-- Custom components
import BasicInput, { globalBasicInputInput } from "../../../../../layout/components/form/input/basic.input";
import Title from "../../../../../layout/components/text/title/title";
import CheckBoxInput from "../../../../../layout/components/form/input/checkbox/checkbox.input";
import DateInput from "../../../../../layout/components/form/input/date/date.input";
import TagList from "../../../../../layout/components/text/tag.list";
import JobSkill from "../../../user/components/skills/job.skill";
import ToggleSwitch from "../../../../../layout/components/switch/toggle.switch";
import InputLabel from "../../../../../layout/components/form/input/input.label";
import LanguageSelectionWorkflow from "../../../../../layout/components/selectors/language/language.selection.workflow.";


//-- SVG Components
import DateSVGComponent from "/src/assets/svg/catalog/date-svgrepo-com.svg"

//-- CSS Module
import styles from "./OptionBoxSection.module.css"


interface OptionBoxSectionProps{
    onClose?: ()=>void;
    onComplete?: ()=>void;
    className?: string;
}




const OptionBoxSection: React.FC<OptionBoxSectionProps> = ({
    onClose,
    onComplete,

    className
}) => {
    const { t } = useTranslation();
    const navigate = useNavigate();

    const { currentJob, setCurrentJob } = useJob();

    /** Memo */
    const salaryLabel = useMemo(() => {
        return formatSalary(currentJob.salary);
    }, [currentJob.salary]);


    const hasSalary = useMemo(() => {
        const salary = currentJob.salary;

        return (
            (salary?.min ?? 0) !== 0 ||
            (salary?.max ?? 0) !== 0
        );
    }, [currentJob.salary]);
    

    const displayNames = useMemo(
        () =>
            new Intl.DisplayNames(["en"], {
                type: "language",
            }),
        [],
    );

    const [pubStatusColor, setPubStatusColor ] = useState<string>("");

    
    useEffect(()=>{
        const color = getComputedStyle(document.documentElement)
                .getPropertyValue(jobStatusStyles[currentJob.publicationStatus].txtColor);
        setPubStatusColor(color);
    },[currentJob.publicationStatus]);




    return (
        <div className={`${styles.container} ${className}`}>
            {/** PUBLICATION SETTINGS */}
            <div className={`${styles.card} card`}>
                <Title title={t("jobs.createJob.publicationSettingsSection.title")} />
                
                {/** PUBLICATION STATE */}
                <div className={styles.contentBox}>
                    <span className={styles.title}>{t("global.jobs.publicationState.title")}</span>
                    <div className={styles.checkboxSection}>
                        <CheckBoxInput
                            borderRadius="100%"
                            checked={currentJob.publicationStatus == "published"}
                            text={t("global.jobs.publicationState.published")}
                            onChange={()=> ( setCurrentJob((prev)=>({...prev, publicationStatus: "published"})) )}
                        />
                        <CheckBoxInput
                            borderRadius="100%"
                            checked={currentJob.publicationStatus === "draft"}
                            text={t("global.jobs.publicationState.draft")}
                            onChange={()=> ( setCurrentJob((prev)=>({...prev, publicationStatus: "draft"}))  )}
                        />
                        <CheckBoxInput
                            borderRadius="100%"
                            checked={currentJob.publicationStatus === "closed"}
                            text={t("global.jobs.publicationState.closed")}
                            onChange={()=> ( setCurrentJob((prev)=>({...prev, publicationStatus: "closed"}))  )}
                        />
                    </div>
                </div>


                {/** VISIBILITY STATE */}
                <div className={styles.contentBox}>
                    <span className={styles.title}>{t("global.jobs.visibilityStatus.title")}</span>
                    <div className={styles.checkboxSection}>
                        <CheckBoxInput
                            borderRadius="100%"
                            checked={currentJob.visibilityStatus === "public"}
                            text={t("global.jobs.visibilityStatus.publish.title")}
                            desc={t("global.jobs.visibilityStatus.publish.desc")}
                            onChange={()=> ( setCurrentJob((prev)=>({...prev, visibilityStatus: "public" })) )}
                        />

                        <CheckBoxInput
                            borderRadius="100%"
                            checked={currentJob.visibilityStatus === "private"}
                            text={t("global.jobs.visibilityStatus.private.title")}
                            desc={t("global.jobs.visibilityStatus.private.desc")}
                            onChange={()=> ( setCurrentJob((prev)=>({...prev, visibilityStatus: "private"})) )}
                            />
                    </div>
                </div>


                {/** PUBLICATION DATE */}
                <div className={styles.contentBox}>
                    <span className={styles.title}>
                        {t("jobs.createJob.publicationSettingsSection.inputs.publicationDate.title")}
                    </span>
                    <DateInput
                        leading={DateSVGComponent}
                        onSelectedDate={(date)=>
                            setCurrentJob((prev)=>({...prev, publicationDate: date}))
                        }
                        defaultContent={t("global.dates.inputs.selectCalandarDate.placeholder")}
                    />
                </div>
            </div>


            {/** ADDITIONNAL OPTIONS  */}
            <div className={`${styles.card} card`}>
                <div className={styles.contentBox}>
                    <Title title={t("jobs.createJob.additionnalOpstions.title")} />
                    <div className={styles.inputs}>
                        {/**EXPERTISE LEVEL */}
                        <BasicInput
                            {...globalBasicInputInput}
                            label={t("jobs.createJob.additionnalOpstions.inputs.expertiseLevel.label")}
                            placeholder={t("jobs.createJob.additionnalOpstions.inputs.expertiseLevel.placeholder")}
                            />

                        {/** LANGUAGE DRAWER  */}
                        <div className={styles.languageDrawer}>
                            <InputLabel 
                                label={t("jobs.createJob.additionnalOpstions.inputs.requireLanguage.label")}
                            />
                            <LanguageSelectionWorkflow
                                languages={[
                                    "fr", // Français
                                    "en", // English
                                    "de", // Deutsch
                                    "es", // Español
                                    "it", // Italiano
                                    "pt", // Português
                                    "nl", // Nederlands
                                ]}
                                placeholder={t("jobs.createJob.additionnalOpstions.inputs.requireLanguage.placeholder")}
                                onAdd={(language) => {
                                    setCurrentJob(prev => ({
                                        ...prev,
                                        requireLanguages: prev.requireLanguages.some(
                                            l => l.code === language.code
                                        )
                                            ? prev.requireLanguages
                                            : [...prev.requireLanguages, language],
                                    }));
                                }}
                            />

                            <div className={styles.result}>
                                {currentJob.requireLanguages.map(language => (
                                    <JobSkill
                                        onClose={()=>{
                                            setCurrentJob(prev => ({
                                                ...prev,
                                                requireLanguages: prev.requireLanguages.filter((item)=> item != language),
                                            }));
                                        }}
                                        content={`${displayNames.of(language.code)} · ${language.proficiencyLevel}`}
                                    />
                                ))}
                            </div>
                        </div>

                        {/**  JOB WORK MODE (remote?) */}
                        <ToggleSwitch 
                            onChange={(value)=>(
                                setCurrentJob((prev)=>({...prev, jobWorkMode: value ? "remote" : "onsite" }))
                            )}
                            text={t("jobs.createJob.additionnalOpstions.switchs.jobWorkMode")}
                        />
                    </div>
                </div>
            </div>
            

            {/**  OVERVIEW */}
            <div className={`${styles.card} ${styles.overview} card`}>
                <Title title={t("jobs.createJob.overview.title")} />
                {/**  CONTENT */}
                <div className={styles.content}>
                    <Title title={currentJob.title ?? ""} />

                    {/**  DETAILS */}
                    <div className={styles.details}>
                        <div className={styles.tags}>
                            <TagList
                                tags={[
                                    [
                                        currentJob.location?.street,
                                        currentJob.location?.city,
                                        currentJob.location?.country,
                                    ]
                                        .filter(Boolean)
                                        .join(", "),

                                    currentJob?.contract,
                                ].filter(Boolean) as string[]}
                            />
                            {currentJob.publicationStatus && (
                                <div
                                    style={{
                                        ["--pubColor" as any]: pubStatusColor,
                                    }}
                                    className={styles.pubStatus}
                                >
                                    {t(`global.jobs.publicationState.${currentJob.publicationStatus}`)}
                                </div>
                            )}
                        </div>
                        <div className={styles.pill}>
                            {
                                hasSalary && (
                                    <JobSkill 
                                        content={`${t("global.text.fork")}: ${salaryLabel}`}
                                    />
                                )
                            }
                            {
                                currentJob.skills.map((item, index)=>(
                                    <JobSkill key={index} content={item} />
                                ))
                            }
                        </div>
                    </div>
                </div>
            </div>
            

            {/** CLOSURE BUTTONS */}
            <div className={styles.buttons}>
                <button
                    onClick={()=>{
                        setCurrentJob(INITIAL_JOB_VIEW);
                        if(onClose)
                            onClose();
                        navigateTo(navigate, RouteScheme.userJobs);
                    }}
                    className={styles.close}
                >
                    {t("jobs.buttons.cancel")}
                </button>
                <button
                    onClick={onComplete}
                    className={styles.complete}
                >
                    {t("jobs.buttons.create")}
                </button>
            </div>
        </div>
    );
}
 
export default OptionBoxSection;