
import { useAppContext } from "../../../../hooks/context";
import styles from "./style.module.css"


interface SpinnerProps{
    width?: string| number;
    height?: string| number;
    className?:string;
    activeColor?: string;
    forgroundColor?: string;
}

const Spinner: React.FC<SpinnerProps> = ({
    width, height, className,
    activeColor, forgroundColor
}) => {
    const spinnerStyle = {
        "--width": typeof width === "number" ? `${width}px` : width ?? "25px",
        "--height": typeof height === "number" ? `${height}px` : height ?? "25px",
        "--activeColor": activeColor ?? "#015DFC",
        "--forgroundColor": forgroundColor ?? "#DFE1E7"
    } as React.CSSProperties;

    return ( 
        <div
            style={spinnerStyle}
            className={`${styles.spinner} ${className}`}
        />
    );
}


export default Spinner;



interface AppSpinnerProps{
    children: React.ReactNode
}

export const AppSpinner: React.FC<AppSpinnerProps> = ({ children }) => {
    const { loading } = useAppContext();

    return (
        <>
            {children}

            {loading && loading.state && (
                <div className={styles.overlay}>
                    <Spinner width={40} height={40} />
                    {loading.subtitle && (<span className={styles.subtitle}>{loading.subtitle}</span>)}
                </div>
            )}
        </>
    );
};