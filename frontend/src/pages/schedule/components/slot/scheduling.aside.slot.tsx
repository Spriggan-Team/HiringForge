import react from 'react'

/**
 * -------------------------
 * Compoenent
 * ------------------------
 */

import type { CalendarEvent } from "../../../../features/planning/planning";

export type BasicSlotComponent = {
    children: React.ReactNode;
}


export const SchedulingAsideHeader: React.FC<BasicSlotComponent> = ({ children }) => <>{children}</>;


export type SchedulingAsideSlotProps<T = {}> = {
    children?: ((event: CalendarEvent<T>) => React.ReactNode);
};

export const SchedulingAsideItemFooter = <T,>({
    children,
}: SchedulingAsideSlotProps<T>) => {
    return <>{children}</>;
};
