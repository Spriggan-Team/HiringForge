import { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useTranslation } from "react-i18next";

//-- Services
import RouteScheme from "../../../../../route.scheme";
import { formatSalary } from "../../../../../utils/format";
import { INITIAL_JOB_VIEW, type JobView } from "../../../../../features/jobs/JobOffer";
import { navigateTo } from "../../../../../App";
import { jobStatusStyles } from "../../../../../context/styles";
import LanguageQueries from "../../../../../api/services/Language/queries";
import SkillServices from "../../../../../api/services/shared/skill.service";
import { useUserJobContext } from "../../../../../context/user.job.context";

//-- Custom components
import Title from "../../../../../layout/components/text/title/title";
import CheckBoxInput from "../../../../../layout/components/form/input/checkbox/checkbox.input";
import DateInput from "../../../../../layout/components/form/input/date/date.input";
import TagList from "../../../../../layout/components/text/tag.list";
import JobSkill from "../../../components/skills/job.skill";
import ToggleSwitch from "../../../../../layout/components/switch/toggle.switch";
import InputLabel from "../../../../../layout/components/form/input/input.label";
import LanguageSelectionWorkflow from "../../../../../layout/components/selectors/language/language.selection.workflow.";
import SimpleButton from "../../../../../layout/components/buttons/simple/simple.button";
import  { DrawerBuilder,  } from "../../../../../layout/components/menu/dropdown/menu.dropdown";
import type { JobFormProps } from "../job.form.page";


//-- SVG Components
import DateSVGComponent from "/src/assets/svg/catalog/date-svgrepo-com.svg?react"


//-- CSS Module
import styles from "./OptionBoxSection.module.css"



interface OptionBoxSectionProps{
    onClose?: ()=>void;
    onComplete?: (job: JobView)=>void;
    className?: string;
    formType: JobFormProps['formType'];
}



