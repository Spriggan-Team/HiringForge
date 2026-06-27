
import styles from "./styles.module.css"

export interface CandidateCardProps{
    lastName: string;
    firstName: string;
    time: string;
}

const CandidateCard: React.FC<CandidateCardProps>  = ({
    time,
    lastName,
    firstName,
}) => {
    return (
        <div className={styles.card}>
            <span className={styles.name}>{firstName} {lastName}</span>
            <span className={styles.time}>{time}</span>
        </div>
    );
}
 
export default CandidateCard;