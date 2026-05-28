import React from "react";

// React - CSS
import styles from "./style.module.css";

interface StageTitleProps{
    step: number;
    txt: string;
    active?: boolean;
    subtitle?: string;
}

const StageTitle: React.FC<StageTitleProps> = ({
    step,
    txt,
    active = false,
    subtitle = null
}) => {

    const formattedStep =
        step < 10 ? `0${step}` : step;

    return (
        <div
            className={`
                ${styles.container}
                ${active ? styles.active : ""}
            `}
        >
            <div className={styles.stepWrapper}>
                <span className={styles.step}>
                    {formattedStep}
                </span>
            </div>

            <div className={styles.content}>
                <span className={styles.txt}>
                    {txt}
                </span>
                { subtitle && (
                    <span className={styles.subtxt}>
                        {subtitle}
                    </span>
                )}

            </div>
        </div>
    );
}

export default StageTitle;