const OptionBoxSection: React.FC<OptionBoxSectionProps> = ({
    onClose,
    onComplete,
    className,
    formType
}) => {
    const { t }      = useTranslation();
    const navigate   = useNavigate();
    const { editingJob, setEditingJob } = useUserJobContext();

    const [languageCodes,   setLanguageCodes]   = useState<{id: number; code: string}[]>([]);
    const [expertiseValues, setExpertiseValues]  = useState<string[]>([]);
    const [pubStatusColor,  setPubStatusColor]   = useState<string>("");

    const displayNames = useMemo(
        () => new Intl.DisplayNames(["en"], { type: "language" }),
        [],
    );

    // -- Static data init --
    useEffect(() => {
        const init = async () => {
            try {
                const [codes, expertises] = await Promise.all([
                    LanguageQueries.getLanguages().then(data => data.map(l => ({ id: l.id, code:  l.code}))),
                    SkillServices.getExpertiseCollection(),
                ]);
                setLanguageCodes(codes);
                setExpertiseValues(expertises);
            } catch (error) {
                console.warn("OptionBoxSection init error:", error);
            }
        };
        init();
    }, []); 


    // -- Publication status color --
    useEffect(() => {
        const color = getComputedStyle(document.documentElement)
            .getPropertyValue(jobStatusStyles[editingJob?.publicationStatus ?? "draft"].txtColor);
        setPubStatusColor(color);
    }, [editingJob?.publicationStatus]);


    const salaryLabel = useMemo(() => formatSalary(editingJob?.salary), [editingJob?.salary]);


    const hasSalary = useMemo(() => {
        const { min = 0, max = 0 } = editingJob?.salary ?? {};
        return min !== 0 || max !== 0;
    }, [editingJob?.salary]);


    return (
        <div className={`${styles.container} ${className ?? ""}`}>

            {/*  Publication settings  */}
            <div className={`${styles.card} card`}>
                <Title title={t("jobs.createJob.publicationSettingsSection.title")} />

                {/* Publication state */}
                <div 
                    className={styles.contentBox}
                >
                    <span className={styles.title}>{t("global.jobs.publicationState.title")}</span>
                    <div className={styles.checkboxSection}>
                        {(["published", "draft", "closed"] as const).map(status =>{
                            if(formType === "create" && status === "closed" ){
                                return null;
                            }

                            // A candidate has already postulate
                            if(formType == "modify" && status == "draft" && editingJob.cardinal.candidates > 0)
                            { 
                                return null;
                            }

                            return (
                                <CheckBoxInput
                                    key={status}
                                    borderRadius="100%"
                                    checked={editingJob?.publicationStatus === status}
                                    text={t(`global.jobs.publicationState.${status}`)}
                                    onChange={() =>
                                        setEditingJob(prev => {
                                            if(!prev) return prev;
                                            return  ({ ...prev, publicationStatus: status });
                                        })
                                    }
                                />
                            )
                        })}
                    </div>
                </div>

                {/* Visibility state */}
                <div 
                    className={styles.contentBox}
                >
                    <span className={styles.title}>{t("global.jobs.visibilityStatus.title")}</span>
                    <div className={styles.checkboxSection}>
                        {(["public", "private"] as const).map(vis => (
                            <CheckBoxInput
                                key={vis}
                                borderRadius="100%"
                                checked={editingJob?.visibilityStatus === vis}
                                text={t(`global.jobs.visibilityStatus.${vis}.title`)}
                                desc={t(`global.jobs.visibilityStatus.${vis}.desc`)}
                                onChange={() =>
                                    setEditingJob(prev => {
                                        if(!prev) return prev;
                                        return ({ ...prev, visibilityStatus: vis })
                                    })
                                }
                            />
                        ))}
                    </div>
                </div>

                {/* Publication date */}
                <div className={styles.contentBox}>
                    <span className={styles.title}>
                        {t("jobs.createJob.publicationSettingsSection.inputs.publicationDate.title")}
                    </span>
                    <DateInput
                        leading={DateSVGComponent}
                        defaultContent={t("global.dates.inputs.selectCalandarDate.placeholder")}
                        onSelectedDate={date =>
                            setEditingJob(prev => {
                                if(!prev) return prev;
                                return ({ ...prev, publicationDate: date ?? null });
                            })
                        }
                    />
                </div>
            </div>

            {/* -- Additional options -- */}
            <div className={`${styles.card} card`}>
                <div className={styles.contentBox}>
                    <Title title={t("jobs.createJob.additionnalOpstions.title")} />
                    <div className={styles.inputs}>

                        {/* Expertise level */}
                        <div className={styles.drawer}>
                            <InputLabel label={t("jobs.createJob.additionnalOpstions.inputs.expertiseLevel.label")} />
                            <DrawerBuilder
                                value={editingJob?.expertise}
                                placeholder={t("jobs.createJob.additionnalOpstions.inputs.expertiseLevel.placeholder")}
                                items={expertiseValues.map((v, key) => ({ key, value: v, label: v }))}
                                onChange={value => {
                                    if (value)
                                        setEditingJob(prev => {
                                            if(!prev) return prev;
                                            return ({ ...prev, expertise: value as string });
                                        });
                                }}
                            />
                        </div>

                        {/* Language requirement */}
                        <div className={styles.languageDrawer}>
                            <InputLabel label={t("jobs.createJob.additionnalOpstions.inputs.requireLanguage.label")} />
                            <LanguageSelectionWorkflow
                                languages={languageCodes}
                                placeholder={t("jobs.createJob.additionnalOpstions.inputs.requireLanguage.placeholder")}
                                onAdd={language => {
                                    setEditingJob(prev => {
                                        if(!prev) return prev;
                                        return ({
                                            ...prev,
                                            requireLanguages: prev.requireLanguages.some(l => l.code === language.code)
                                                ? prev.requireLanguages
                                                : [...prev.requireLanguages, language],
                                        })
                                    });
                                }}
                            />
                            {editingJob && editingJob.requireLanguages.length > 0 && (
                                <div className={styles.result}>
                                    {editingJob.requireLanguages.map(lang => (
                                        <JobSkill
                                            key={lang.code}
                                            content={`${displayNames.of(lang.code)} · ${lang.proficiencyLevel}`}
                                            onClose={() =>
                                                setEditingJob(prev => {
                                                    if(!prev) return prev;
                                                    return  ({
                                                        ...prev,
                                                        requireLanguages: prev.requireLanguages.filter(l => l !== lang),
                                                    })
                                                })
                                            }
                                        />
                                    ))}
                                </div>
                            )}
                        </div>

                        {/* Work mode */}
                        <ToggleSwitch
                            text={t("jobs.createJob.additionnalOpstions.switchs.jobWorkMode")}
                            onChange={value =>
                                setEditingJob(prev => {
                                    if(!prev) return prev;
                                    return ({
                                        ...prev,
                                        jobWorkMode: value ? "remote" : "onsite",
                                    })
                                })
                            }
                        />
                    </div>
                </div>
            </div>

            {/* -- Overview -- */}
            <div className={`${styles.card} ${styles.overview} card`}>
                <Title title={t("jobs.createJob.overview.title")} />
                <div className={styles.overviewContent}>
                    <Title title={editingJob?.title ?? ""} />
                    <div className={styles.details}>
                        <div className={styles.tags}>
                            <TagList
                                tags={[
                                    [
                                        editingJob?.location?.street,
                                        editingJob?.location?.city,
                                        editingJob?.location?.country,
                                    ]
                                        .filter(Boolean)
                                        .join(", "),
                                    editingJob?.contract?.label,
                                ].filter(Boolean) as string[]}
                            />
                            {editingJob && editingJob.publicationStatus && (
                                <div
                                    style={{ ["--pubColor" as string]: pubStatusColor }}
                                    className={styles.pubStatus}
                                >
                                    {t(`global.jobs.publicationState.${editingJob.publicationStatus}`)}
                                </div>
                            )}
                        </div>
                        <div className={styles.pill}>
                            {hasSalary && (
                                <JobSkill content={`${t("global.text.fork")}: ${salaryLabel}`} />
                            )}
                            {editingJob && editingJob.skills.map(item => (
                                <JobSkill key={item.id} content={item.name} />
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {/* -- Action buttons -- */}
            <div className={styles.buttons}>
                <SimpleButton
                    text={t("jobs.buttons.cancel")}
                    onClick={() => {
                        setEditingJob(INITIAL_JOB_VIEW);
                        onClose?.();
                        navigateTo(navigate, RouteScheme.userJobs);
                    }}
                />
                <SimpleButton
                    className={styles.complete}
                    onClick={() => onComplete?.(editingJob)}
                    text={t(formType === "create" ? "jobs.buttons.create" : "jobs.buttons.modify")}
                />
            </div>
        </div>
    );
};

 
export default OptionBoxSection;