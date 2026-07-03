

import styles from "./InputLabel.module.css"

interface InputLabelProps{
    label: string;
    inputName?: string;
    className?: string;
}

const InputLabel: React.FC<InputLabelProps> = ({
    label,
    inputName,
    className
}) => {
    return (
        <label htmlFor={inputName} className={`${styles.label} ${className}`} >{label}</label>
    );
}
 
export default InputLabel;