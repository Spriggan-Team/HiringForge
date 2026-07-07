import { format, isSameDay } from "date-fns";
import { useTranslation } from "react-i18next";

//-- Services & types
import type { TFunction } from "i18next";
import type { CalendarEvent } from "../../../features/planning/planning";
import { getDeltaSecondeTime, getElapsedTime } from "../../../utils/format";

//-- Custom components
import ArrowNavigation from "../../../layout/components/navigation/arrow.navigation";
import Title from "../../../layout/components/text/title/title";
import Gauge from "../../../layout/components/progress/gauge/gauge";

//-- SVG Components
import TimeSVG from "/src/assets/svg/time/time-svgrepo-com.svg"

//-- CSS Modules
import styles from "./SchedulingAside.module.css"
import { mockCalendarEvents } from "../../../core/mock/events";
import type { Time } from "../../../features/shared/global";


interface SchedulingAsideProps{
    date: Date;
    events?: CalendarEvent[];
    className?: string;
}

const SchedulingAside: React.FC<SchedulingAsideProps> = ({
    date,
    events = [],

    className
}) => {
    const { t } = useTranslation();
    
    return (
        <div className={`${styles.aside} ${className}`}>
            {/** Header */}
            <div className={styles.header}>
                {/** TEXT SECTION */}
                <div className={styles.textSection}>
                    <Title title={t("scheduler.scheduled")}/>
                    <span className={styles.date}>{format(date, "d MMMM, yyyy")}</span>
                </div>

                {/** NAVIGATION ARROWS */}
                <ArrowNavigation />
            </div>

            {/** ITEMS */}
            <div className={`${styles.items} scrollbar`}>
                {
                    mockCalendarEvents.map((event,index)=>{
                        if(index > 2) return null;
                        return (
                            <EventCard 
                                t={t}
                                key={index}
                                event={event}
                            />
                        );
                    })
                }
            </div>
        </div>
    );
}
 
export default SchedulingAside ;


/** -- EventCard -- */

interface EventCardProps{
    className?: string;
    event: CalendarEvent;
    t:  TFunction<"translation", undefined>;
}


const EventCard: React.FC<EventCardProps> = ({
    t,
    event,
    className,
}) => {
    const today = new Date();

    const progress = (() => {
        if (!event.time.end || !isSameDay(today, event.date)) {
            return 0;
        }

        const elapsed = getDeltaSecondeTime(
            event.time.start,
            {
                hours: today.getHours(),
                minutes: today.getMinutes(),
            }
        );

        const total = getDeltaSecondeTime(
            event.time.start,
            event.time.end
        );

        return Math.min(1, Math.max(0, elapsed / total));
    })();

    const formatTime = ({ hours, minutes }: Time) =>`${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}`;

    return (
        <div className={`${styles.event} ${className} card`}>
            {/* Header */}
            <div className={styles.header}>
                <span className={styles.text}>
                    {formatTime(event.time.start)}
                </span>
                <div className={styles.separator} />
            </div>

            {/* Title */}
            <div className={styles.titleSection}>
                {event.time.end && (
                    <Gauge width='100%' height="5px" percent={progress} />
                )}

                <Title title={event.title} />

                <span className={styles.desc}>
                    {event.note}
                </span>
            </div>

            <div className={styles.dotedSeparator} />

            {/* Time */}
            <div className={styles.middle}>
                <div>
                    <TimeSVG width={15} height={15} />
                    <span>{formatTime(event.time.start)}</span>
                </div>

                {event.time.end && (
                    <span>
                        {getElapsedTime(event.time.start, event.time.end)}
                    </span>
                )}
            </div>

            <div className={styles.dotedSeparator} />

            {/* Members */}
            <div className={styles.footer}>
                <div className={styles.members}>
                    <div className={styles.images}>
                        {/* avatars */}
                        <img src="https://www.studio-pop-art.fr/cdn/shop/products/portrait-homme-465158.webp?v=1690383976&width=1445" alt="" />
                        <img src="https://www.bragard.fr/14489-large_default/leo-veste-homme.jpg" alt="" />
                    </div>

                    <div className={styles.txt}>
                        <span>Julia K, Ivan M</span>
                        <span>+5 {t("global.messages.more")}</span>
                    </div>
                </div>
            </div>
        </div>
    );
}
 