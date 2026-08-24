
import { useTranslation } from "react-i18next";

//-- Custom composant
import Title from "../../../../../layout/components/text/title/title";
import SectionHeader from "../../../../../layout/components/sections/sectionHeader/section.header";
import ViewAllLink from "../../../../../layout/components/link/view.all.link";

//-- SVG Components
import AlertSVG from "/src/assets/svg/security/alert-rhombus-svgrepo-com.svg?react"
import LeftToRightChevronSVG from "/src/assets/svg/arrows/chevron-right-double-svgrepo-com.svg?react"

//-- CSS Styles
import styles from "./PriorityTask.module.css"


interface PriorityTaskProps{
    width?: string
}


const mockData = [
    {
        title: "3 Entretiens Aujourd'hui",
        desc: "2h à venir"
    },
    {
        title: "5 candidatures sans retour",
        desc: "depuis 5 jours"
    },
    {
        title: "2 offres expirent cette semaine",
        desc: "à vérifier"
    }
]

const PriorityTask: React.FC<PriorityTaskProps> = ({
    width
}) => {
    const { t } = useTranslation();
    return (
        <div 
            style={{
                ["--width" as string]: width ?? "100%"
            }}
            className={styles.container}
        >
            <div className={styles.header}>
                <SectionHeader
                    title={t("userHome.priorityTask.title")} 
                    action={<ViewAllLink />}
                />
            </div>
            <div className={styles.items}>
                {
                    mockData.map((item, index) => (
                        <Task 
                            key={index}
                            title={item.title}
                            desc={item.desc}
                        />
                    ))
                }
            </div>
        </div>
    );
}
 
export default PriorityTask;


//--- Task

interface TaskProps{
    title: string;
    desc?: string;
    onClick?: React.MouseEventHandler
}

const Task: React.FC<TaskProps> = ({
    title,
    desc,
    onClick
}) => {
    return (
        <div  
            onClick={onClick}
            style={{
                ["--cursor" as string]: onClick ? "pointer" : "default"
            }}
            className={styles.item}
        >
            <div className={styles.left}>
                <AlertSVG  
                    width={15}
                    height={15}
                    className={styles.alertSvg}
                />

                <div className={styles.content}>
                    <div className={styles.titleSection}>
                        <Title title={title}/>
                    </div>
                    <span className={styles.desc} >{desc}</span>
                </div>
            </div>
            
            <LeftToRightChevronSVG  
                width={15}
                height={15}
                className={styles.chevronSvg}
            />
        </div>
    );
}
 