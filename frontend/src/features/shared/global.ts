
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
    id: string;
    code: string;
    label?: string;
}


export type EntityAction =  "edit" | "duplicate" | "delete" | null