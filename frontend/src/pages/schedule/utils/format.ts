import { InterviewStatus, InterviewType, type ComputedInterviewsStatus, type InterviewStatusValue } from "../../../features/interviews/interviews";

import type { CalendarEventBadgeCSSFlag, TaskRate } from "../../../features/planning/planning";


export const formatInterviewsTitle = ({title, type}: {title?: string, type?: string})=>{
    if(title) 
        return title;
    if(type){
        switch(type){
            case InterviewType.RH_INTERVIEWS:
                return 'Entretien RH'
            case InterviewType.TECHNICAL_INTERVIEWS:
                return "Entretien Technique"
            default:
                return "Entretien"
        }
    }
    return 'Entretien'
}



/**
 * This function helps calculating/evaluating interview rate (flag)
 * depending on the shcedule date, time & current date
 *  - "urgent"  : the interview is  currently running
 *  - "success" : the end time is exceed
 *  - "danger"  : 15 minutes before start
 *  - "warning" : one hour before start
 *  - "high"    : it is plan for today
 *  - "medium"  : it start tomorrow
 *  - "normal"  : it is planned with this week
 *  - "low"     : any other state
 * @param param0 
 * @returns 
 */
export function evalInterviewRate({
    interview,
    now =  new Date()
}: {
    interview: {
        startDate: string;
        minutes: number
    },
    now?: Date 
}
): TaskRate {
    const startDate = new Date(interview.startDate);

    const endDate = new Date(
        startDate.getTime() + interview.minutes * 60 * 1000
    );

    const nowTime = now.getTime();
    const startTime = startDate.getTime();
    const endTime = endDate.getTime();

    const minutesUntilStart = (startTime - nowTime) / (1000 * 60);

    // Interview is currently running
    if (nowTime >= startTime && nowTime < endTime) {
        return "urgent";
    }

    // Interview duration has elapsed
    if (nowTime >= endTime) {
        return "success";
    }

    // Starts in less than 15 minutes
    if (minutesUntilStart <= 15) {
        return "danger";
    }

    // Starts in less than one hour
    if (minutesUntilStart <= 60) {
        return "warning";
    }

    // Starts today
    if (isSameDay(startDate, now)) {
        return "high";
    }

    // Starts tomorrow
    if (isTomorrow(startDate, now)) {
        return "medium";
    }

    // Starts within the next 7 days
    if (minutesUntilStart <= 7 * 24 * 60) {
        return "normal";
    }

    return "low";
}


function isSameDay(date1: Date, date2: Date): boolean {
    return (
        date1.getFullYear() === date2.getFullYear() &&
        date1.getMonth() === date2.getMonth() &&
        date1.getDate() === date2.getDate()
    );
}


function isTomorrow(date: Date, now: Date): boolean {
    const tomorrow = new Date(now);

    tomorrow.setDate(now.getDate() + 1);

    return isSameDay(date, tomorrow);
}




/**
 * Transforme interview status into
 * @param status 
 */
export const translateInterviewStatus = (status: InterviewStatusValue | ComputedInterviewsStatus)=>{
    switch(status){
        case "Accepted":
            return "Accepté"
        case "Rejeted":
            return "Rejeted"
        case InterviewStatus.SCHEDULED:
            return "Programmé"
        case InterviewStatus.CLOSED:
            return "Closed"
        case InterviewStatus.COMPLETED:
            return "InterviewStatus"
        case InterviewStatus.COMPLETED:
            return "Completed"
        case InterviewStatus.IN_PROGRESS:
            return "Progess"
        case InterviewStatus.MISSED:
            return "Missed"
        default:
            return "Unknown"
    }
}

export const mapInterviewsStatusIntoCalendarEventCSSFlag = (status: InterviewStatusValue | ComputedInterviewsStatus): CalendarEventBadgeCSSFlag  =>{
    switch(status){
        case "Accepted":
            return "accepted"
        case "Rejeted":
            return 'refused'
        case InterviewStatus.SCHEDULED:
            return 'normal'
        case InterviewStatus.CLOSED:
            return 'close'
        case InterviewStatus.COMPLETED:
            return 'normal'
        case InterviewStatus.IN_PROGRESS:
            return 'in_progess'
        case InterviewStatus.MISSED:
            return 'missed'
        default:
            return ""
    }
}