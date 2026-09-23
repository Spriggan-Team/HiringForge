
import React, {  useCallback, useEffect, useRef, useState } from "react"
import { format, startOfToday } from "date-fns";

//-- Services & types
import InterviewsQueries from "../../../../api/services/interviews/queries";
import type { CalendarEvent } from "../../../../features/planning/planning";
import { evalInterviewRate, formatInterviewsTitle } from "../../utils/format";
import type { CalendarDayProps } from "../../../../layout/components/cards/calendar/CalendarDay";
import { InterviewStatus, type InterviewStatusValue } from "../../../../features/interviews/interviews";

//-- Custom
import SchedulingCalendar from "../../components/calendar/schedule.calendar";
import SchedulingAside from "../../components/scheduling.aside";
import { SchedulingAsideItemFooter } from "../../components/slot/scheduling.aside.slot";

//-- SVG


//-- CSS Styles
import styles from "./CandidateInterviewsPage.module.css"




/**
 * ------------------
 * Types (utility)
 * ------------------
 */ 
type CandidateCalendarEvent = {
    interviewStatus: InterviewStatusValue
};
type ScheduledCalendartasks = Record<string, CalendarDayProps['scheduleTask']>;


/**
 * --------------
 * Components
 * ------------
 */
interface CandidateInterviewsPageProps{}

const PAGE_LIMIT = 15;



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
    const [interviewsCalendarEvent, setInterviewsCalendarEvent] = useState<CalendarEvent<CandidateCalendarEvent>[]>();

    //-- Memory Cache
    const interviewsCache = useRef<Record<string, CalendarEvent<CandidateCalendarEvent>[]>>({}); //-- key: {skip,limit, date}
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
                console.log("Event", cache[cacheKey])
                setInterviewsCalendarEvent(cache[cacheKey]);

                return;
            }

            const result = await InterviewsQueries.getInterviewAgendaForCandidate({
                skip, 
                limit: PAGE_LIMIT, 
                date: currentDate,
            });


            const promises: Promise<CalendarEvent<CandidateCalendarEvent>>[] = result.map(async (t) => {
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

                    interviewStatus: t.status,
                    badge: translateInterviewStatus(t.status),

                    members: [{
                        name: t.company.name,
                        image: t.company.logoUrl ?? "/assets/images/company-placeholder.png"
                    }],
                    durationMinutes: t.minutes
                };
            });

            const mapped: CalendarEvent<CandidateCalendarEvent>[] = await Promise.all(promises);
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
                const results = await InterviewsQueries.getCandidateCalendarPlaning({ currentMonth });
                
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
            fetchCurrentDateAgenda(pendingDate);
        }
    }, [pendingDate, fetchCurrentDateAgenda])



    /**
     * -------------------------
     * RENDER
     * -------------------------
     */

    return (
        <div className={styles.container}>
            <div className={styles.calendar}>
                <SchedulingCalendar
                    today={today}
                    currentMonth={currentMonth}
                    setCurrentMonth={setCurrentMonth}
                    onSelectedDate={(date)=>{
                        setSkip(0);
                        setPendingDate(date)
                    }}
                    taskGenerator={(currentDate)=>{
                        return scheduledTasks[[
                                currentDate.getFullYear(),
                                String(currentDate.getMonth() + 1).padStart(2, "0"),
                                String(currentDate.getDate()).padStart(2, "0"),
                            ].join("-")
                        ]
                    }}
                />
            </div>

            <SchedulingAside<CandidateCalendarEvent>
                date={pendingDate ?? today}
                events={interviewsCalendarEvent ?? []}
                onPrev={()=>{
                    setSkip((prev) => Math.max(0, prev - PAGE_LIMIT));
                }}
                onNext={()=>{
                    if(hasMore){
                        setSkip((prev)=> prev + PAGE_LIMIT);
                    }
                }}
            >
                <SchedulingAsideItemFooter>
                    {(event)=> {
                        const immutableStatus = [InterviewStatus.CLOSED];
                        const endDate = new Date(event.date.getTime() + event.durationMinutes * 60 * 1000);

                        const isNotMutable  = immutableStatus.includes(
                                (event as CalendarEvent<CandidateCalendarEvent>).interviewStatus as string
                            ) || today > endDate ;
                        
                        return (
                             isNotMutable ? null : (
                                <CandidateScheduleItemActionButtons 
                                    id={event.id}
                                    onAcceptCompleted={()=>{}}
                                    onRefuseCompleted={()=>{}}
                                />
                            )
                        )
                    }}
                </SchedulingAsideItemFooter>
            </SchedulingAside>
        </div>
    );
}
 
export default CandidateInterviewsPage;


const CandidateScheduleItemActionButtons = ({
    id,
    onAcceptCompleted,
    onRefuseCompleted
}: {
    id: string;
    onAcceptCompleted: (id: string)=>void;
    onRefuseCompleted: (id: string, data: { reason: string })=>void
}) => {
    /**
     * -------------
     * handlers
     * -------------
     */
    const acceptInterviews = useCallback(async()=>{

    },[])

    const refuseInterviews = useCallback(async()=>{

    },[])

    return (
        <div 
            key={id}
            className={styles.taskActionsButtons}
        >
            <button 
                type="button"
                className={styles.acceptBtn}
            >
                Accept
            </button>
            <button 
                type="button"
                className={styles.refuseBtn}
            >
                Refuse
            </button>
        </div>
    );
}
 
/**
 * -----------------
 * Helper
 * -----------------
 */

/**
 * Transforme interview status into
 * @param status 
 */
const translateInterviewStatus = (status: InterviewStatusValue)=>{
    switch(status){
        default:
            return ""
    }
}