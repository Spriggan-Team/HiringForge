

import styles from "./styles.module.css"

const Home = () => {
    return ( 
        <div className={styles.container}>
            <div className={styles.statsSection}></div>
            <div className={styles.jobsSection}>
                <div className={styles.searchSection} ></div>
                <div className={styles.notificationSection}></div>
            </div>
        </div>
    );
}
 
export default Home;