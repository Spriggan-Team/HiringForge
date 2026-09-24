import type { EventSlotComponentProps, SlotComponentProps,   } from '../../../../features/shared/global';


/**
 * -------------------------
 * Compoenent
 * ------------------------
 */


/**
 * Header 
 */
export const SchedulingAsideHeader: React.FC<SlotComponentProps> = ({ children }) => <>{children}</>;

/**
 * ------------------
 * Children footer 
 * ----------------
 */


export const SchedulingAsideItemFooter = <T,>({
    children,
}: EventSlotComponentProps<T>) => {
    return <>{children}</>;
};



export const SchedulingAsideItemBadge  = <T,>({children}: EventSlotComponentProps<T>)=>{
    return (
        <>{children}</>
    )
}