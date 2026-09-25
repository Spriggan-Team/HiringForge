

import { 
    format
} from "date-fns";
import { forwardRef, useCallback, useEffect, useImperativeHandle, useRef, useState } from "react";

//-- Services & Types
import type { TaskRate } from "../../../../features/planning/planning";

//-- CSS Styles
import styles from "./CalendarDay.module.css"
import { TaskStyleConfig } from "../../../../context/styles";



//** TYPES */

export interface CalendarHandleContext {
    requestClose: (visibility: boolean) => void;
}


export interface TaskData {
    task: string;
    rate?: TaskRate;
}


export interface CalendarDayProps {
    date: Date;
    scheduleTask: TaskData[];

    onClick?: (date: Date) => void;
    disable?: boolean;
    disableStyle?: boolean;
    disableOnClick?: boolean;

    style?: React.CSSProperties;
    className?: string;
    children?: React.ReactNode;
}

/* -------------------------------------------------------------------------- */

const CalendarDay = forwardRef<CalendarHandleContext, CalendarDayProps>(
    (
        {
            date,
            scheduleTask,
            
            onClick,
            disable,
            disableOnClick,
            disableStyle,

            style,
            children,
            className,
        },
        ref,
    ) => {
        const popoverRef = useRef<HTMLDivElement>(null);

        const [popoverVisibility, setPopoverVisibility] = useState(false);

        /*-------------- Helpers ----------- */

        const requestClose = useCallback(() => {
            setPopoverVisibility(false);
        }, []);

        
        /*-------- Outside click / Escape -------  */


        useEffect(() => {
            if (!children || !popoverVisibility) {
                return;
            }

            const handleMouseOutside = (e: MouseEvent) => {
                if (
                    popoverRef.current &&
                    !popoverRef.current.contains(e.target as Node)
                ) {
                    requestClose();
                }
            };

            const handleKeyDown = (e: KeyboardEvent) => {
                if (e.key === "Escape") {
                    requestClose();
                }
            };

            window.addEventListener("mousedown", handleMouseOutside);
            window.addEventListener("keydown", handleKeyDown);

            return () => {
                window.removeEventListener("mousedown", handleMouseOutside);
                window.removeEventListener("keydown", handleKeyDown);
            };
        }, [children, popoverVisibility, requestClose]);


        /* ------  Ref API --------  */

        useImperativeHandle(ref, () => ({
            requestClose,
        }), [requestClose]);

        /* -------- Render --------- */

        const isClickDisabled = disableOnClick ?? disable;
        const isVisuallyDisabled = disableStyle ?? disable;

        return (
            <div
                style={{
                    ...style,
                    cursor:
                        !(isClickDisabled) && (onClick || children)
                            ? "pointer"
                            : undefined,
                }}
                onClick={() => {
                    if(isClickDisabled) 
                        return;
                    
                    onClick?.(date);
                    if (children && !popoverVisibility) {
                        setPopoverVisibility(true);
                    }
                }}
                className={`${styles.day} ${className ?? ""}  ${isVisuallyDisabled ? styles.disabled : ""} card` }
            >
                {/*  PRINCIPAL CONTENT (FRONT)  - Main */}
                <div
                    style={
                        !style && !className ? {
                            ['--color' as string]: "#475569"
                        }: 
                        undefined
                    }
                    className={`${styles.principal} ${!isClickDisabled ? styles.clickable : ""}`}
                >
                    <span className={styles.title}>
                        {format(date, "dd")}
                    </span>

                    <div className={styles.taskSection}>
                        {scheduleTask.map((item, index) => (
                            <div
                                key={index}
                                className={styles.task}
                                style={{
                                    ["--task-rate-color" as string]:
                                        TaskStyleConfig[(item.rate ?? "normal")],
                                }}
                            >
                                {item.task}
                            </div>
                        ))}
                    </div>
                </div>

                {/* Popover */}

                {children && popoverVisibility && (
                    <div
                        ref={popoverRef}
                        className={`${styles.popover} ${styles.children}`}
                        onClick={(e) => e.stopPropagation()}
                    >
                        {children}
                    </div>
                )}
            </div>
        );
    },
);

CalendarDay.displayName = "CalendarDay";

export default CalendarDay;