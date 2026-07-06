import type { Time } from "../shared/time";

export interface CalendarEvent{
    title: string;
    date: Date;
    type: CalendarEventType;
    note: string;
    rate: TaskRate;
    members: string[];
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