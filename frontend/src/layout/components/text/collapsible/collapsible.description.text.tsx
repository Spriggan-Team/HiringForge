
import React, { useState } from "react";

import DownArrowSVG from '/src/assets/svg/arrows/down-arrow-5-svgrepo-com.svg?react';

import styles from "./CollapsibleDescriptionText.module.css"


interface CollapsibleDescriptionTextProps{
    title?: string;
    text: string;
    maxLines?: number;
}


/**
 * Displays a text with a configurable line limit
 * and allows the user to expand or collapse it.
 */
const CollapsibleDescriptionText: React.FC<
    CollapsibleDescriptionTextProps
> = ({
    title,
    text,
    maxLines = 3,
}) => {
    const [isExpanded, setIsExpanded] = useState(false);

    return (
        <div className={styles.container}>
            {title && (
                <span className={styles.title}>
                    {title}
                </span>
            )}

            <div
                className={styles.details}
                style={{
                    "--max-lines": maxLines,
                } as React.CSSProperties}
            >
                <p
                    className={`${styles.text} ${
                        isExpanded
                            ? styles.expanded
                            : styles.collapsed
                    }`}
                >
                    {text}
                </p>

                <button
                    type="button"
                    className={`${styles.icon} ${
                        isExpanded
                            ? styles.expandedIcon
                            : styles.collapsedIcon
                    }`}
                    onClick={() => setIsExpanded((prev) => !prev)}
                    aria-expanded={isExpanded}
                >
                    <DownArrowSVG width={15} height={15} />
                </button>
            </div>
        </div>
    );
};

export default CollapsibleDescriptionText;