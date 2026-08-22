import React from "react";

// React - CSS
import styles from "./style.module.css";

interface StageTitleProps{
    step: number;
    txt: string;
    active?: boolean;
    subtitle?: string;
    disableCursorPointer?: boolean;
    backgroundColor?: string;
    textColor?: string;
    onClick?: React.MouseEventHandler<HTMLDivElement>;
}

const StageTitle: React.FC<StageTitleProps> = ({
    step,
    txt,
    active = false,
    subtitle = null,
    onClick,
    textColor,
    disableCursorPointer = false,
    backgroundColor = "rgba(255,255,255,.55)" ,
}) => {

    const formattedStep =
        step < 10 ? `0${step}` : step;

    return (
        <div
            className={`
                ${styles.container}
                ${active ? styles.active : ""}
            `}
            onClick={onClick}
            style={{ 
                ["--pointer" as string]: !disableCursorPointer &&  onClick ? "pointer" : "default",
                ["--backgroundColor" as string]: backgroundColor,
                ["--textColor" as string]: textColor,
                ["--stepColor" as string]: textColor
            }}
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