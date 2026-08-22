
import type { CalendarEvent } from "../../features/planning/planning";



export const mockCalendarEvents: CalendarEvent[] = [
    {
        title: "Phone Screening - Emma Wilson",
        date: new Date(2026, 6, 8),
        type: "screening",
        note: "First contact to validate the candidate's background and expectations.",
        rate: "normal",
        members: ["Emma Wilson"],
        time: {
            start: { hours: 9, minutes: 0 },
            end: { hours: 9, minutes: 30 },
        },
    },

    {
        title: "HR Interview - Lucas Martin",
        date: new Date(2026, 6, 8),
        type: "hr-interview",
        note: "Discuss motivation, salary expectations and availability.",
        rate: "medium",
        members: ["Lucas Martin", "Sophia Brown"],
        time: {
            start: { hours: 10, minutes: 30 },
            end: { hours: 11, minutes: 15 },
        },
    },

    {
        title: "Technical Interview - Olivia Smith",
        date: new Date(2026, 6, 9),
        type: "interview",
        note: "React, TypeScript and architecture discussion.",
        rate: "high",
        members: ["Olivia Smith", "Lead Frontend"],
        time: {
            start: { hours: 14, minutes: 0 },
            end: { hours: 15, minutes: 30 },
        },
    },

    {
        title: "Technical Assessment",
        date: new Date(2026, 6, 10),
        type: "assesment",
        note: "Complete the coding challenge before Friday.",
        rate: "warning",
        members: ["Nathan Clark"],
        time: {
            start: { hours: 9, minutes: 30 },
        },
    },

    {
        title: "Candidate Review",
        date: new Date(2026, 6, 10),
        type: "review",
        note: "Collect interview feedback and decide next steps.",
        rate: "medium",
        members: [
            "HR Team",
            "Engineering Manager",
            "Lead Frontend",
        ],
        time: {
            start: { hours: 16, minutes: 0 },
            end: { hours: 17, minutes: 0 },
        },
    },

    {
        title: "Send Interview Invitation",
        date: new Date(2026, 6, 11),
        type: "task",
        note: "Invite shortlisted candidates for the next interview.",
        rate: "low",
        members: ["Recruiter"],
        time: {
            start: { hours: 8, minutes: 45 },
        },
    },

    {
        title: "Reference Check",
        date: new Date(2026, 6, 14),
        type: "task",
        note: "Contact previous employer for reference validation.",
        rate: "normal",
        members: ["HR"],
        time: {
            start: { hours: 11, minutes: 0 },
            end: { hours: 11, minutes: 45 },
        },
    },

    {
        title: "Final Technical Interview",
        date: new Date(2026, 6, 15),
        type: "interview",
        note: "Final discussion with the CTO.",
        rate: "urgent",
        members: [
            "CTO",
            "Senior Developer",
            "Candidate",
        ],
        time: {
            start: { hours: 15, minutes: 0 },
            end: { hours: 16, minutes: 30 },
        },
    },

    {
        title: "Prepare Employment Offer",
        date: new Date(2026, 6, 16),
        type: "task",
        note: "Prepare contract and compensation package.",
        rate: "success",
        members: ["HR", "Finance"],
        time: {
            start: { hours: 10, minutes: 0 },
        },
    },

    {
        title: "Missing Candidate Documents",
        date: new Date(2026, 6, 17),
        type: "task",
        note: "Request missing identity and diploma documents.",
        rate: "danger",
        members: ["Recruiter"],
        time: {
            start: { hours: 9, minutes: 15 },
        },
    },
];