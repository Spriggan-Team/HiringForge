import React from 'react'
import { format } from "date-fns";
import { useTranslation } from "react-i18next";

//-- Services & types
import type { CalendarEvent } from "../../../features/planning/planning";

//-- Custom components
import ArrowNavigation from "../../../layout/components/navigation/arrow.navigation";
import Title from "../../../layout/components/text/title/title";
import { CardPlaceholder } from "../../../layout/components/cards/placeholder/card.placeholder";
import EventCard from "../../../layout/components/cards/event/event.card";
import { 
    SchedulingAsideItemFooter,
    SchedulingAsideHeader,
    type BasicSlotComponent,
    type SchedulingAsideSlotProps
} from './slot/scheduling.aside.slot';


//-- CSS Modules
import styles from "./SchedulingAside.module.css"


type SchedulingAsideProps<T = {}> = {
    date: Date;
    events: CalendarEvent<T>[];
    className?: string;
    
    onPrev?:(currentLenght: number) => void;
    onNext?: (currentLenght: number) => void;
    
    children?:  React.ReactNode;
}


const SchedulingAside = <T,>({
    date,
    events = [],
    children,

    onNext,
    onPrev,
    className
}: SchedulingAsideProps<T>) => {
    const { t } = useTranslation();

    /**
     * -----------------
     * Slots Components
     * -----------------
     */
    let header: React.ReactNode = null;
    let actions: ((event: CalendarEvent<T>) => React.ReactNode) | null  = null;

    React.Children.forEach(children, (child)=>{
        if(!React.isValidElement(child)){
            return;
        }

        if (child.type === SchedulingAsideHeader) {
            header = (child.props as BasicSlotComponent).children;
        }
        
        if (child.type === SchedulingAsideItemFooter) {
            const actionChildren = (child.props as SchedulingAsideSlotProps<T>).children;

            if (typeof actionChildren === "function") {
                actions = actionChildren;
            }
        }
    })
    
    return (
        <div className={`${styles.aside} ${className}`}>
            {/** Header */}
            <div className={styles.header}>
                {
                    header ?? (
                        <>
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
                        </>
                    )
                }

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
                                    {actions?.(event)}
                                </EventCard>
                            );
                        }) :
                        <CardPlaceholder text="Aucun entretien" />
                }
            </div>
        </div>
    );
}


export default SchedulingAside;



