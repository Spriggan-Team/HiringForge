
import React, {  useCallback, useEffect, useRef, useState } from "react"
import { format, startOfToday } from "date-fns";

//-- Services & types
import InterviewsQueries from "../../../../api/services/interviews/queries";
import type { CalendarEvent } from "../../../../features/planning/planning";
import { evalInterviewRate, formatInterviewsTitle, mapInterviewsStatusIntoCalendarEventCSSFlag, translateInterviewStatus } from "../../utils/format";
import type { CalendarDayProps } from "../../../../layout/components/cards/calendar/CalendarDay";
import { IMMUTABLE_INTERVIEW_STATUS, InterviewStatus, type ComputedInterviewsStatus, type InterviewStatusValue } from "../../../../features/interviews/interviews";
import { useAppContext } from "../../../../hooks/context";
import InterviewsServices from "../../../../api/services/interviews/command";

//-- Custom
import SchedulingCalendar from "../../components/calendar/schedule.calendar";
import SchedulingAside from "../../components/scheduling.aside";
import { SchedulingAsideItemBadge, SchedulingAsideItemFooter } from "../../components/slot/scheduling.aside.slot";
import InfoPill from "../../../../layout/components/badges/pill/info.pill";
import RejectEventForm from "../../../components/rejectForm/reject.event.form";
import CollapsibleDescriptionText from "../../../../layout/components/text/collapsible/collapsible.description.text";
import CandidateScheduleItemActionButtons from "../../components/actions/scheduling.aside.action.buttons";

//-- SVG


//-- CSS Styles
import styles from "./CandidateInterviewsPage.module.css"



/**
 * ------------------
 * Types (utility)
 * ------------------
 */ 
type CandidateCalendarEvent = {
    rejectionReason?: string;
    candidateApproval?: boolean;
    interviewStatus: | ComputedInterviewsStatus | InterviewStatusValue; // status & computed status
};

export type CandidateCalendarEventItem = CalendarEvent<CandidateCalendarEvent>;
type ScheduledCalendartasks = Record<string, CalendarDayProps['scheduleTask']>;


/**
 * --------------
 * Components
 * ------------
 */
interface CandidateInterviewsPageProps{}

const PAGE_LIMIT = 15;
const now = new Date();



