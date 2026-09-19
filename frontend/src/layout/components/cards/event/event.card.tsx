
import {  isSameDay } from "date-fns";
import type { TFunction } from "i18next";

import { getDeltaSecondeTime, getElapsedTime } from "../../../../utils/format";
import type { Time } from "../../../../features/shared/global";
import type { CalendarEvent } from "../../../../features/planning/planning";

//-- Components

//-- SVG Components
import TimeSVG from "/src/assets/svg/time/time-svgrepo-com.svg?react"

//-- CSS
import styles from "./EventCard.module.css"
import Title from "../../text/title/title";
import Gauge from "../../progress/gauge/gauge";


/** -- EventCard -- */

interface EventCardProps{
    className?: string;
    event: CalendarEvent;
    t:  TFunction<"translation", undefined>;
    imageUrls?: string[];
    children?: React.ReactNode;
}


const EventCard: React.FC<EventCardProps> = ({
    t,
    event,
    className,
    children,
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

            { 
                children && (
                    <div className={styles.children}>
                        {children}
                    </div>
                )
            }
        </div>
    );
}


export default EventCard;