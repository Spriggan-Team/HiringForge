
//-- Custom components
import { useTranslation } from "react-i18next";
import Separator from "../../../../../layout/components/separator/separator";

//-- SVG Components
import RightToLeftArrowSVG from '/src/assets/svg/arrows/back-arrow-direction-down-right-left-up-svgrepo-com.svg';


//-- CSS Styles
import styles from "./Agenda.module.css"
import Title from "../../../../../layout/components/text/title/title";


interface AgendaProps{}

type AgendaItemType = "technical-interview" |  "rh-interview"

const mockData = [
    {
        time: "14h:10",
        title: "Entretien Technique",
        person: "Marc Leroy",
        type: "technical-interview" as AgendaItemType
    },
    {
        time: "14h:00",
        title: "Entretien RH",
        person: "Alice Dupont",
        type: "rh-interview" as AgendaItemType
    }
]



const Agenda: React.FC<AgendaProps> = ({

}) => {
    const {t} = useTranslation();
    return (
        <div className={styles.container}>
            <Title title={t("userHome.agenda.title")} />
            {
                mockData.map((item, key)=>(
                    <AgendaItem 
                        key={key}
                        time={item.time}
                        title={item.title}
                        person={item.person}
                        separatorColor={
                            item.type === "rh-interview"
                                ? "#3B82F6"
                                : item.type === "technical-interview"
                                    ? "#8B5CF6"
                                    : undefined
                        }
                    />
                ))
            }
        </div>
    );
}
 
export default Agenda;


/** Agenda Item */

interface AgendaItemProps{
    time: string;
    title: string;
    person: string;
    separatorColor?: string;
    onClick?: React.MouseEventHandler<HTMLDivElement>
}


const AgendaItem: React.FC<AgendaItemProps> = ({
    title,
    person,
    time,
    separatorColor = "#3B82F6",
    onClick,
}) => {
    return (
        <div 
            onClick={onClick}
            style={{
                ["--cursor" as string] : onClick ? "pointer" : "default"
            }}
            className={styles.item}
        >
            <div className={styles.left}>
                <Separator
                    width="4px"
                    height="60px"
                    orient="vertical"
                    backgroundColor={separatorColor}
                />

                <div className={styles.time}>
                    <Title title={time} />
                </div>

                <div className={styles.main}>
                    <Title title={title} />
                    <span>{person}</span>
                </div>
            </div>

            <div className={styles.chevron}>
                <RightToLeftArrowSVG
                    width={15} height={15}
                    style={{ transform: "rotate(180deg)"  }}
                />
            </div>
        </div>
    );
}
