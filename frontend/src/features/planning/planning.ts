import type { InterviewTypeValue } from "../interviews/interviews";
import type { Time } from "../shared/global";

export interface CalendarEvent{
    id: string;
    title: string;
    date: Date;
    type?: InterviewTypeValue;
    note?: string;
    rate: TaskRate;
    members: {name: string, image?: string | null}[]; //string[]: name[] -> array of name
    time: {
        start: Time,
        end?: Time
    }
}



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
    type: undefined,
    note: "string",
    rate: "normal",
    members: [],
    time: {
        start:{
            hours: 0,
            minutes: 0
        },
        end: undefined
    }
}