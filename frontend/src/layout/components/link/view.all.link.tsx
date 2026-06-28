
import { useTranslation } from "react-i18next";

//-- SVG Components
import RightToLeftArrowSVG from '/src/assets/svg/arrows/back-arrow-direction-down-right-left-up-svgrepo-com.svg';

//-- CSS module
import styles from "./ViewAllLink.module.css"



interface ViewActionProps{
    onClick?: React.MouseEventHandler
}


const ViewAllLink: React.FC<ViewActionProps> = ({
    onClick
}) => {
    const {t} = useTranslation();

    return (
        <div className={styles.container}>
            <RightToLeftArrowSVG
                width={15} 
                height={15}
                style={{ transform: "rotate(180deg)"  }}
            />
            <span 
                onClick={onClick}
                style={{
                    ['--cursor' as any]: onClick ? "pointer" : "default"
                }}
                className={styles.link}
            >
                {t("global.link.viewAll")}
            </span>
        </div>
    );
}
 
export default ViewAllLink;