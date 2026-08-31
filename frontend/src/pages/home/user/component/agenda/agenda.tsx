import { useTranslation } from "react-i18next";

//-- Custom components
import Title from "../../../../../layout/components/text/title/title";
import Separator from "../../../../../layout/components/separator/separator";

//-- Services
import { useEffect, useState } from "react";
import InterviewsQueries from "../../../../../api/services/interviews/queries";
import { InterviewType, type InterviewTypeValue, type InterviewWithCandidateData } from "../../../../../features/interviews/interviews";

//-- SVG Components
import RightToLeftArrowSVG from '/src/assets/svg/arrows/back-arrow-direction-down-right-left-up-svgrepo-com.svg?react';


//-- CSS Styles
import styles from "./Agenda.module.css"
import { formatMinutesIntoTime } from "../../../../../utils/format";



interface AgendaProps{}

type AgendaItemType = "technical-interview" |  "rh-interview"


const colorScheme = {
    [InterviewType.RH_INTERVIEWS]: "#3B82F6",
    [InterviewType.TECHNICAL_INTERVIEWS]: "#8B5CF6"
}


const Agenda: React.FC<AgendaProps> = ({

}) => {
    const {t} = useTranslation();
    const [agenda, setAgenda] = useState<InterviewWithCandidateData[]>([]);

    useEffect(()=>{
        const fetchData = async ()=>{
            try{
                const data = await InterviewsQueries.getInterviewAgendaForRecruiter();
                console.log("Data ", data);
                setAgenda(agenda);
            }
            catch(error){
                console.log("Something went wrong while retreiving agenda data")
            }
        }
        fetchData()
    },[])

    return (
        <div className={styles.container}>
            <Title title={t("userHome.agenda.title")} />
            {
                agenda.map((item, key)=>(
                    <AgendaItem 
                        key={key}
                        time={formatMinutesIntoTime(item.minutes)}
                        subject={item.type}
                        person={`${item.candidate.firstName} ${item.candidate.lastName}`}
                        separatorColor={item.type ? colorScheme[item.type] : "#6e6868" }
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
    person: string;
    separatorColor?: string;
    subject: InterviewTypeValue | undefined;
    onClick?: React.MouseEventHandler<HTMLDivElement>
}


const AgendaItem: React.FC<AgendaItemProps> = ({
    subject,
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
                    <Title title={ subject ? getInterviewsSubject(subject) : "" } />
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

const INTERVIEW_SUBJECTS: Record<InterviewTypeValue, string> = {
  [InterviewType.RH_INTERVIEWS]: "Entretien RH",
  [InterviewType.TECHNICAL_INTERVIEWS]: "Entretien Technique",
};

const getInterviewsSubject = (value: InterviewTypeValue): string => {
  return INTERVIEW_SUBJECTS[value] ?? "";
};