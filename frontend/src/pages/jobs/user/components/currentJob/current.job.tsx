
//-- Services
import { jobsViewData } from "../../../../../core/mock/job.data";

//--Custom Components
import QuillRenderer from "../../../../../layout/components/editors/quill/quill.renderer";
import MenuDrawer, { MenuDrawerBody, MenuDrawerItem, MenuDrawerTrigger } from "../../../../../layout/components/menu/drawer/menu.drawer";
import SectionHeader from "../../../../../layout/components/sections/sectionHeader/section.header";

//-- Custom SVG Component
import VerticalOptionsSVGComponent from "/src/assets/svg/menu/options-vertical-svgrepo-com.svg"

//-- Styles CSS
import styles from "./CurrentJob.module.css"
import InfoPill from "../../../../../layout/components/badges/pill/info.pill";

interface CurrentJobProps{}


const CurrentJob: React.FC<CurrentJobProps> = ({}) => {
    return (
        <div className={`${styles.container} card`}>
            {/* IMAGE */}
            <div className={styles.imageWrapper}>
                <img
                    src={jobsViewData[0].mainImage ?? "/jobs/placeholder-job.jpg"}
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
                                <MenuDrawerItem value="edit">Edit</MenuDrawerItem>
                                <MenuDrawerItem value="duplicate">Duplicate</MenuDrawerItem>
                                <MenuDrawerItem value="delete">Delete</MenuDrawerItem>
                                </MenuDrawerBody>
                            </MenuDrawer>
                        }
                    />
                </div>

                {/* META INFO GRID */}
                <div className={styles.metaGrid}>
                    <div className={styles.metaItem}>
                        <span className={styles.metaLabel}>Salary</span>
                        <span className={styles.metaValue}>€48K - €55K</span>
                    </div>

                    <div className={styles.metaItem}>
                        <span className={styles.metaLabel}>Location</span>
                        <span className={styles.metaValue}>Paris / Remote</span>
                    </div>

                    <div className={styles.metaItem}>
                        <span className={styles.metaLabel}>Contract</span>
                        <span className={styles.metaValue}>CDI</span>
                    </div>
                </div>

                {/* CATEGORIES */}
                <div className={styles.categories}>
                    {jobsViewData[0].categories.map((cat) => (
                        <JobCatItem key={cat} content={cat} />
                    ))}
                </div>
            </div>

            {/* CONTENT SCROLLABLE */}
            <div className={styles.contentWrapper}>
                <div className={`${styles.content} scrollbar`}>
                    <QuillRenderer content={jobsViewData[0].content} />
                </div>
                <div className={`${styles.fadeBottom} fadeBottom`} />
            </div>

            {/* FOOTER */}
            <div className={styles.footer}>
                <div className={styles.footerLeft}>
                    <span className={styles.views}>👁 1.2k views</span>
                    <span className={styles.applicants}>🧑‍💻 38 applicants</span>
                </div>
                <button className={styles.primaryAction}>Apply now</button>
            </div>
        </div>
    );
}
 
export default CurrentJob;


/** Job Category */

interface JobCatItemProps{
    content: string;
    bgColor?: "#64748B"
}

const JobCatItem: React.FC<JobCatItemProps> = ({
    content,
    bgColor
}) => {
    return (
        <div className={styles.category}>
            {content}
        </div>
    );
}
 