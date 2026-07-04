import React, {
    createContext,
    useContext,
    useEffect,
    useMemo,
    useRef,
    useState,
} from "react";


import {
    addMonths,
    eachDayOfInterval,
    endOfMonth,
    endOfWeek,
    format,
    isSameDay,
    isSameMonth,
    startOfMonth,
    startOfToday,
    startOfWeek,
    subMonths,
} from "date-fns";


//-- CSS modules
import styles from "./Calendar.module.css";


interface CalendarContextValue {
    selectedDate: Date | null;
    pendingDate:  Date | null;       // date clicked, waiting for confirmation
    confirm:      () => void;        // parent calls this to validate the selection
    cancel:       () => void;        // parent calls this to reject the selection
}


const CalendarContext = createContext<CalendarContextValue | null>(null);



export const useCalendarContext = () => {
    const ctx =useContext(CalendarContext);
    if (!ctx) 
        throw new Error("useCalendarContext must be used inside <Calendar>");
    return ctx;
};


//--  Props

interface CalendarProps {
    width?: string;

    defaultSelectedDate?: Date | null; //-- default value
    validate?:        (date: Date) => boolean;
    onDateChange?:    (date: Date) => void;
    // children = context popover 
    children?:        React.ReactNode; // It receives selectedDate / pendingDate / confirm / cancel via useCalendarContext()
    className?:       string;
}


//--- Calendar

const Calendar: React.FC<CalendarProps> = ({
    width,
    
    validate,
    onDateChange,
    defaultSelectedDate= null,

    children,
    className,
}) => {
    const today = startOfToday();

    const [currentMonth, setCurrentMonth] = useState(defaultSelectedDate ?? today);
    const [selectedDate, setSelectedDate] = useState<Date | null>(defaultSelectedDate ?? today);

    // pendingDate  (the date the user just clicked, before confirmation)
    const [pendingDate,  setPendingDate]  = useState<Date | null>(null);

    //-- Popover position
    const [popoverAnchor, setPopoverAnchor] = useState<{ top: number; left: number } | null>(null);

    //-- HTML Ref
    const gridRef    = useRef<HTMLDivElement>(null);
    const popoverRef = useRef<HTMLDivElement>(null);


    //-- Days grids (builder)
    const days = useMemo(() => {
        const firstDay = startOfWeek(startOfMonth(currentMonth), { weekStartsOn: 1 });
        const lastDay  = endOfWeek(endOfMonth(currentMonth),     { weekStartsOn: 1 });
        return eachDayOfInterval({ start: firstDay, end: lastDay });
    }, [currentMonth]);

    //-- Handling date selection (click)
    const handleDayClick = (date: Date, buttonEl: HTMLButtonElement) => {
        if (validate && !validate(date))
            return;

        if (children) {
            //-- Open context popover
            setPendingDate(date);

            //-- Position popover (below the clicked button)
            const btnRect  = buttonEl.getBoundingClientRect();
            const gridRect = gridRef.current!.getBoundingClientRect();
            setPopoverAnchor({
                top:  btnRect.bottom - gridRect.top + 6,
                left: btnRect.left   - gridRect.left,
            });
        }
        else {
            // No children  (confirm immediately)
            commitSelection(date);
        }
    };

    // -- Confirm (called by child context UI) 
    const confirm = () => {
        if (!pendingDate) 
            return;
        
        commitSelection(pendingDate);
        setPendingDate(null);
        setPopoverAnchor(null);
    };

    // -- Handle close popover
    const cancel = () => {
        setPendingDate(null);
        setPopoverAnchor(null);
    };

    const commitSelection = (date: Date) => {
        setSelectedDate(date);
        onDateChange?.(date);
    };

    // -- Handling close popover
    useEffect(() => {
        if (!pendingDate) 
            return;
        
        //-- Close Handler
        const handleOutside = (e: MouseEvent) => {
            if (
                popoverRef.current &&
                !popoverRef.current.contains(e.target as Node)
            ) {
                cancel();
            }
        };
        const handleKey = (e: KeyboardEvent) => { if (e.key === "Escape") cancel(); };

        //-- Set handler
        window.addEventListener("keydown", handleKey);
        const timer = setTimeout(() =>
            document.addEventListener("mousedown", handleOutside), 0
        );

        //-- Detach Handler
        return () => {
            clearTimeout(timer);
            document.removeEventListener("mousedown", handleOutside);
            window.removeEventListener("keydown", handleKey);
        };
    }, [pendingDate]);


    return (
        <CalendarContext.Provider value={{ selectedDate, pendingDate, confirm, cancel }}>
            <div 
                style={{
                    ["--width" as string]: width ?? "320px"
                }}
                className={`${styles.calendar} ${className ?? ""}`}
            >

                {/* Header */}
                <header className={styles.header}>
                    <span>{format(currentMonth, "MMMM yyyy")}</span>
                    <div className={styles.leading}>
                        <div className={styles.navigateButton}>
                            <button onClick={() => setCurrentMonth(subMonths(currentMonth, 1))}>◀</button>
                            <button onClick={() => setCurrentMonth(addMonths(currentMonth, 1))}>▶</button>
                        </div>
                        <button
                            className={styles.todayBtn}
                            onClick={() => { setCurrentMonth(today); commitSelection(today); }}
                        >
                            Today
                        </button>
                    </div>
                </header>

                {/* Weekday labels */}
                <div className={styles.weekdays}>
                    {["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"].map(d => (
                        <span key={d}>{d}</span>
                    ))}
                </div>

                {/* Days grid — position:relative (popover anchors to it) */}
                <div className={styles.grid} ref={gridRef}>
                    {days.map(day => {
                        const isCurrentMonth = isSameMonth(day, currentMonth);
                        const isSelected     = selectedDate  && isSameDay(selectedDate,  day);
                        const isPending      = pendingDate   && isSameDay(pendingDate,    day);
                        const isToday        = isSameDay(day, today);
                        const disabled       = validate ? !validate(day) : false;

                        return (
                            <button
                                key={day.toISOString()}
                                disabled={disabled}
                                onClick={e => handleDayClick(day, e.currentTarget)}
                                className={[
                                    styles.day,
                                    !isCurrentMonth && styles.outside,
                                    isToday         && styles.today,
                                    isSelected      && styles.selected,
                                    isPending       && styles.pending,
                                    disabled        && styles.disabled,
                                ].filter(Boolean).join(" ")}
                            >
                                <span>{format(day, "d")}</span>
                            </button>
                        );
                    })}

                    {/* Context popover — anchored to the grid, shown above/below clicked day */}
                    {children && pendingDate && popoverAnchor && (
                        <div
                            ref={popoverRef}
                            className={styles.popover}
                            style={{
                                top:  popoverAnchor.top,
                                left: popoverAnchor.left,
                            }}
                            // Prevent clicks inside from bubbling to outside-click handler
                            onMouseDown={e => e.stopPropagation()}
                        >
                            {children}
                        </div>
                    )}
                </div>

            </div>
        </CalendarContext.Provider>
    );
};

export default Calendar;