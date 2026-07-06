import { useState } from "react";

// SVG Components
import CloseSVGComponent from "/src/assets/svg/close-svgrepo-com.svg";

// CSS
import styles from "./EditableTextInput.module.css";

interface EditableTextInputProps {
    value?: string;
    placeholder?: string;
    className?: string;

    onChange?: (value: string) => void;
}

const EditableTextInput: React.FC<EditableTextInputProps> = ({
    value = "",
    placeholder = "Click to edit...",
    className,
    onChange,
}) => {
    const [content, setContent] = useState(value);

    const handleChange = (value: string) => {
        setContent(value);
        onChange?.(value);
    };

    const handleReset = () => {
        handleChange("");
    };
    

    return (
        <div className={`${styles.container} ${className ?? ""}`}>
            <input
                value={content}
                className={styles.input}
                placeholder={placeholder}
                onChange={(e) => handleChange(e.target.value)}
            />

            {content.length > 0 && (
                <button
                    type="button"
                    className={styles.reset}
                    onClick={handleReset}
                    aria-label="Clear text"
                >
                    <CloseSVGComponent
                        width={12}
                        height={12}
                    />
                </button>
            )}
        </div>
    );
};

export default EditableTextInput;