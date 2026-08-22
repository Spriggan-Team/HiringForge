

import styles from "./CalendarContainer.module.css"


interface CalendarContainerProps{
    children: React.ReactNode;
    className?: string
}


const CalendarContainer: React.FC<CalendarContainerProps> = ({
    children,
    className
}) => {
    return (
        <div 
            className={`${styles.container} ${className}`}
        >
            {children}
        </div>
    );
}
 
export default CalendarContainer;