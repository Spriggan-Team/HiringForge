
//-- Custom components
import Title from "../../../../../layout/components/text/title/title";

//-- CSS Module
import styles from "./OptionBoxSection.module.css"


interface OptionBoxSectionProps{

}


const OptionBoxSection: React.FC<OptionBoxSectionProps> = ({}) => {
    return (
        <div className={styles.container}>
            <div className={styles.card}>
                <Title title=""/>
            </div>
        </div>
    );
}
 
export default OptionBoxSection;