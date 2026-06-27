import {  format, startOfToday } from "date-fns";


const Calendar = () => {
    let today = startOfToday();

    return (
        <div>
            <time dateTime={format(today, "yyyy-MM-dd")}>
                {/* {format(today, 'd')} */}
            </time>
        </div>
    );
}
 
export default Calendar;