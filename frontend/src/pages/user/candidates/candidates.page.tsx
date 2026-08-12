

import ApplicationsTable from "../../jobs/components/tables/candidates/application.table";
import styles from "./CandidatesPage.module.css"


interface CandidatesPageProps{}


const CandidatesPage: React.FC<CandidatesPageProps> = ({}) => {
    return (
        <div className={styles.container}>
            <ApplicationsTable />
        </div>
    );
}
 
export default CandidatesPage;