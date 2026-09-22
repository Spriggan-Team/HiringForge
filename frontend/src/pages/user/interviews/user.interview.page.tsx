//-- Components
import InterviewsSection from "../../jobs/components/tables/interviews/interviews.section";

//-- CSS Styles
import styles from "./UserInterviewsPage.module.css"

interface UserInterviewsPageProps{}


const UserInterviewsPage: React.FC<UserInterviewsPageProps> = ({}) => {
    return (
        <main className={styles.main}>
            <InterviewsSection />
        </main>
    );
}
 
export default UserInterviewsPage;