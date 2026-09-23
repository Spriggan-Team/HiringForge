import type { InterviewTypeValue } from "../interviews/interviews";
import type { Time } from "../shared/global";

export type CalendarEvent<T = {}> = T & {
    id: string;
    title: string;
    date: Date;
    type?: InterviewTypeValue;
    note?: string;
    rate: TaskRate;
    status?: string; //-- text for status, ect...
    members: {name: string, image?: string | null}[]; //string[]: name[] -> array of name
    durationMinutes: number;
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
    durationMinutes: 0
}