

import { 
    startOfWeek,
    eachDayOfInterval,
    endOfMonth, endOfWeek,
    startOfMonth, startOfToday,
    format,
    isSameMonth,
} from "date-fns";
import { useTranslation } from "react-i18next";
import { useCallback, useMemo, useRef, useState } from "react";

//-- Custom compoenents
import Title from "../../layout/components/text/title/title";
import SchedulingAside from "./components/scheduling.aside";
import CalendarDay, { type CalendarDayProps, type CalendarHandleContext } from "../../layout/components/cards/calendar/CalendarDay";
import CalendarGrid from "../../layout/components/cards/calendar/CalendarGrid";
import CalendarHeader from "../../layout/components/cards/calendar/CalendarHeader";
import CalendarContainer from "../../layout/components/cards/calendar/calendar.container";
import CalendarEvent from "../../layout/components/cards/calendar/CalendarEvent";

//-- CSS Styles
import styles from "./SchedulingWorkspace.module.css"



interface SchedulingWorkspaceProps{}




const DAYS_LABEL = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];



const SchedulingWorkspace: React.FC<SchedulingWorkspaceProps> = () => {
    const today = startOfToday();
    const { t } = useTranslation();

    const [currentMonth, setCurrentMonth] = useState(today);
    const [pendingDate, setPendingDate] = useState<Date | null>(null);


    const days = useMemo(()=>{
        const firstDay = startOfWeek(startOfMonth(currentMonth), { weekStartsOn: 1 });
        const lastDay = endOfWeek(endOfMonth(currentMonth), { weekStartsOn: 1 });
        
        return eachDayOfInterval({
            start: firstDay,
            end: lastDay
        });
    },[currentMonth])

    return (
        <div className={styles.container}>
            {/** CALENDAR */}
            <div className={styles.calendar}>
                <CalendarContainer>
                    <CalendarHeader 
                        defaultDate={today}
                        onChange={(date)=> setCurrentMonth(date)}
                    />
                    
                    <CalendarGrid>
                        {
                            DAYS_LABEL.map((label, key) => (
                                <span key={key} className={styles.dayLabel}>{label}</span>
                            ))
                        }
                        {
                            days.map((d, index)=>{
                                const isSameCurrentMonth  = isSameMonth(d, currentMonth);
                                const className = [
                                    !isSameCurrentMonth && styles.outside
                                ].filter(Boolean).join(" ")

                                return(
                                    <CalendarDayView
                                        date={d}
                                        key={index}
                                        defaultDate={today}
                                        className={className}
                                        disable={!isSameCurrentMonth || today > d}
                                        onClick={(date)=> setPendingDate(date)}
                                    />
                                )
                            })
                        }
                    </CalendarGrid>
                </CalendarContainer>
            </div>

            <SchedulingAside
                date={pendingDate ?? today}
            />
        </div>
    );
}

 

export default SchedulingWorkspace;



/** CalendarDay View */

interface CalendarDayViewProps{
    date: Date | null;
    defaultDate: Date;
    tasks?: CalendarDayProps['scheduleTask'];

    onClick?: (date: Date)=>void;
    onSave?: ()=> void;

    className?: string;
    disable?: boolean;
}


const CalendarDayView: React.FC<CalendarDayViewProps> = ({
    date,
    tasks = [],
    defaultDate,

    onClick,
    disable,
    className
}) => {
    const ref = useRef<CalendarHandleContext>(null);

    return (
        <CalendarDay
            ref={ref}
            disable={disable}
            className={`${className} ${disable ? styles.disable : ""}`}
            date={date ?? defaultDate}
            scheduleTask={tasks}
            onClick={onClick}
        >
            {/* <CalendarEvent
                date={date}
                onSave={() => {}}
                onClose={() => {
                    ref.current?.requestClose(true);
                }}
            /> */}
        </CalendarDay>
    );
};