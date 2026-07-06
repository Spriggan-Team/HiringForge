import { useState } from "react";

//--Custom components
import InputLabel from "../input.label";
import MenuDrawer, { 
    MenuDrawerBody,
    MenuDrawerTrigger,
} from "../../../menu/drawer/menu.drawer";
import DatePicker from "../../../cards/calendar/datepicker/DatePicker";

//-- CSS Modules
import styles from "./DateInput.module.css"



interface DateInputProps{
    label?: string;
    onSelectedDate?: (date: Date) => void;
    defaultContent?: string;
    leading?: React.FC<React.SVGProps<SVGSVGElement>>;
}


const DateInput: React.FC<DateInputProps> = ({
    label,
    defaultContent,
    leading: Leading,
    onSelectedDate,
}) => {
    const [selectedDate, setSelectedDate] = useState<Date | null>(null);

    return (
        <div className={styles.container}>
            {label && <InputLabel label={label} />}
            <MenuDrawer>
                <MenuDrawerTrigger 
                    className={`${styles.trigger} card-border`}
                    leading={
                        <div className={styles.leading}>
                            {
                                Leading && (
                                    <Leading width={15} height={15} />
                                )
                            }
                        </div>
                    }
                >
                    <div>
                        {selectedDate
                            ? selectedDate.toLocaleDateString()
                            : defaultContent ?? new Date().toLocaleDateString()}
                    </div>
                </MenuDrawerTrigger>

                <MenuDrawerBody
                    position="initial-absolute"
                    applyDefaultStyle={false}
                >
                    <DatePicker
                        defaultSelectedDate={selectedDate}
                        onDateChange={(date) => {
                            onSelectedDate?.(date);
                            setSelectedDate(date)
                        }}
                    />
                </MenuDrawerBody>
            </MenuDrawer>
        </div>
    );
};


 
export default DateInput;