
import { JobStatisticsSection } from "../../jobs/components/tables/stats/job.statistics.sections";
import styles from "./UserStatsPage.module.css"


const UserStatsPage = () => {
    return (
        <div className={styles.container}>
            <JobStatisticsSection />
        </div>
    );
}
 
export default UserStatsPage;