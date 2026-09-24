
import React from "react"

import styles from './CandidateScheduleItemActionButtons.module.css'
/**
 * Actions buttons 
 */

interface CandidateScheduleItemActionButtonsProps{
    id: string;
    onRefuse: ()=> void;
    onAcceptCompleted: ()=>void;
}

const CandidateScheduleItemActionButtons: React.FC<CandidateScheduleItemActionButtonsProps> = ({
    id,
    onRefuse,
    onAcceptCompleted,
}) => {
    return (
        <div 
            key={id}
            className={styles.taskActionsButtons}
        >
            <button 
                type="button"
                className={styles.acceptBtn}
                onClick={()=>{
                    onAcceptCompleted();
                }}
            >
                Accept
            </button>
            <button 
                type="button"
                className={styles.refuseBtn}
                onClick={onRefuse}
            >
                Refuse
            </button>
        </div>
    );
}
 
export default CandidateScheduleItemActionButtons;


