
//-- Css styles 
import styles from "./style.module.css"


interface InfoPillProps{
    text: string;
    backgroundColor?: string;
    txtColor?: string;
    indicator?: boolean;
}

const InfoPill: React.FC<InfoPillProps> = ({
    text,
    backgroundColor,
    txtColor,
    indicator
}) => {
    return (
        <div 
            className={styles.container}
            style={{
                ["--txtColor" as string]: txtColor ?? "#264FEB",
                ["--backgroundColor" as string]: backgroundColor ?? "#E3EDFE"
            }}
        >
            { indicator && <div className={styles.indicator} /> }
            <span>{ text }</span>
        </div>
    );
}
 
export default InfoPill;