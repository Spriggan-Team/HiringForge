import {
    addMonths,
    subMonths,
    setMonth,
    setYear,
    format,
} from "date-fns";
import { useMemo, useState } from "react";
import { useTranslation } from "react-i18next";


import MenuDrawer, {
    MenuDrawerBody,
    MenuDrawerItem,
    MenuDrawerTrigger,
} from "../../menu/drawer/menu.drawer";


//CSS Modules - Styles
import styles from "./CalendarHeader.module.css";
import ArrowNavigation from "../../navigation/arrow.navigation";



interface CalendarHeaderProps {
    defaultDate: Date;

    onChange?: (date: Date) => void;
    onPrevious?: () => void;
    onNext?: () => void;
}



const CalendarHeader: React.FC<CalendarHeaderProps> = ({
    defaultDate,
    onChange,
    onPrevious,
    onNext,
}) => {
    const { t } = useTranslation();

    const today = new Date();
    const [date, setDate] = useState(defaultDate);

    const months = useMemo(() => {
        const currentYear = today.getFullYear();
        const currentMonth = today.getMonth();

        const firstMonth =
            date.getFullYear() === currentYear
                ? currentMonth
                : 0;

        return Array.from(
            { length: 12 - firstMonth },
            (_, index) => {
                const month = firstMonth + index;

                return {
                    value: month,
                    label: format(setMonth(new Date(), month), "MMMM"),
                };
            }
        );
    }, [date]);


    const years = useMemo(() => {
        const currentYear = today.getFullYear();

        return Array.from(
            { length: 5 },
            (_, i) => currentYear + i
        );
    }, [today]);

    

    const updateDate = (newDate: Date) => {
        setDate(newDate);
        onChange?.(newDate);
    };


    return (
        <div className={styles.container}>
            <div className={styles.main}>
                {/* Month */}
                <div className={styles.drawerContainer}>
                    <MenuDrawer
                        onChange={(monthIndex: number) =>
                            updateDate(setMonth(date, monthIndex))
                        }
                    >
                        <MenuDrawerTrigger>
                            {format(date, "MMMM")}
                        </MenuDrawerTrigger>

                        <MenuDrawerBody position="initial-absolute">
                            {months.map(month => (
                                <MenuDrawerItem
                                    key={month.value}
                                    value={month.value}
                                >
                                    {month.value === today.getMonth() ? t("global.dates.thisMonth") : month.label}
                                </MenuDrawerItem>
                            ))}
                        </MenuDrawerBody>
                    </MenuDrawer>
                </div>

                {/* Year */}
                <div className={styles.drawerContainer}>
                    <MenuDrawer
                        onChange={(year: number) =>
                            updateDate(setYear(date, year))
                        }
                    >
                        <MenuDrawerTrigger>
                            {format(date, "yyyy")}
                        </MenuDrawerTrigger>

                        <MenuDrawerBody position="initial-absolute">
                            {years.map(year => (
                                <MenuDrawerItem
                                    key={year}
                                    value={year}
                                >
                                    {year}
                                </MenuDrawerItem>
                            ))}
                        </MenuDrawerBody>
                    </MenuDrawer>
                </div>
            </div>

            {/** NAVIGATIOn ARROWS */}
            <ArrowNavigation 
                onPrevious={()=>{
                    if (onPrevious) {
                        onPrevious();
                        return;
                    }

                    updateDate(subMonths(date, 1));
                }}
                onNext={()=>{
                    if (onNext) {
                        onNext();
                        return;
                    }

                    updateDate(addMonths(date, 1));
                }}
            />

        </div>
    );
};

export default CalendarHeader;