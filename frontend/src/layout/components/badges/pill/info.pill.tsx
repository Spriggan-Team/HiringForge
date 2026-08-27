
//-- Css styles 
import styles from "./style.module.css"


export interface InfoPillProps{
    text: string;
    indicator?: boolean;
    
    txtColor?: string;
    className?: string;
    backgroundColor?: string;
    borderRadius?: string | number;
}



const InfoPill: React.FC<InfoPillProps> = ({
    text,
    indicator,
    className,

    txtColor,
    borderRadius,
    backgroundColor,
}) => {
    if(!text)
        return null;
    
    return (
        <div 
            className={`${styles.container} ${className}`}
            style={{
                ["--txtColor" as string]: txtColor ?? "#264FEB",
                ["--backgroundColor" as string]: backgroundColor ?? "#E3EDFE",
                ["--borderRadius" as string]: typeof borderRadius === "string" ? borderRadius : !borderRadius ? "15px" : `${borderRadius}px`
            }}
        >
            { indicator && <div className={styles.indicator} /> }
            <span>{ text }</span>
        </div>
    );
}
 
export default InfoPill;