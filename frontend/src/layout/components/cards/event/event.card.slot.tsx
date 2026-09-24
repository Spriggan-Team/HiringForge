import React from "react"
import type { SlotComponentProps } from "../../../../features/shared/global";


/**
 * Card Actions buttons
 */


const EventCardActions: React.FC<SlotComponentProps> = ({
    children,
}) => {
    return <>{children}</>;
};


/**
 * Card status
 */
export const EventCardBadge: React.FC<SlotComponentProps> = ({children})=>{
    return (<>{children}</>)
}


export  { EventCardActions };