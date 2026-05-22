import React from "react";

//React -CSS
import styles from "./style.module.css"

interface StageTitleProps{
    step: number;
    txt: string;
}

const StageTitle: React.FC<StageTitleProps> = ({
        step,
        txt
}) => {
    return ( 
        <div className={styles.container}>
            <span>{step}</span>
            <span>{txt}</span>
        </div>
    );
}
 
export default StageTitle;