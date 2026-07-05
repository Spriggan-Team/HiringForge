

import React, { useId } from "react";

//-- CSS Module
import styles from "./ToggleSwitch.module.css";



interface ToggleSwitchProps {
    id?: string;
    checked?: boolean;
    defaultChecked?: boolean;

    text?: string;
    desc?: string;

    disabled?: boolean;

    className?: string;
    labelClassName?: string;
    switchClassName?: string;

    onChange?: (value: boolean) => void;
}



const ToggleSwitch: React.FC<ToggleSwitchProps> = ({
    id,
    checked,
    defaultChecked = false,

    text,
    desc,

    disabled = false,

    className,
    labelClassName,
    switchClassName,

    onChange,
}) => {
    const inputId = id ?? useId();

    return (
        <label
            htmlFor={inputId}
            className={`${styles.container} ${className ?? ""} ${
                disabled ? styles.disabled : ""
            }`}
        >
            <input
                id={inputId}
                type="checkbox"
                className={styles.input}
                checked={checked}
                defaultChecked={defaultChecked}
                disabled={disabled}
                onChange={(e) => onChange?.(e.target.checked)}
            />

            {/* Toggle visual */}
            <div className={`${styles.switch} ${switchClassName ?? ""}`}>
                <div className={styles.circle} />
            </div>

            {/* Text */}
            {(text || desc) && (
                <div className={`${styles.textBox} ${labelClassName ?? ""}`}>
                    {text && <span className={styles.text}>{text}</span>}
                    {desc && <span className={styles.desc}>{desc}</span>}
                </div>
            )}
        </label>
    );
};

export default ToggleSwitch;