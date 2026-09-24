import { type FC, type ReactNode } from "react"
// Time

export interface Time{
    hours: number ; minutes: number
}


export interface TimeRange {
  startTime: Time;
  endTime: Time;
}

//------------------
//-----
//------------------

export interface Location{
    id?: string,
    city: string;
    street: string;
    country: string;
    postalCode: string;
    visibilityRange?: number;
}


export interface Language {
    id: number;
    code: string;
    label?: string;
}


export interface Skill{
    id: string;
    name: string;
}

export type EntityAction =  "edit" | "duplicate" | "delete" | null



//---------------------------------------
// FRONTEND / API & SHARED TYPES - UI
//---------------------------------------

/**
 * Shared types used across multiple layers of the application,
 * such as the frontend, API, and other shared modules.
 *
 * These types define common contracts to ensure consistency
 * and type safety between different parts of the system.
 */


export interface FilterState {
    publishedState: JobPublishedState;
    offerState: JobOfferState;
    salary: number;
    candidateCount: number;
    searchText: string;
    searchAddress: string;
}

export interface SymfonyDateTime {
  date: string;          // ex: "2026-08-26 18:24:38.000000"
  timezone: string;      // ex: "UTC"
  timezone_type: number; // ex: 3
}

export interface JobPublishedState {
    draft: boolean;
    closed: boolean;
    published: boolean;
}

export interface JobOfferState {
    active: boolean;
    pending: boolean;
    inactive: boolean;
}


//--------------------------
//-- UI & Services only
//--------------------------

//-- Seacrh

export interface CandidateSearchItem extends AutoCompleteSearchResultItem {
  email: string;
  firstName: string;
  applicationId: string;
  candidateId: string;
  jobTitle: string;
};


export interface AutoCompleteSearchResultItem {
  id: string;
  image?: string;
  label: string;
  sublabel?: string;
  [key: string]: unknown; 
}

//-- component
export type SlotComponentProps = {
    children: ReactNode;
};

export type EventSlotComponentProps<T = {}> = {
    children: (event: T) => ReactNode;
};

//----------------------------
//------ API Only
//----------------------------

export interface PendingRequest<T> {
    promise: Promise<T>;
    signal?: AbortSignal;
}