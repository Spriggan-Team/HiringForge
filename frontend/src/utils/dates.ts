import type { Timeframe } from "../api/services/shared/reponses.types";
import type { TimeRange } from "../features/shared/global";


const currentYear = new Date().getFullYear();

//-- Return twelve month of a year
export const currentMonth = Array.from({ length: 12 }, (_, index) => 
    new Date(currentYear, index, 1)
);

const today = new Date();


//returns seven days of the weeks
export const currentDays = Array.from({ length: 7 }, (_, index) => {
    const day = new Date(today);
    day.setDate(today.getDate() - today.getDay() + index);

    return day;
});


/**
 * Get Date with offeser number
 * @param startOfWeek 
 * @param offset 
 * @returns 
 */
export const getDate = (startOfWeek: Date, offset: number) => {
    const d = new Date(startOfWeek);
    d.setDate(startOfWeek.getDate() + offset);
    return d;
};




/**
 * Nav between weeks, months or years
 *  date: starting point of naviagtion
 *  amount: amount of steps to navigate
 *  unit: how much a single navigation can really be (ex: 1 -> one week , 2 -> weeks ...)
 */
export const shiftDate = (
    date: Date,
    amount: number,
    unit: 'day' | 'week' | 'month' | 'year'
): Date => {
    const nextDate = new Date(date);

    switch (unit) {
        case 'day':
            nextDate.setDate(nextDate.getDate() + amount);
            break;

        case 'week':
            nextDate.setDate(nextDate.getDate() + amount * 7);
            break;

        case 'month':
            nextDate.setMonth(nextDate.getMonth() + amount);
            break;

        case 'year':
            nextDate.setFullYear(nextDate.getFullYear() + amount);
            break;
    }

    return nextDate;
};


/**
 * Parse string data into date
 * @param value 
 * @returns 
 */
export const parseDate = (value: string): Date | null => {
    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return null;
    }

    return date;
};


/**
 * Handle user date string input to iso
 * use for handling state value
 * @param value 
 * @param setValue 
 * @returns 
 */
export const formatDateInputValue  = (e: React.ChangeEvent<HTMLInputElement>, callback: (param: string)=> void)=>{
 const inputValue = e.target.value;

  // keep : JJMMAAAA
  const digits = inputValue.replace(/\D/g, '').slice(0, 8);

  // Detect backspace
  const isDeleting = (e.nativeEvent as InputEvent).inputType === 'deleteContentBackward';

  //-- Building
  let formatted = '';

  if (digits.length > 0) {
    // Day (JJ)
    formatted = digits.slice(0, 2);

    // 1st "/" if value > 2 number
    if (digits.length > 2 || (digits.length === 2 && !isDeleting)) {
      formatted += '/' + digits.slice(2, 4);
    }

    // 2nd "/" if moe than 4 digit
    if (digits.length > 4 || (digits.length === 4 && !isDeleting)) {
      formatted += '/' + digits.slice(4, 8);
    }
  }

  callback(formatted);
}



/**
 * Sort date by timeframe
 * the first inserted date is the representative of
 * the over with its timeframe.
 * @param date 
 * @param timeframe - week|month
 * @returns 
 */
export const getTimeframeCacheKey = (
    date: Date,
    timeframe: Timeframe
): string => {
    switch (timeframe) {
        case 'week': {
            const startOfWeek = new Date(date);

            const day = startOfWeek.getDay();
            const diff = day === 0 ? -6 : 1 - day;

            startOfWeek.setDate(startOfWeek.getDate() + diff);

            return `week-${startOfWeek.getFullYear()}-${String(
                startOfWeek.getMonth() + 1
            ).padStart(2, '0')}-${String(
                startOfWeek.getDate()
            ).padStart(2, '0')}`;
        }

        case 'month':
            return `month-${date.getFullYear()}-${String(
                date.getMonth() + 1
            ).padStart(2, '0')}`;

        case 'year':
            return `year-${date.getFullYear()}`;

        default:
            throw new Error(`Unsupported timeframe: ${timeframe}`);
    }
};



export const parseFrenchDate = (dateString: string): Date | null => {
  // Verify french type
  if (!/^\d{2}\/\d{2}\/\d{4}$/.test(dateString)) {
    return null;
  }

  //-- parts
  const [dayStr, monthStr, yearStr] = dateString.split('/');
  const day = parseInt(dayStr, 10);
  const month = parseInt(monthStr, 10) - 1; // ⚠️ Mois indexé de 0 à 11
  const year = parseInt(yearStr, 10);

  const date = new Date(year, month, day);

  // Validation
  if (
    date.getFullYear() !== year ||
    date.getMonth() !== month ||
    date.getDate() !== day
  ) {
    return null; // Invalid
  }

  return date;
};



/**
 * Convert date + minutes into time range data
 * - date + minutes -> start: time, end: time
 */
export const calculateTimeRange = (
  startDate: string | Date,
  minutes: number
): TimeRange => {
  const start = new Date(startDate);

  if (Number.isNaN(start.getTime())) {
    throw new Error("Invalid start date.");
  }

  if (minutes < 0) {
    throw new Error("Duration cannot be negative.");
  }

  const end = new Date(
    start.getTime() + minutes * 60 * 1000
  );

  return {
    startTime: {
      hours: start.getHours(),
      minutes: start.getMinutes(),
    },
    endTime: {
      hours: end.getHours(),
      minutes: end.getMinutes(),
    },
  };
};


/**
 * Calculate date progression 
 * @returns 
 */
export const calculateProgress = (
    startDate: string | Date,
    durationMinutes: number,
): number => {
    const start = new Date(startDate);

    if (Number.isNaN(start.getTime())) {
        return 0;
    }

    const now = new Date();
    const end = new Date(start.getTime() + durationMinutes * 60 * 1000);

    const total = end.getTime() - start.getTime();
    const elapsed = now.getTime() - start.getTime();

    if (total <= 0) {
        return 0;
    }

    return Math.min(
        1,
        Math.max(0, elapsed / total)
    );
};



/**
 * Checks whether the date corresponds to a calendar day
 * strictly after the reference day (tomorrow or later).
 *
 * The time of day is ignored when comparing the dates.
 */
export const isFuturDay = ({
    date,
    today: t
}:{
    date:Date,
    today?: Date
}): boolean => {
    const today =  t ?? new Date();
    today.setHours(0, 0, 0, 0);

    const targetDate = new Date(date);
    targetDate.setHours(0, 0, 0, 0);

    return targetDate > today;
}