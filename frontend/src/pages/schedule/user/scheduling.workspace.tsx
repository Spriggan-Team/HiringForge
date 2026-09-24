

import { 
    startOfToday,
    format,
} from "date-fns";
import { useTranslation } from "react-i18next";
import { useCallback, useEffect, useRef, useState } from "react";

//-- Service 
import InterviewsQueries from "../../../api/services/interviews/queries";
import { formatInterviewsTitle, evalInterviewRate } from "../utils/format";
import type {  CalendarEvent } from "../../../features/planning/planning";


//-- Custom compoenents
import SchedulingAside from "../components/scheduling.aside";
import SchedulingCalendar from "../components/calendar/schedule.calendar";
import type { CalendarDayProps } from "../../../layout/components/cards/calendar/CalendarDay";


//-- CSS Styles
import styles from "./SchedulingWorkspace.module.css"



interface RecruiterCalendarEvent{}
type RecruiterCalendarEventItem = CalendarEvent<RecruiterCalendarEvent>;

interface SchedulingWorkspaceProps{}



type ScheduledCalendartasks = Record<string, CalendarDayProps['scheduleTask']>;


const PAGE_LIMIT = 15;

const SchedulingWorkspace: React.FC<SchedulingWorkspaceProps> = () => {
    const today = startOfToday();

    //-- Pagination
    const [skip, setSkip] = useState(0);
    const [hasMore, setHasMore] = useState(false);

    //-- Days
    const [currentMonth, setCurrentMonth] = useState(today);            //-- current month
    const [pendingDate, setPendingDate] = useState<Date | null>(null);  //-- Selected date

    //-- Interviews
    const [scheduledTasks, setScheduledTasks] = useState<ScheduledCalendartasks>({}); // key: Y-m-d
    const [interviewsCalendarEvent, setInterviewsCalendarEvent] = useState<RecruiterCalendarEventItem[]>();

    //-- Memory Cache
    const imageUrlCache = useRef<Record<string, string>>({}); //-- key: candidate.id
    const interviewsCache = useRef<Record<string, RecruiterCalendarEventItem[]>>({}); //-- key: {skip,limit, date}
    const calendarScheduleCollection = useRef<Record<string, ScheduledCalendartasks>>({}); //-- Key: m-d


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

            const promises: Promise<RecruiterCalendarEventItem>[] = result.map(async (t) => {
                let image: string | null = null;
                const imageKey = t.candidate.id;
                const imageCache = imageUrlCache.current;

                if (imageCache[imageKey]) {
                    image = imageCache[imageKey];
                }
                else {
                    const blob = await InterviewsQueries.getCandidateImage({
                        interviewId: t.id,
                        candidateId: t.candidate.id
                    });

                    image = URL.createObjectURL(blob);
                    imageCache[imageKey] = image;
                }

                const schedultedAt = new Date(t.startDate);
                const endDate = new Date(schedultedAt.getTime() + t.minutes * 60 * 1000) ;

                return {
                    id: t.id,
                    title: formatInterviewsTitle({
                        title: t.title,
                        type: t.type
                    }),
                    type: t.type,

                    url: t.url,
                    date: schedultedAt,
                    endDate: endDate,

                    note: t.description ?? "",
                    links: t.url ? [{url: t.url}] : null,
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
                    durationMinutes: t.minutes
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


    //--------------
    //-- Render
    //---------------

    return (
        <div className={styles.container}>
            {/** CALENDAR */}
            <div className={styles.calendar}>
                <SchedulingCalendar
                    today={today}
                    currentMonth={currentMonth}
                    setCurrentMonth={setCurrentMonth}
                    taskGenerator={(currentDate)=>{
                        return scheduledTasks[[
                                currentDate.getFullYear(),
                                String(currentDate.getMonth() + 1).padStart(2, "0"),
                                String(currentDate.getDate()).padStart(2, "0"),
                            ].join("-")
                        ]
                    }}
                    onSelectedDate={(date)=>{
                        setSkip(0);
                        setPendingDate(date)
                    }}
                />
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


