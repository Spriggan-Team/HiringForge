
import styles from "./style.module.css"

interface SimpleButtonProps{
    text?: string;
    children?: React.ReactNode;

    onClick?: ()=>void;
    className?: string;
}

const SimpleButton: React.FC<SimpleButtonProps> = ({
    text,
    children,

    onClick, className,
}) => {
    if(!text && !children)
        return null;

    return ( 
        <button
            onClick={onClick}
            className={`${styles.button} ${className ?? styles.normal} `}
        >
            {text ?? children ?? "button"}
        </button>
    );
}
 
export default SimpleButton;