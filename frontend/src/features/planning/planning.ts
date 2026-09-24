import type { InterviewTypeValue } from "../interviews/interviews";
import type { Time } from "../shared/global";



export type CalendarEvent<T = {}> = T & {
    id: string;
    title: string;
    type?: InterviewTypeValue;
    
    date: Date;
    endDate: Date;
    durationMinutes: number;

    note?: string;
    rate: TaskRate;
    badge?: {
        text: string
        flag: CalendarEventBadgeCSSFlag 
    }; //-- text for status, ect...

    links?: {url?: string; isActive?: boolean}[] | null;
    members: {name: string, image?: string | null}[]; //string[]: name[] -> array of name
} 

export type CalendarEventBadgeCSSFlag =  'accepted' | 'refused'  | 'normal' | 'missed' | 'in_progess' | 'close' | '';


export type TaskRate =
    | "low"
    | "normal"
    | "medium"
    | "high"
    | "urgent"
    | "success"
    | "warning"
    | "danger";

export type CalendarEventType =
    | "interview"
    | "hr-interview"
    | "screening"
    | "assesment"
    | "review"
    | "task"
    | undefined


export const INITIAL_CALENDAR_EVENT_VIEW: CalendarEvent = {
    id: "",
    title: "",
    date: new Date(),
    endDate: new Date(),
    type: undefined,
    note: "string",
    rate: "normal",
    members: [],
    durationMinutes: 0,
    links: [],
}