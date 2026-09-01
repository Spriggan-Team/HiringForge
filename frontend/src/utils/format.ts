import { format } from "date-fns";
import type { Time } from "../features/shared/global";


/**
 * Format name
 * @param name 
 * @returns 
 */
export const getInitials = (name: string) => {
return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
};


/**
 * Format date
 */

//-- Format date
export const formatDateSafely = (dateString: string | null | undefined): string => {
    if (!dateString) return "—";
    const date = new Date(dateString);
    return isNaN(date.getTime()) ? "—" : format(date, 'dd MMMM yyyy');
};

/** Parse date safely */
export const safeParsingDate = (value: unknown): Date | null => {
    //Symfony date
    if (
        typeof value === "object" &&
        value !== null &&
        "date" in value
    ) {
        const parsedDate = new Date(
            (value as { date: string }).date
        );

        return isNaN(parsedDate.getTime())
            ? null
            : parsedDate;
    }

    if (typeof value === "string" || typeof value === "number") {
        const parsedDate = new Date(value);

        return isNaN(parsedDate.getTime())
            ? null
            : parsedDate;
    }

    if (value instanceof Date) {
        return isNaN(value.getTime()) ? null : value;
    }

    return null;
};



//-- Trandfrom date into elapsed/remaining-time time
export function formatRemainingTime(
    input: number | string | Date, 
    isCountdown: boolean = false
): string {
    const minutes = typeof input === "number" 
        ? input 
        : getMinutesFromDate(input, isCountdown);

    if (Number.isNaN(minutes)) return "";

    if (minutes <= 0) {
        return "Completed";
    }

    // 3. Calculs des jours, heures et minutes
    const days = Math.floor(minutes / (24 * 60));
    const hours = Math.floor((minutes % (24 * 60)) / 60);
    const mins = minutes % 60;

    if (days > 0) {
        let result = `${days} day${days > 1 ? "s" : ""}`;

        if (hours > 0) {
            result += ` ${hours}h`;
        }

        if (mins > 0) {
            result += ` ${mins} min`;
        }

        return result;
    }

    if (hours > 0) {
        return `${hours}h ${mins.toString().padStart(2, "0")} min`;
    }

    return `${mins} min`;
}



export const formatSalary = (salary?: {
    min?: number;
    max?: number;
    devise?: string;
}) => {
    if (!salary) return "";
    
    // console.log({salary});
    const parts = [];

    if (salary.min !== undefined && salary.min !== 0) {
        parts.push(`${salary.min}${salary.devise ?? ""}`);
    }

    if (salary.max !== undefined && salary.max !== 0) {
        parts.push(`${salary.max}${salary.devise ?? ""}`);
    }

    return parts.join(" - ");
};



//---------------------
//--- DATES
//---------------------


//-- Get elapsed time
export const getElapsedTime = (start: Time, end: Time): string => {
    const startMinutes = start.hours * 60 + start.minutes;
    const endMinutes = end.hours * 60 + end.minutes;

    const elapsed = Math.max(0, endMinutes - startMinutes);

    const hours = Math.floor(elapsed / 60);
    const minutes = elapsed % 60;

    if (hours === 0) {
        return `${minutes}min`;
    }

    if (minutes === 0) {
        return `${hours}h`;
    }

    return `${hours}h ${minutes}min`;
};




//-- delat time
export const toSeconds = ({ hours, minutes }: Time) => hours * 3600 + minutes * 60;


export const getDeltaSecondeTime = (start: Time, end: Time): number => {
    return Math.max(0, toSeconds(end) - toSeconds(start));
};

//-----------
//-- Helpers
//-----------
export function getMinutesFromDate(date: string | Date, isCountdown: boolean = false): number {
    const targetTime = new Date(date).getTime();
    if (Number.isNaN(targetTime)) return NaN;

    const now = Date.now();
    const diffMs = isCountdown ? targetTime - now : now - targetTime;

    return Math.floor(diffMs / (1000 * 60));
}


/** Trun a minutes number into readable time data */
export const formatMinutesIntoTime = (minutes: number): string => {
  if (isNaN(minutes) || minutes < 0) return '0m';

  const hrs = Math.floor(minutes / 60);
  const mins = minutes % 60;

  if (hrs === 0) {
    return `${mins}m`;
  }

  const paddedMins = mins.toString().padStart(2, '0');

  if (mins === 0) {
    return `${hrs}h`;
  }

  return `${hrs}h ${paddedMins}m`;
};