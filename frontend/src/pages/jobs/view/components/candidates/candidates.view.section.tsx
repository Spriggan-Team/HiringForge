
import { useState } from "react";


//-- CSS Moduels 
import styles from "./CandidatesViewSection.module.css"



const CandidatesViewSection = () => {
    const [candiidates, setCandidates] = useState();

    return (
        <div className={styles.container}>
            <div className={styles.header}>

            </div>
            Candidates section
        </div>
    );
}
 
export default CandidatesViewSection;