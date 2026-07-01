
import { useId } from "react";

//-- CSS Styles
import styles from "./CheckBoxInput.module.css";



interface CheckBoxInputProps {
    text: string;

    value?: string;
    checked?: boolean;

    id?: string;
    inputName?: string;
    className?: string;

    disabled?: boolean;
    onChange?: (checked: boolean) => void;
}


const CheckBoxInput: React.FC<CheckBoxInputProps> = ({
    id,
    text,
    value,
    checked,
    inputName,
    className,
    disabled = false,
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
                className={styles.input}
                type="checkbox"
                name={inputName ?? inputId}
                value={value}
                checked={checked}
                disabled={disabled}
                onChange={(e) => onChange?.(e.target.checked)}
            />

            <span className={styles.box}>
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

            <span className={styles.label}>
                {text}
            </span>
        </label>
    );
};


export default CheckBoxInput;