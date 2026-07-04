
import { useId } from "react";

//-- CSS Styles
import styles from "./CheckBoxInput.module.css";



interface CheckBoxInputProps {
    text?: string;
    desc?: string;
    children?:string;

    value?: string;
    checked?: boolean;

    id?: string;
    inputName?: string;
    className?: string;
    indicatorClassName?: string;
    borderRadius?: string;

    disabled?: boolean;
    onChange?: (checked: boolean) => void;
}


const CheckBoxInput: React.FC<CheckBoxInputProps> = ({
    id,
    text,
    children,
    desc,

    value,
    checked,
    inputName,
    className,
    indicatorClassName,

    disabled = false,
    borderRadius,
    onChange,
}) => {

    const inputId = id ?? useId();

    return (
        <label
            htmlFor={inputId}
            className={`${styles.container} ${className ?? ""}`}
        >
            <input
                id={inputId}
                type="checkbox"
                value={value}
                checked={checked}
                disabled={disabled}
                className={styles.input}
                name={inputName ?? inputId}
                onChange={(e) => onChange?.(e.target.checked)}
            />

            <span
                style={{
                    borderRadius: borderRadius ?? "6px",
                }}
                className={`${styles.box} ${indicatorClassName}`}
            >
                <svg
                    viewBox="0 0 24 24"
                    className={styles.icon}
                >
                    <path
                        fill="none"
                        d="M5 13l4 4L19 7"
                        stroke="currentColor"
                        strokeWidth="3"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                    />
                </svg>
            </span>

            <div>
                {children && (children)}
                {
                    text && (
                        <span className={styles.label}>
                            {text}
                        </span>
                    )
                }
                {
                    desc && (
                        <span className={styles.desc}>
                            {desc}
                        </span>
                    )
                }
            </div>
        </label>
    );
};


export default CheckBoxInput;