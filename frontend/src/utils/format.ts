
//format minutes to hours - min ...
export function formatRemainingTime(minutes: number): string {
    if(Number.isNaN(minutes)) return "";

    if (minutes <= 0) {
        return "Completed";
    }

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
    console.log({salary});
    const parts = [];

    if (salary.min !== undefined && salary.min !== 0) {
        parts.push(`${salary.min}${salary.devise ?? ""}`);
    }

    if (salary.max !== undefined && salary.max !== 0) {
        parts.push(`${salary.max}${salary.devise ?? ""}`);
    }

    return parts.join(" - ");
};