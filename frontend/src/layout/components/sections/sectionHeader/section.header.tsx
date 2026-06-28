
//-- CSS Styles
import Title from "../../text/title/title";
import styles from "./SectionHeader.module.css"



interface SectionHeaderProps{
    title: string;
    action?: React.ReactNode
}


const SectionHeader: React.FC<SectionHeaderProps> = ({
    title,
    action
}) => {
    return (
        <div className={styles.container}>
            <div className={styles.txt}>
                <Title title={title}/>
            </div>
            { action && (
                <div className={styles.leading}>
                    {action}
                </div>
            )}
        </div>
    );
}
 
export default SectionHeader;