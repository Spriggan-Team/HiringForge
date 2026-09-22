

import React, { useMemo, useRef } from "react";

import { 
    startOfWeek,
    eachDayOfInterval,
    endOfMonth, endOfWeek,
    startOfMonth, startOfToday,
    format,
    isSameMonth,
} from "date-fns";

//-- Types & serviecs


import CalendarContainer from "../../../../layout/components/cards/calendar/calendar.container";
import CalendarHeader from "../../../../layout/components/cards/calendar/CalendarHeader";
import CalendarGrid from "../../../../layout/components/cards/calendar/CalendarGrid";
import CalendarDay from "../../../../layout/components/cards/calendar/CalendarDay";
import type { CalendarDayProps, CalendarHandleContext, TaskData } from "../../../../layout/components/cards/calendar/CalendarDay";

//-- CSS Styles
import styles from "./SchedulingCalendar.module.css"


interface SchedulingCalendarProps{
    today?: Date;
    currentMonth: Date;
    setCurrentMonth: React.Dispatch<React.SetStateAction<Date>>;

    /** Event handlers */
    taskGenerator: (currentDate: Date) => TaskData[]; // used for generating tasks (events) associated to the current date
    onSelectedDate?: (selected: Date)=> void;
}


const DAYS_LABEL = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];


/**
 * Build calendar
 */
const SchedulingCalendar: React.FC<SchedulingCalendarProps> = ({
    today: t,
    currentMonth,
    setCurrentMonth,

    taskGenerator,
    onSelectedDate
}) => {
    const today = t ?? new Date();

    //-- Get days
    const days = useMemo(()=>{
        const firstDay = startOfWeek(startOfMonth(currentMonth), { weekStartsOn: 1 });
        const lastDay = endOfWeek(endOfMonth(currentMonth), { weekStartsOn: 1 });
        
        return eachDayOfInterval({
            start: firstDay,
            end: lastDay
        });
    },[currentMonth]);

    return (
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
                                (!isSameCurrentMonth ) && styles.outside
                            ].filter(Boolean).join(" ")

                            const tasks = taskGenerator(d);

                            return(
                                <CalendarDayView
                                    date={d}
                                    key={index}
                                    defaultDate={today}
                                    className={className}
                                    tasks={tasks}
                                    disable={!isSameCurrentMonth}
                                    onClick={(date)=>  onSelectedDate?.(date) }
                                />
                            )
                        })
                    }
                </CalendarGrid>
            </CalendarContainer>
        </div>
    );
}

 
export default SchedulingCalendar;



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