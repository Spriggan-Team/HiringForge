
import EmploymentOffersSection from "../../jobs/components/tables/employment/employment.offer.section";

//-- styles
import styles from "./UserOffersPage.module.css"


const UserOffersPage = () => {
    return (
        <div className={styles.container}>
            <EmploymentOffersSection />
        </div>
    );
}
 
export default UserOffersPage;