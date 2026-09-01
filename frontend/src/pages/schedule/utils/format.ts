import { InterviewType } from "../../../features/interviews/interviews";
import type { TaskRate } from "../../../features/planning/planning";



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

    const minutesUntilStart =
        (startTime - nowTime) / (1000 * 60);

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