const CandidateInterviewsPage: React.FC<CandidateInterviewsPageProps> = ({}) => {
    const today = startOfToday();
    const { setLoading, setPopup, setModal } = useAppContext();

    //-- Pagination
    const [skip, setSkip] = useState(0);
    const [hasMore, setHasMore] = useState(false);

    //-- Days
    const [currentMonth, setCurrentMonth] = useState(today);            //-- current month
    const [pendingDate, setPendingDate] = useState<Date | null>(null);  //-- Selected date

    //-- Interviews
    const [scheduledTasks, setScheduledTasks] = useState<ScheduledCalendartasks>({}); // key: Y-m-d
    const [interviewsCalendarEvent, setInterviewsCalendarEvent] = useState<CandidateCalendarEventItem[]>([]);

    //-- Memory Cache
    const interviewsCache = useRef<Record<string, CandidateCalendarEventItem[]>>({}); //-- key: {skip,limit, date}
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
                setInterviewsCalendarEvent(cache[cacheKey]);
                return;
            }

            const result = await InterviewsQueries.getInterviewAgendaForCandidate({
                skip, 
                limit: PAGE_LIMIT, 
                date: currentDate,
            });


            const promises: Promise<CandidateCalendarEventItem>[] = result.map(async (t) => {
                const schedultedAt = new Date(t.startDate);
                const endDate = new Date(schedultedAt.getTime() + t.minutes * 60 * 1000) ;

                const computedStatus =  t.candidateApproval != null 
                                ? t.candidateApproval ?
                                    "Accepted"  
                                    : "Rejeted" 
                                : t.status

                
                const computedRate = t.candidateApproval != null ? 'normal' : evalInterviewRate({
                    interview: {
                        startDate: t.startDate,
                        minutes: t.minutes
                    },
                    now: now
                })

                return {
                    id: t.id,
                    title: formatInterviewsTitle({
                        title: t.title,
                        type: t.type
                    }),
                    url: t.url,
                    type: t.type,

                    date: schedultedAt,
                    endDate: endDate,

                    note: t.description ?? "",
                    candidateApproval: t.candidateApproval,
                    
                    rate: computedRate,
                    links: t.url ? [{ url: t.url, isActive: now <= endDate  }]: null,

                    interviewStatus: computedStatus,
                    badge: {
                        text:  translateInterviewStatus(computedStatus),
                        flag: mapInterviewsStatusIntoCalendarEventCSSFlag(computedStatus)
                    },

                    members: [{
                        name: t.company.name,
                        image: t.company.logoUrl ?? "/assets/images/company-placeholder.png"
                    }],

                    durationMinutes: t.minutes
                };
            });

            const mapped: CalendarEvent<CandidateCalendarEvent>[] = await Promise.all(promises);
            console.log("Interview Agenda result", mapped);

            cache[cacheKey] = mapped;

            setInterviewsCalendarEvent(mapped);
            //-- Determine has more
            setHasMore(result.length === PAGE_LIMIT);
        }
        catch(error){
            console.warn("Something went wrong when fetching user agenda: ", error)
        }
    }, [skip]);


    const handleUpdateCurrentInterviewsEvent = useCallback((event: CandidateCalendarEventItem, update:  Partial<Omit<CandidateCalendarEventItem, "id">>)=>{
        if(!pendingDate) return;
        const updatedItem = {
            ...event,
            ...update,
        };

        //-- Update cache
        const cache = interviewsCache.current;
        const cacheKey = JSON.stringify({ skip, limit: PAGE_LIMIT, date: format(pendingDate, "yyyy-MM-dd") });

        if (cache[cacheKey]) {
            cache[cacheKey] = cache[cacheKey].map((item) =>
                item.id === event.id ? updatedItem : item
            );
        }

        //-- Sate update
        setInterviewsCalendarEvent((prev) => {
            return prev.map((item) => {
                if (item.id === event.id) {
                    return updatedItem;
                }
                return item;
            });
        });
    },[skip, pendingDate]);


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
                <SchedulingAsideItemFooter<CandidateCalendarEventItem>>
                    {(event)=> {
                        /**
                         * ------------------
                         * Accept/Refuse handlers
                         * -------------------
                         */
                        const handleRefusal = async (reason: string)=>{
                            try{
                                setLoading({ state: true });
                                await InterviewsServices.refuse({
                                    id: event.id,
                                    reason
                                });
                                
                                setLoading({ state: false });
                                handleUpdateCurrentInterviewsEvent(event, { 
                                    candidateApproval: false, rejectionReason: reason, 
                                    badge: {
                                        text:  translateInterviewStatus("Rejeted"),
                                        flag: mapInterviewsStatusIntoCalendarEventCSSFlag("Rejeted")
                                    },
                                    interviewStatus: "Rejeted"
                                });

                                setModal(null); 
                                setPopup({ status: 'success', message: "Opération executé avec succès" })
                            }
                            catch(error){
                                setLoading({ state: false });
                                console.log("Something went wrong while refusing interview ", error);
                                setPopup({ status: 'error', message: 'Une erreur innatendue est survenue' })
                            }
                        }

                        const handleAccept = async()=>{
                            try{
                                setLoading({ state: true });
                                await InterviewsServices.accept({id: event.id});

                                setLoading({ state: false });
                                handleUpdateCurrentInterviewsEvent(event, { 
                                    candidateApproval: true,
                                    badge: {
                                        text: translateInterviewStatus("Accepted"),
                                        flag: mapInterviewsStatusIntoCalendarEventCSSFlag("Accepted")
                                    },
                                    interviewStatus: "Accepted"
                                })
                            }
                            catch(error){
                                setLoading({ state: false });
                                console.log("Something went wrong while accepting interview ", error);
                                setPopup({ status: 'error', message: 'Une erreur innatendue est survenue' })

                            }
                        }

                        /**
                         * ----------
                         * Rendering
                         * ---------
                         */
                        const isNotMutable  =  IMMUTABLE_INTERVIEW_STATUS.includes(
                               event.interviewStatus as string
                            ) || now >= event.endDate || event.candidateApproval != null;

                        return (
                             isNotMutable ? 
                                event.rejectionReason && (
                                    <CollapsibleDescriptionText text={event.rejectionReason} />
                                ) 
                                : (
                                    <CandidateScheduleItemActionButtons
                                        id={event.id}
                                        onRefuse={()=>{
                                            setModal({
                                                isOpen: true,
                                                title: "Reject Event",
                                                content: () => (
                                                    <RejectEventForm
                                                        onConfirm={handleRefusal}
                                                        placeholder="message (optionnel)"
                                                    />
                                                )
                                            });
                                        }}
                                        onAcceptCompleted={handleAccept}
                                    />
                                )
                        )
                    }}
                </SchedulingAsideItemFooter>
                <SchedulingAsideItemBadge<CalendarEvent<CandidateCalendarEvent>>>
                    {(event)=>{
                        console.log("Scheduling aside item : ", event);
                        const isVisible =  IMMUTABLE_INTERVIEW_STATUS.includes(
                                event.interviewStatus as string
                            ) || event.endDate <= now;

                        return (
                            isVisible ? (
                                <InfoPill 
                                    className={styles.infoPill}
                                    text={event.badge?.text as string}
                                />
                            ) : null
                        )
                    }}
                </SchedulingAsideItemBadge>
            </SchedulingAside>
        </div>
    );
}
 
export default CandidateInterviewsPage;

