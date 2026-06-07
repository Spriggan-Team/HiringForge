
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
                ["--txtColor" as string]: txtColor,
                ["--backgroundColor" as string]: backgroundColor
            }}
        >
            { indicator && <div /> }
            { text }
        </div>
    );
}
 
export default InfoPill;