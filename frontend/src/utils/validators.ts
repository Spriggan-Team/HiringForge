import type { JobView } from "../features/jobs/JobOffer";


export const validateSalary = (
    currentJob: JobView,
    maxSalaryValidationError?: () => void,
    minSalaryValidationError?: () => void,
): boolean => {

    const min = currentJob.salary?.min;
    const max = currentJob.salary?.max;

    const hasMin =
        min !== undefined &&
        min !== null &&
        min > 0;

    const hasMax =
        max !== undefined &&
        max !== null &&
        max > 0;

    // Minimum supérieur au maximum
    if (hasMin && hasMax && min > max) {
        minSalaryValidationError?.();
        return false;
    }

    // Maximum inférieur au minimum
    if (hasMin && hasMax && max < min) {
        maxSalaryValidationError?.();
        return false;
    }


    return true;
};