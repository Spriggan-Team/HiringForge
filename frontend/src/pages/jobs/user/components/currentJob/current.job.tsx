
import { useTranslation } from "react-i18next";

//-- Services
import type { JobView } from "../../../../../features/jobs/JobOffer";

//--Custom Components
import QuillRenderer from "../../../../../layout/components/editors/quill/quill.renderer";
import MenuDrawer, { MenuDrawerBody, MenuDrawerItem, MenuDrawerTrigger } from "../../../../../layout/components/menu/drawer/menu.drawer";
import SectionHeader from "../../../../../layout/components/sections/sectionHeader/section.header";
import InfoPill from "../../../../../layout/components/badges/pill/info.pill";

//-- Custom SVG Component
import VerticalOptionsSVGComponent from "/src/assets/svg/menu/options-vertical-svgrepo-com.svg"

//-- Styles CSS
import styles from "./CurrentJob.module.css"
import JobSkill from "../../../components/skills/job.skill";


interface CurrentJobProps{
    job: JobView;
    onClick: (id: string) => void;
}



const CurrentJob: React.FC<CurrentJobProps> = ({
    job,
    onClick
}) => {
    const {t} = useTranslation();

    return (
        <div 
            className={`${styles.container} card`}
        >
            {/* IMAGE */}
            <div className={styles.imageWrapper}>
                <img
                    src={job.mainImage ?? "/jobs/placeholder-job.jpg"}
                    alt="Job cover"
                    className={styles.image}
                />

                <div className={styles.imageOverlay} />
                    <div className={styles.badgeOverlay}>
                    <InfoPill text="Published" indicator />
                </div>
            </div>

            {/* HEADER */}
            <div className={styles.header}>
                <div className={styles.headerTop}>
                    <SectionHeader
                        title="Développeur Frontend React"
                        action={
                            <MenuDrawer>
                                <MenuDrawerTrigger displayArrowDown={false}>
                                {() => (
                                    <VerticalOptionsSVGComponent width={18} height={18} />
                                )}
                                </MenuDrawerTrigger>

                                <MenuDrawerBody>
                                    <MenuDrawerItem value="edit">{t("global.actions.edit")}</MenuDrawerItem>
                                    <MenuDrawerItem value="duplicate">{t("global.actions.duplicate")}</MenuDrawerItem>
                                    <MenuDrawerItem value="delete">{t("global.actions.delete")}</MenuDrawerItem>
                                </MenuDrawerBody>
                            </MenuDrawer>
                        }
                    />
                </div>

                {/* META INFO GRID */}
                <div className={styles.metaGrid}>
                    {
                        job.salary && (
                            <div className={styles.metaItem}>
                                <span className={styles.metaLabel}>{t("global.salary.title")}</span>
                                <span className={styles.metaValue}>
                                    {job.salary?.min && job.salary.max 
                                        ? `${job.salary.devise} ${job.salary.min} - ${job.salary.devise} ${job.salary.max}` 
                                        : job.salary?.min ?
                                            `min: ${job.salary.devise}${job.salary.min}`
                                            : job.salary.max && `max: ${job.salary.devise}${job.salary?.max}`
                                    }
                                </span>
                            </div>
                        )
                    }

                    {job.location && (
                        <div className={styles.metaItem}>
                            <span className={styles.metaLabel}>{t("global.location.title")}</span>
                            <span className={styles.metaValue}>
                                {`${job.location.street ?? ""} ${job.location.city ?? ""} ${job.location.country ?? ""} ${job.jobWorkMode ? `/ ${job.jobWorkMode}` : ""}`}
                            </span>
                        </div>
                    )}

                    {
                        job.contract && (
                            <div className={styles.metaItem}>
                                <span className={styles.metaLabel}>{t("global.contract.title")}</span>
                                <span className={styles.metaValue}>{job.contract}</span>
                            </div>
                        )
                    }
                </div>

                {/* CATEGORIES */}
                <div className={styles.skills}>
                    {job.skills?.map((skill, index) => (
                        <JobSkill key={index} content={skill} />
                    ))}
                </div>
            </div>

            {/* CONTENT SCROLLABLE */}
            <div className={styles.contentWrapper}>
                <div className={`${styles.content} scrollbar`}>
                    <QuillRenderer content={job.content} />
                </div>
                <div className={`${styles.fadeBottom} fadeBottom`} />
            </div>

            {/* FOOTER */}
            <div className={styles.footer}>
                <div className={styles.footerLeft}>
                    <span className={styles.views}>{job.views} {t("global.views.viewsLabel", {count: job.views ?? 0})}</span>
                    <span className={styles.applicants}>🧑‍💻 {job.applications} {t("global.candidate.candidateLabel", {count: job.applications ?? 0})}</span>
                </div>
                {/** SEE MORE BUTTON */}
                <button 
                    onClick={()=>{
                        if(onClick)
                            onClick(job.id)
                    }}
                    className={styles.primaryAction}
                >
                    {t("global.messages.seeDetails")}
                </button>
            </div>
        </div>
    );
}
 

export default CurrentJob;


