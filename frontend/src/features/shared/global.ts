
export interface Time{
    hours: number ; minutes: number
}

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



//----------------------------
// FRONTEND / API & SHARED TYPES
//----------------------------

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

