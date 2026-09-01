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
import TimeSVG from "/src/assets/svg/time/time-svgrepo-com.svg?react"

//-- CSS Modules
import styles from "./SchedulingAside.module.css"
import type { Time } from "../../../features/shared/global";
import { CardPlaceholder } from "../../../layout/components/cards/placeholder.php/card.placeholder";


interface SchedulingAsideProps{
    date: Date;
    events: CalendarEvent[];
    className?: string;
    onPrev?:(currentLenght: number) => void;
    onNext?: (currentLenght: number) => void;
}

const SchedulingAside: React.FC<SchedulingAsideProps> = ({
    date,
    events = [],

    onNext,
    onPrev,
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
                <ArrowNavigation 
                    onPrevious={()=> onPrev?.(events.length)}
                    onNext={()=> onNext?.(events.length)}
                />
            </div>

            {/** ITEMS */}
            <div className={`${styles.items} scrollbar`}>
                {
                    events.length > 0 ?
                        events.map((event,index)=>{
                            return (
                                <EventCard 
                                    t={t}
                                    key={index}
                                    event={event}
                                />
                            );
                        }) :
                        <CardPlaceholder text="Aucun entretien" />
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
    imageUrls?: string[]
}


const EventCard: React.FC<EventCardProps> = ({
    t,
    event,
    className,
    imageUrls = []
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
    const [images, memberNames] = event.members.reduce(
        (acc, current) => {
            acc[0].push(current.image ?? "");
            acc[1].push(current.name);

            return acc;
        },
        [[], []] as [string[], string[]]
    );

    const membersOutOfDisplayRange = (memberNames.length > 3 ? memberNames.length - 3 : memberNames.length);

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
                {
                    event.note && (
                        <span className={styles.desc}>
                            {event.note}
                        </span>
                    )
                }
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
                        {
                            images.map((img, index)=>(
                                <img 
                                    key={`${img}-${index}`}
                                    src={img ?? ""} 
                                    alt="image" 
                                />
                            ))
                        }
                    </div>
                    {
                        <div className={styles.txt}>
                            <span>{memberNames.slice(0, membersOutOfDisplayRange).join(', ')}</span>
                            {membersOutOfDisplayRange > 3 && <span>+{membersOutOfDisplayRange} {t("global.messages.more")}</span>}
                        </div>
                    }
                </div>
            </div>
        </div>
    );
}
 