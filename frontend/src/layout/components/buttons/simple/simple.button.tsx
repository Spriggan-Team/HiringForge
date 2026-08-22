
import type { CSSProperties } from "react";
import styles from "./style.module.css"

interface SimpleButtonProps{
    text?: string;
    children?: React.ReactNode;
    style?: CSSProperties;
    onClick?: ()=>void;
    className?: string;
}

const SimpleButton: React.FC<SimpleButtonProps> = ({
    text,
    children,
    style,
    onClick, className,
}) => {
    if(!text && !children)
        return null;

    return ( 
        <button
            onClick={onClick}
            style={style}
            className={`${styles.button} ${className ?? styles.normal} `}
        >
            {text ?? children ?? "button"}
        </button>
    );
}
 
export default SimpleButton;