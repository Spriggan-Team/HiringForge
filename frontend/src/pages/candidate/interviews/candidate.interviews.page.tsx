
import React, { useMemo, useRef, useState } from "react"
import { eachDayOfInterval, endOfMonth, endOfWeek, isSameMonth, startOfMonth, startOfToday, startOfWeek } from "date-fns";

//-- Services & types
import type { CalendarEvent } from "../../../features/planning/planning";
import CalendarDay, { type CalendarDayProps, type CalendarHandleContext } from "../../../layout/components/cards/calendar/CalendarDay";

//-- Custom
import CalendarHeader from "../../../layout/components/cards/calendar/CalendarHeader";
import CalendarGrid from "../../../layout/components/cards/calendar/CalendarGrid";

//-- CSS Styles
import styles from "./CandidateInterviewsPage.module.css"


type ScheduledCalendartasks = Record<string, CalendarDayProps['scheduleTask']>;

interface CandidateInterviewsPageProps{}



const DAYS_LABEL = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];

const CandidateInterviewsPage: React.FC<CandidateInterviewsPageProps> = ({
}) => {
    const today = startOfToday();

    //-- Pagination
    const [skip, setSkip] = useState(0);
    const [hasMore, setHasMore] = useState(false);

    //-- Days
    const [currentMonth, setCurrentMonth] = useState(today);            //-- current month
    const [pendingDate, setPendingDate] = useState<Date | null>(null);  //-- Selected date

    //-- Interviews
    const [scheduledTasks, setScheduledTasks] = useState<ScheduledCalendartasks>({}); // key: Y-m-d
    const [interviewsCalendarEvent, setInterviewsCalendarEvent] = useState<CalendarEvent[]>();

    //-- Memory Cache
    const imageUrlCache = useRef<Record<string, string>>({}); //-- key: interview.id
    const interviewsCache = useRef<Record<string, CalendarEvent[]>>({}); //-- key: {skip,limit, date}
    const calendarScheduleCollection = useRef<Record<string, ScheduledCalendartasks>>({}); //-- Key: m-d


    //-- Current Date
    const days = useMemo(()=>{
        const firstDay = startOfWeek(startOfMonth(currentMonth), { weekStartsOn: 1 });
        const lastDay = endOfWeek(endOfMonth(currentMonth), { weekStartsOn: 1 });
        
        return eachDayOfInterval({
            start: firstDay,
            end: lastDay
        });
    },[currentMonth]);
    

    return (
        <div className={styles.container}>
            <div className={styles.calendar}>
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
                                    tasks={
                                        scheduledTasks[[
                                                d.getFullYear(),
                                                String(d.getMonth() + 1).padStart(2, "0"),
                                                String(d.getDate()).padStart(2, "0"),
                                            ].join("-")
                                        ]
                                    }
                                    disable={!isSameCurrentMonth}
                                    onClick={(date)=> {
                                        setSkip(0);
                                        setPendingDate(date)
                                    }}
                                />
                            )
                        })
                    }
                </CalendarGrid>
            </div>

            <div className={styles.aside}>
                
            </div>
        </div>
    );
}
 
export default CandidateInterviewsPage;


/**
 * Helpers 
 */

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