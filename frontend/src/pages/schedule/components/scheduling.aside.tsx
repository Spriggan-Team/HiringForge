import { format } from "date-fns";
import { useTranslation } from "react-i18next";

//-- Services & types
import type { CalendarEvent } from "../../../features/planning/planning";

//-- Custom components
import ArrowNavigation from "../../../layout/components/navigation/arrow.navigation";
import Title from "../../../layout/components/text/title/title";
import { CardPlaceholder } from "../../../layout/components/cards/placeholder/card.placeholder";
import EventCard from "../../../layout/components/cards/event/event.card";


//-- CSS Modules
import styles from "./SchedulingAside.module.css"


interface SchedulingAsideProps{
    date: Date;
    events: CalendarEvent[];
    className?: string;
    children?: React.ReactNode;

    onPrev?:(currentLenght: number) => void;
    onNext?: (currentLenght: number) => void;
}

const SchedulingAside: React.FC<SchedulingAsideProps> = ({
    date,
    events = [],
    children,

    onNext,
    onPrev,
    className
}) => {
    const { t } = useTranslation();
    
    return (
        <div className={`${styles.aside} ${className}`}>
            {/** Header */}
            <div className={styles.header}>
                {/** TEXT SECTION */}
                <div className={styles.textSection}>
                    <Title title={t("scheduler.scheduled")}/>
                    <span className={styles.date}>{format(date, "d MMMM, yyyy")}</span>
                </div>

                {/** NAVIGATION ARROWS */}
                <ArrowNavigation 
                    onPrevious={()=> onPrev?.(events.length)}
                    onNext={()=> onNext?.(events.length)}
                />
            </div>

            {/** ITEMS */}
            <div className={`${styles.items} scrollbar`}>
                {
                    events.length > 0 ?
                        events.map((event,index)=>{
                            return (
                                <EventCard
                                    t={t}
                                    key={index}
                                    event={event}
                                >
                                    {children && children}
                                </EventCard>
                            );
                        }) :
                        <CardPlaceholder text="Aucun entretien" />
                }
            </div>
        </div>
    );
}
 
export default SchedulingAside ;

