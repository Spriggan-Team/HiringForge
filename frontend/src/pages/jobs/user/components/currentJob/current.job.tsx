
import { useTranslation } from "react-i18next";

//-- Services
import type { JobActivityStatus, JobEngagementMetrics, JobPublicationStatus, JobStatus, JobView, VisibilityStatus } from "../../../../../features/jobs/JobOffer";

//--Custom Components
import MenuDrawer, { MenuDrawerBody, MenuDrawerItem, MenuDrawerTrigger } from "../../../../../layout/components/menu/dropdown/menu.dropdown";
import SectionHeader from "../../../../../layout/components/sections/sectionHeader/section.header";
import InfoPill from "../../../../../layout/components/badges/pill/info.pill";
import JobSkill from "../../../components/skills/job.skill";
import TipTapRenderer from "../../../../../layout/components/editors/tiptap/tiptap.renderer";

//-- Custom SVG Component
import VerticalOptionsSVGComponent from "/src/assets/svg/menu/options-vertical-svgrepo-com.svg?react"

//-- Styles CSS
import styles from "./CurrentJob.module.css"


interface CurrentJobProps{
    onClick: (id: string) => void;
    job: JobView & JobEngagementMetrics;
    onInvalidateCache?: (jobId?: string) => void;
}


const CurrentJob: React.FC<CurrentJobProps> = ({
    job,
    onClick,
    onInvalidateCache,
}) => {
    const {t} = useTranslation();

    const handleAction = (actionType: string) => {
        if (actionType === 'edit' || actionType === 'delete') {
            // Invalidations du cache pour la mise à jour
            onInvalidateCache?.(job.id);
        }
    };

    const renderVisibilityStatus = (status: JobStatus): string => {
        switch (status) {
            case 'published':
                return t('global.jobs.publicationState.published');
            case 'draft':
                return t('global.jobs.publicationState.draft');
            case 'active':
                return t('global.jobs.offerStatus.active');
            case 'closed':
                return t('global.jobs.publicationState.closed');
            case 'pending':
                return t('global.jobs.offerStatus.pending');
            default:
                return t('global.jobs.publicationState.published');
        }
    };

    const displayStatus = (
        publicationStatus: JobPublicationStatus, 
        activityStatus?: JobActivityStatus
    ): string => {
        if (
            publicationStatus === 'published' && 
            (activityStatus === 'active' || activityStatus === 'pending')
        ) {
            return renderVisibilityStatus(activityStatus);
        }

        return renderVisibilityStatus(publicationStatus);
    };


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

                {/* Option A : Si overlay et badge sont frères */}
                <div className={styles.imageOverlay} />
                <div className={styles.badgeOverlay}>
                    <InfoPill 
                        className={styles.infoPill} 
                        text={displayStatus(job.publicationStatus, job.activityStatus)} 
                        indicator 
                    />
                </div>
            </div>

            {/* HEADER */}
            <div className={styles.header}>
                <div className={styles.headerTop}>
                    <SectionHeader
                        title={job.title}
                        action={
                            <MenuDrawer
                                onChange={(value)=>{
                                    console.log("VALUE", value)
                                }}
                            >
                                <MenuDrawerTrigger displayArrowDown={false}>
                                {() => (
                                    <VerticalOptionsSVGComponent width={18} height={18} />
                                )}
                                </MenuDrawerTrigger>

                                <MenuDrawerBody>
                                    {
                                    job.publicationStatus != 'closed' ?  (
                                            <MenuDrawerItem 
                                                value="edit"
                                            >
                                                {t("global.actions.edit")}
                                            </MenuDrawerItem>
                                        ) 
                                        : null
                                    }
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
                                <span className={styles.metaValue}>{job.contract.label}</span>
                            </div>
                        )
                    }
                </div>

                {/* CATEGORIES */}
                <div className={styles.skills}>
                    {job.skills?.map((skill, index) => (
                        <JobSkill key={index} content={skill.name} />
                    ))}
                </div>
            </div>

            {/* CONTENT SCROLLABLE */}
            <div className={styles.contentWrapper}>
                <div className={`${styles.content} scrollbar`}>
                    <TipTapRenderer key={job.id} content={job.content} />
                </div>
                <div className={`${styles.fadeBottom} fadeBottom`} />
            </div>

            {/* FOOTER */}
            <div className={styles.footer}>
                <div className={styles.footerLeft}>
                    <span className={styles.views}>{job.views} {t("global.views.viewsLabel", {count: job.views ?? 0})}</span>
                    <span className={styles.applicants}>🧑‍💻 {job.applications} {t("global.candidate.candidateLabel", { count: job.applications ?? 0 })}</span>
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


