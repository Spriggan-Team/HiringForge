
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

