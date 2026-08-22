

export const getDate = (startOfWeek: Date, offset: number) => {
    const d = new Date(startOfWeek);
    d.setDate(startOfWeek.getDate() + offset);
    return d;
};


const currentYear = new Date().getFullYear();

export const currentMonth = Array.from({ length: 12 }, (_, index) => 
    new Date(currentYear, index, 1)
);

const today = new Date();

export const currentDay = Array.from({ length: 7 }, (_, index) => {
    const day = new Date(today);
    day.setDate(today.getDate() - today.getDay() + index);

    return day;
});