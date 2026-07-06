import type { JobStatus } from "../features/jobs/JobOffer";
import type { TaskRate } from "../features/planning/planning";


export const TaskStyleConfig: Record<TaskRate, string> = {
    low:      "var(--calendar-task-low)",
    normal:   "var(--calendar-task-normal)",
    medium:   "var(--calendar-task-medium)",
    high:     "var(--calendar-task-high)",
    urgent:   "var(--calendar-task-urgent)",

    success:  "var(--calendar-task-success)",
    warning:  "var(--calendar-task-warning)",
    danger:   "var(--calendar-task-danger)",
};


export const jobStatusStyles: Record<JobStatus, { bgColor: string; txtColor: string }> = {
    active: {
        txtColor: "--status-active-text",
        bgColor: "--status-active-bg",
    },

    pending: {
        txtColor: "--status-pending-text",
        bgColor: "--status-pending-bg",
    },

    published: {
        txtColor: "--status-published-text",
        bgColor: "--status-published-bg",
    },

    draft: {
        txtColor: "--status-draft-text",
        bgColor: "--status-draft-bg",
    },

    closed: {
        txtColor: "--status-closed-text",
        bgColor: "--status-closed-bg",
    },
};