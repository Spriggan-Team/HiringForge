

export const getDate = (startOfWeek: Date, offset: number) => {
    const d = new Date(startOfWeek);
    d.setDate(startOfWeek.getDate() + offset);
    return d;
};
