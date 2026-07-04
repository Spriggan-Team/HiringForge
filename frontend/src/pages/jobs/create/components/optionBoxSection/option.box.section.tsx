import { useState } from "react";
import { useTranslation } from "react-i18next";

//-- Services
import { useJob } from "../../../../../context/job.context";

//-- Custom components
import BasicInput, { type BasicInputProps } from "../../../../../layout/components/form/input/basic.input";
import Title from "../../../../../layout/components/text/title/title";
import CheckBoxInput from "../../../../../layout/components/form/input/checkbox/checkbox.input";
import DateInput from "../../../../../layout/components/form/input/date/date.input";
import TagList from "../../../../../layout/components/text/tag.list";
import JobSkill from "../../../user/components/skills/job.skill";

//-- SVG Components
import DateSVGComponent from "/src/assets/svg/catalog/date-svgrepo-com.svg"

//-- CSS Module
import styles from "./OptionBoxSection.module.css"


interface OptionBoxSectionProps{}


const globalBasicInputInput: BasicInputProps = {
    width: "100%",
    backgroundColor: "#FFFFFF",
    className: `${styles.input} card-border`,
}


type VisibilitySetingsProps = "public" | "private";
type PublicationSettingsType = "draft" | "published" | "closed";
type ExpertiseLevelType = "junior" | "senior" //-- level of experience 


const OptionBoxSection: React.FC<OptionBoxSectionProps> = ({}) => {
    const { t } = useTranslation();
    const { currentJob } = useJob();

    const [language, setLanguage] = useState<string | null>(null);
    const [publicationDate, setPublicationDate] = useState<Date | null>();
    const [expertise, setExpertise] = useState<ExpertiseLevelType | null>(null);
    const [visibilityState, setVisibilityState] = useState<VisibilitySetingsProps>("public");
    const [publicationSettings, setPublicationSettings] = useState<PublicationSettingsType>("draft");

    return (
        <div className={styles.container}>
            <div className={`${styles.card} card`}>
                <Title title={t("jobs.createJob.publicationSettingsSection.title")} />
                
                {/** PUBLICATION STATE */}
                <div className={styles.contentBox}>
                    <span className={styles.title}>{t("global.jobs.publicationState.title")}</span>
                    <div className={styles.checkboxSection}>
                        <CheckBoxInput
                            borderRadius="100%"
                            checked={publicationSettings == "published"}
                            text={t("global.jobs.publicationState.publish")}
                            onChange={()=> ( setPublicationSettings("published") )}
                        />
                        <CheckBoxInput
                            borderRadius="100%"
                            checked={publicationSettings === "draft"}
                            text={t("global.jobs.publicationState.draft")}
                            onChange={()=> ( setPublicationSettings("draft") )}
                        />
                        <CheckBoxInput
                            borderRadius="100%"
                            checked={publicationSettings === "closed"}
                            text={t("global.jobs.publicationState.closed")}
                            onChange={()=> ( setPublicationSettings("closed") )}
                        />
                    </div>
                </div>

                {/** VISIBILITY STATE */}
                <div className={styles.contentBox}>
                    <span className={styles.title}>{t("global.jobs.visibilityStatus.title")}</span>
                    <div className={styles.checkboxSection}>
                        <CheckBoxInput
                            borderRadius="100%"
                            checked={visibilityState === "public"}
                            text={t("global.jobs.visibilityStatus.publish.title")}
                            desc={t("global.jobs.visibilityStatus.publish.desc")}
                            onChange={()=> ( setVisibilityState("public") )}
                        />

                        <CheckBoxInput
                            borderRadius="100%"
                            checked={visibilityState === "private"}
                            text={t("global.jobs.visibilityStatus.private.title")}
                            desc={t("global.jobs.visibilityStatus.private.desc")}
                            onChange={()=> ( setVisibilityState("private") )}
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
                            setPublicationDate(date)
                        }
                        defaultContent={t("global.dates.inputs.selectCalandarDate.placeholder")}
                    />
                </div>
            </div>

            {/** Additionnal Options */}
            <div className={`${styles.card} card`}>
                <Title title=""/>
                <div className={styles.contentBox}>
                    <Title title={t("jobs.createJob.additionnalOpstions.title")} />
                    <div className={styles.inputs}>
                        <BasicInput
                            {...globalBasicInputInput}
                            label={t("jobs.createJob.additionnalOpstions.inputs.expertiseLevel.label")}
                            placeholder={t("jobs.createJob.additionnalOpstions.inputs.expertiseLevel.placeholder")}
                        />
                        <BasicInput
                            {...globalBasicInputInput}
                            label={t("jobs.createJob.additionnalOpstions.inputs.requireLanguage.label")}
                            placeholder={t("jobs.createJob.additionnalOpstions.inputs.requireLanguage.placeholder")}
                        />
                    </div>
                </div>
            </div>
            
            {/**  Overview */}
            <div className={`${styles.card} ${styles.overview} card`}>
                <Title title={t("jobs.createJob.overview.title")} />

                <div>
                    <Title title={currentJob.title ?? ""} />
                    <div className={styles.addressTxt}>
                        <div className={styles.tags}>
                            <TagList
                                tags={[
                                    [
                                        currentJob.location?.street,
                                        currentJob.location?.city,
                                        currentJob.location?.country,
                                    ]
                                        .filter(Boolean)
                                        .join(""),

                                    currentJob?.contract,
                                ].filter(Boolean) as string[]}
                            />
                        </div>
                        <div className={styles.pill}>
                            {
                                (currentJob.salary && (currentJob.salary?.min != 0 || currentJob.salary.max != 0)) && (
                                    <JobSkill 
                                        content={
                                            `${t("global.text.fork")}: ${[
                                                        currentJob.salary.min ? currentJob.salary.min + (currentJob.salary.devise ?? "") : undefined  ,
                                                        currentJob.salary.max ? currentJob.salary.max + (currentJob.salary.devise ?? "") : undefined 
                                                    ].filter(Boolean)
                                                    .join(" - ")
                                                }
                                            `
                                        }
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
        </div>
    );
}
 
export default OptionBoxSection;