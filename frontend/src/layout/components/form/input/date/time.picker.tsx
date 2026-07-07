import { useCallback, useState } from "react";

//Services & types
import type { Time } from "../../../../../features/shared/global";

//-- SVG Compoenenst
import LeftArrowSVGComponent from "/src/assets/svg/arrows/arrow-left-334-svgrepo-com.svg";
import RightArrowSVGComponent from "/src/assets/svg/arrows/arrow-right-333-svgrepo-com.svg";

//--CSS Module
import styles from "./TimePicker.module.css"


interface TimePickerProps{
    defaultValue?: Time;
    onChange?: (time : Time)=>void
}


const TimePicker: React.FC<TimePickerProps> = ({
    onChange,
    defaultValue = { hours: 0, minutes: 0 },
}) => {
    const [time, setTime] = useState(defaultValue);

    const update = (next: Time) => {
        setTime(next);
        onChange?.(next);
    };

    const updateHours = (hours: number) => {
        update({
            ...time,
            hours: Math.min(23, Math.max(0, hours)),
        });
    };

    const updateMinutes = (minutes: number) => {
        update({
            ...time,
            minutes: Math.min(59, Math.max(0, minutes)),
        });
    };

    return (
        <div className={styles.container}>
            <button
                type="button"
                className={styles.svgContainer}
                onClick={() => updateHours(time.hours - 1)}
                disabled={time.hours === 0}
            >
                <LeftArrowSVGComponent
                    className={styles.svg}
                    width={15}
                    height={15}
                />
            </button>

            <div className={styles.time}>
                <input
                    className={styles.input}
                    type="number"
                    min={0}
                    max={23}
                    value={String(time.hours).padStart(2, "0")}
                    onChange={(e) =>
                        updateHours(Number(e.target.value))
                    }
                />

                <span>:</span>

                <input
                    className={styles.input}
                    type="number"
                    min={0}
                    max={59}
                    value={String(time.minutes).padStart(2, "0")}
                    onChange={(e) =>
                        updateMinutes(Number(e.target.value))
                    }
                />
            </div>

            <button
                type="button"
                className={styles.svgContainer}
                onClick={() => updateHours(time.hours + 1)}
                disabled={time.hours === 23}
            >
                <RightArrowSVGComponent
                    className={styles.svg}
                    width={15}
                    height={15}
                />
            </button>
        </div>
    );
};

export default TimePicker;