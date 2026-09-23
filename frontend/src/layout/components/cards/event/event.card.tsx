
import {  isSameDay } from "date-fns";
import type { TFunction } from "i18next";

import { getDeltaSecondeTime, getElapsedTime } from "../../../../utils/format";
import type { Time, TimeRange } from "../../../../features/shared/global";
import type { CalendarEvent } from "../../../../features/planning/planning";

//-- Components

//-- SVG Components
import TimeSVG from "/src/assets/svg/time/time-svgrepo-com.svg?react"

//-- CSS
import styles from "./EventCard.module.css"
import Title from "../../text/title/title";
import Gauge from "../../progress/gauge/gauge";
import InfoPill from "../../badges/pill/info.pill";
import { useEffect, useState } from "react";
import { calculateProgress, calculateTimeRange } from "../../../../utils/dates";


/** -- EventCard -- */

interface EventCardProps{
    className?: string;
    imageUrls?: string[];
    
    event: CalendarEvent;
    children?: React.ReactNode;
    status?: string;
    t:  TFunction<"translation", undefined>;
}


const EventCard: React.FC<EventCardProps> = ({
    t,
    event,
    className,
    status,
    children,
    imageUrls = []
}) => {
    const today = new Date();
    const [progress, setProgress] = useState(0);
    const [timeRange, setTimeRange] = useState<TimeRange>({
        startTime: {hours: 0, minutes: 0},
        endTime: {hours: 0, minutes: 0}
    });

    useEffect(() => {
        const updateProgress = () => {
            setProgress(
                calculateProgress(
                    event.date,
                    event.durationMinutes
                )
            );
        };

        updateProgress();
        const interval = setInterval(updateProgress, 1000);
        
        const timeRange = calculateTimeRange(event.date, event.durationMinutes);
        setTimeRange(timeRange);

        return () => clearInterval(interval);
    }, [event.date, event.durationMinutes]);


    //-- Format time
    const formatTime = ({ hours, minutes }: Time) =>`${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}`;
    
    //-- Mapped members images
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
                <div className={styles.timeIndicator}>
                    <span className={styles.text}>
                        {formatTime(timeRange.startTime)}
                    </span>
                    {progress ? (
                        <Gauge width='100%' height="5px" percent={progress} />
                    ):(
                        <div className={styles.separator} />
                    )}
                </div>
            </div>

            {/* Title */}
            <div className={styles.titleSection}>


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
                    <span>{formatTime(timeRange.startTime)}</span>
                </div>

                {timeRange.endTime && (
                    <span>
                        {getElapsedTime(timeRange.startTime, timeRange.endTime)}
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
                    <>
                        <div className={styles.dotedSeparator} />
                        <div className={styles.children}>
                            {children}
                        </div>
                    </>
                )
            }
            {
                status &&(
                    <div className={styles.status}> 
                        <InfoPill  
                            text={status}
                            className={styles.infoPill}
                        />
                    </div>
                )
            }
        </div>
    );
}


export default EventCard;