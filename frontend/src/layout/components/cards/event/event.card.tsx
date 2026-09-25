
import React from 'react'
import type { TFunction } from "i18next";
import { useEffect, useState } from "react";

import { getElapsedTime } from "../../../../utils/format";
import type { SlotComponentProps, Time, TimeRange } from "../../../../features/shared/global";
import type { CalendarEvent } from "../../../../features/planning/planning";
import { calculateProgress, calculateTimeRange,  } from "../../../../utils/dates";

//-- Components
import Title from "../../text/title/title";
import Gauge from "../../progress/gauge/gauge";
import InfoPill from "../../badges/pill/info.pill";
import ShortcurtLink from "../../link/shortcut/shortcut.link";
import { EventCardActions, EventCardBadge } from "./event.card.slot";

//-- SVG Components
import TimeSVG from "/src/assets/svg/time/time-svgrepo-com.svg?react"

//-- CSS
import styles from "./EventCard.module.css"


/** -- EventCard -- */



interface EventCardProps<T>{
    className?: string;
    event: CalendarEvent<T>;
    children?: React.ReactNode;
    t:  TFunction<"translation", undefined>;
}

type EventCardComponent = <T>(
    props: EventCardProps<T>
) =>  React.ReactNode; 


const today = new Date();

const EventCard = (<T= {},>({
    t,
    event,
    className,
    children,
}: EventCardProps<T>) => {

    const [progress, setProgress] = useState(0);
    const [timeRange, setTimeRange] = useState<TimeRange>({
        startTime: {hours: 0, minutes: 0},
        endTime: {hours: 0, minutes: 0}
    });


    /**
     * -------------------------
     * Computed alues
     * ------------------------
     */

    useEffect(() => {
        //-- Time range
        const timeRange = calculateTimeRange(event.date, event.durationMinutes);
        setTimeRange(timeRange);

        //-- Progress
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

        return () =>{ 
            clearInterval(interval)
        };
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

    /**
     * -------------------------
     * Compound Compoenents
     * ------------------------
     */
    let actions: React.ReactNode = null;
    let badge: React.ReactNode  = null;

    React.Children.forEach(children, (child) => {
        if (
            React.isValidElement<SlotComponentProps>(child) &&
            child.type === EventCardActions
        ) {
            actions = child.props.children;
            return;
        }

        if(React.isValidElement<SlotComponentProps>(child) && child.type === EventCardBadge &&  child.props.children){
            badge = child;
            return;
        }
    });

    
    console.log('badge : ', badge)
    /**
     * --------------------------
     * RENDERING
     * ---------------------------
     */

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

            {/**Links */}
            {
                Array.isArray(event.links) &&  event.links.length > 0 && (
                    <>
                        <div className={styles.dotedSeparator} />
                        <div className={styles.links}>
                            {
                                event.links.map((item, index)=>{
                                        if(!item.url)
                                            return;
                                        return (
                                            <ShortcurtLink 
                                                key={index}
                                                link={item.url}
                                                disabled={item.isActive}
                                            />
                                        )
                                    }
                                )
                            }
                        </div>
                    </>
                )
            }

            {/** Actions */}
            { 
                actions  && (
                    <>
                        <div className={styles.dotedSeparator} />
                        <div className={styles.actions}>
                            {actions}
                        </div>
                    </>
                )
            }

            {/** Status */}
            {
                badge !== null && (
                    <>
                        <div
                            className={`${styles.status} ${
                                event.badge
                                    ? styles[`status_${event.badge.flag}`]
                                    : ""
                            }`}
                        >
                            {badge !== undefined ? (
                                <>
                                    <div className={styles.dotedSeparator} />
                                    {badge}
                                </>
                            ) : event.badge ? (
                                <>
                                    <div className={styles.dotedSeparator} />

                                    <InfoPill
                                        text={event.badge.text}
                                        className={styles.infoPill}
                                    />
                                </>
                            ) : null}
                        </div>
                    </>
                )
            }
        </div>
    );
}) as EventCardComponent & {
    Actions: React.FC<SlotComponentProps>;
    badge: React.FC<SlotComponentProps>;
}


EventCard.Actions = EventCardActions;
EventCard.badge = EventCardBadge;


export default EventCard;