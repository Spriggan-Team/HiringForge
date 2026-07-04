

import styles from "./InputLabel.module.css"

interface InputLabelProps{
    label?: string;
    inputName?: string;
    className?: string;

    children?: React.ReactNode
}

const InputLabel: React.FC<InputLabelProps> = ({
    label,
    inputName,
    className,
    children
}) => {
    if(!label && !children)
        return null;

    return (
        <label htmlFor={inputName} className={`${styles.label} ${className}`} >{label ? label : children ?? ""}</label>
    );
}
 
export default InputLabel;