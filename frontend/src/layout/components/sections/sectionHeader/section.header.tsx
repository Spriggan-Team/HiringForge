
//-- CSS Styles
import Title from "../../text/title/title";
import styles from "./SectionHeader.module.css"



interface SectionHeaderProps{
    title: string | React.ReactNode;
    fontSize?: string;
    action?: React.ReactNode
}


const SectionHeader: React.FC<SectionHeaderProps> = ({
    title,
    fontSize,
    action
}) => {
    return (
        <div className={styles.container}>
            {
                typeof title === "string" ? (
                    <div className={styles.txt}>
                        <Title title={title} fontSize={fontSize}/>
                    </div>
                )
                : title
            }

            { action && (
                <div className={styles.leading}>
                    {action}
                </div>
            )}
        </div>
    );
}
 
export default SectionHeader;