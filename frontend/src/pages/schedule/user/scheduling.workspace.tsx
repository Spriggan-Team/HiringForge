

import { 
    startOfWeek,
    eachDayOfInterval,
    endOfMonth, endOfWeek,
    startOfMonth, startOfToday,
    format,
    isSameMonth,
} from "date-fns";
import { useTranslation } from "react-i18next";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";

//--Service
import InterviewsQueries from "../../../api/services/interviews/queries";

//-- Custom compoenents
import Title from "../../../layout/components/text/title/title";
import SchedulingAside from "../components/scheduling.aside";
import CalendarDay, { type CalendarDayProps, type CalendarHandleContext } from "../../../layout/components/cards/calendar/CalendarDay";
import CalendarGrid from "../../../layout/components/cards/calendar/CalendarGrid";
import CalendarHeader from "../../../layout/components/cards/calendar/CalendarHeader";
import CalendarContainer from "../../../layout/components/cards/calendar/calendar.container";


//-- CSS Styles
import styles from "./SchedulingWorkspace.module.css"
import type { InterviewStatusValue, InterviewTypeValue, InterviewWithCandidateData } from "../../../features/interviews/interviews";
import { formatInterviewsTitle, evalInterviewRate } from "../utils/format";
import type {  CalendarEvent, TaskRate } from "../../../features/planning/planning";
import { calculateInterviewTimeRange } from "../../../utils/dates";


interface SchedulingWorkspaceProps{}



const DAYS_LABEL = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];


interface CalendarInterviewCollectionItem{
    id: string;
    title?: string;
    startDate: string;
    minutes: number;
    type?: InterviewTypeValue,
    status: InterviewStatusValue
}


type ScheduledCalendartasks = Record<string, CalendarDayProps['scheduleTask']>;

const PAGE_LIMIT = 15;

const SchedulingWorkspace: React.FC<SchedulingWorkspaceProps> = () => {
    const today = startOfToday();
    const { t } = useTranslation();

    //-- Pagination
    const [skip, setSkip] = useState(0);
    const [hasMore, setHasMore] = useState(false);

    //-- Days
    const [currentMonth, setCurrentMonth] = useState(today); //-- 
    const [pendingDate, setPendingDate] = useState<Date | null>(null); //-- Selected date

    //-- Interviews
    const [scheduledTasks, setScheduledTasks] = useState<ScheduledCalendartasks>({}); // key: Y-m-d
    const [interviewsCalendarEvent, setInterviewsCalendarEvent] = useState<CalendarEvent[]>();

    //-- Memory Cache
    const calendarScheduleCollection = useRef<Record<string, ScheduledCalendartasks>>({}); //Key: m-d
    const interviewsCache = useRef<Record<string, CalendarEvent[]>>({}); // key: {skip,limit, date}
    const imageUrlCache = useRef<Record<string, string>>({}); //-- key: interview.id

    //-- Get days
    const days = useMemo(()=>{
        const firstDay = startOfWeek(startOfMonth(currentMonth), { weekStartsOn: 1 });
        const lastDay = endOfWeek(endOfMonth(currentMonth), { weekStartsOn: 1 });
        
        return eachDayOfInterval({
            start: firstDay,
            end: lastDay
        });
    },[currentMonth]);



    //-------------------
    //-- Event Handlers 
    //-------------------
    const fetchCurrentDateAgenda = useCallback(async(currentDate: Date)=>{
        try{
            
            const dateKey = format(currentDate, "yyyy-MM-dd");
            const cacheKey = JSON.stringify({ skip, limit: PAGE_LIMIT, date: dateKey });
            const cache = interviewsCache.current;

            if(cache[cacheKey]){
                console.log("Interviews deatails: ", cache[cacheKey]);
                setInterviewsCalendarEvent(cache[cacheKey]);
                return;
            }

            const result = await InterviewsQueries.getInterviewAgendaForRecruiter({
                skip, 
                limit: PAGE_LIMIT, 
                date: currentDate ,
            });

            const promises: Promise<CalendarEvent>[] = result.map(async (t) => {
                const timeRange = calculateInterviewTimeRange(
                    t.startDate,
                    t.minutes
                );

                let image: string | null = null;
                const imageKey = t.id;
                const imageCache = imageUrlCache.current;

                if (imageCache[imageKey]) {
                    image = imageCache[imageKey];
                } else {
                    const blob = await InterviewsQueries.getCandidateImage({
                        interviewId: t.id,
                        candidateId: t.candidate.id
                    });

                    image = URL.createObjectURL(blob);
                    imageCache[imageKey] = image;
                }

                return {
                    id: t.id,
                    title: formatInterviewsTitle({
                        title: t.title,
                        type: t.type
                    }),
                    date: new Date(t.startDate),
                    type: t.type,
                    note: t.description ?? "",
                    rate: evalInterviewRate({
                        interview: {
                            startDate: t.startDate,
                            minutes: t.minutes
                        },
                        now: today
                    }),
                    members: [{
                        name: `${t.candidate.firstName} ${t.candidate.lastName}`,
                        image
                    }],
                    time: {
                        start: timeRange.startTime,
                        end: timeRange.endTime
                    }
                };
            });

            const mapped: CalendarEvent[] = await Promise.all(promises);
            cache[cacheKey] = mapped;

            setInterviewsCalendarEvent(mapped);
            //-- Determine has more
            setHasMore(result.length === PAGE_LIMIT);
        }
        catch(error){
            console.warn("Something went wrong when fetching user agenda: ", error)
        }
    }, [skip]);


    //--------------
    //-- Effects
    //---------------
    
    //-- Handler Programm Collection
    useEffect(()=>{
        const fetchData = async ()=>{
            try{
                let cache = calendarScheduleCollection.current;

                //calendar collection cache
                const cacheKey = [
                    currentMonth.getFullYear(),
                    String(currentMonth.getMonth() + 1).padStart(2, "0")
                ].join("-");

                if(cache[cacheKey]){
                    setScheduledTasks(cache[cacheKey]);
                    return;
                }

                //-- current calendar collection
                const results = await InterviewsQueries.getRecruiterCalendarPlaning({ currentMonth });
                console.log("RESULTS: ", results);

                //- Normalizing
                let scheduledTasks: ScheduledCalendartasks = {}
                for(const [key, interview] of Object.entries(results)){ //- key: Y-m-d
                    const tasks = interview.map((t)=> ({
                        task: formatInterviewsTitle({title: t.title, type: t.type}),
                        rate: evalInterviewRate({ interview: t, now: today })
                    }));

                    scheduledTasks[key] = tasks; 
                }

                cache[cacheKey] =  scheduledTasks; //-- update cache
                // console.log(results)

                setScheduledTasks(scheduledTasks) //result work with the same cache key
            }
            catch(error){
                console.warn("Something went wrong while")
            }
        }
        fetchData();
    },[currentMonth]);

    

    useEffect(()=>{
        if(pendingDate){
            fetchCurrentDateAgenda(pendingDate);
        }
    }, [pendingDate, fetchCurrentDateAgenda])


    //-- Clean - up
    useEffect(()=>{
        return ()=>{
            Object.values(imageUrlCache.current).forEach((item)=>URL.revokeObjectURL(item))
        }
    },[])

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
                </CalendarContainer>
            </div>

            <SchedulingAside
                events={interviewsCalendarEvent ?? []}
                onPrev={()=>{
                    setSkip((prev) => Math.max(0, prev - PAGE_LIMIT));
                }}
                onNext={()=>{
                    if(hasMore){
                        setSkip((prev)=> prev + PAGE_LIMIT);
                    }
                }}
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