

//-- CSS Styles
import styles from "./CalendarGrid.module.css"


interface CalendarGridProps{
    children: React.ReactNode;
}


const CalendarGrid: React.FC<CalendarGridProps> = ({
    children
}) => {
    return (
        <div className={styles.grid}>
            {children}
        </div>
    );
}

 
export default CalendarGrid
