import CheckSVG from "../../../../assets/svg/check/check-svgrepo-com.svg"

//Css styles
import styles from "./style.module.css"


interface StatusItemProps{
    text : string;
    width?: number | string;
    height?: number | string;
}

const StatusItem: React.FC<StatusItemProps> = ({text}) => {
    return ( 
        <div className={styles.container}>
            <CheckSVG height={35} width={35}/>
            <div className={styles.text}>{text}</div>
        </div>
    );
}
 
export default StatusItem;