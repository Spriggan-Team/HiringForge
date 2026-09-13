


import React, { useCallback, useEffect, useRef, useState } from 'react'

//-- SVG Modules
import LeftToRightArrowSVGComponent from "/src/assets/svg/arrows/arrow-left-334-svgrepo-com.svg?react";
import RightToLeftArrowSVGComponent from "/src/assets/svg/arrows/arrow-right-333-svgrepo-com.svg?react";

//-- Css Module - Styles
import styles from "./ContentSlider.module.css"




interface ContentSliderProps {
    className?: string;
    children: React.ReactNode;
}

const ContentSlider: React.FC<ContentSliderProps> = ({
    className,
    children
}) => {
    const childrenRef = useRef<HTMLDivElement | null>(null);

    const childrenArray = React.Children.toArray(children);
    const totalCount = childrenArray.length;

    const [currentIndicator, setCurrentIndicator] = useState(0);

    const updateIndicator = useCallback(() => {
        const container = childrenRef.current;

        if (!container || totalCount === 0) {
            return;
        }

        const elements = Array.from(
            container.children
        ) as HTMLElement[];

        if (elements.length === 0) {
            return;
        }

        const scrollLeft = container.scrollLeft;

        let closestIndex = 0;
        let closestDistance = Infinity;

        elements.forEach((element, index) => {
            const distance = Math.abs(
                element.offsetLeft - scrollLeft
            );

            if (distance < closestDistance) {
                closestDistance = distance;
                closestIndex = index;
            }
        });

        setCurrentIndicator(closestIndex);
    }, [totalCount]);

    const scrollToIndex = useCallback((index: number) => {
        const container = childrenRef.current;

        if (!container) {
            return;
        }

        const element = container.children[index] as HTMLElement;

        if (!element) {
            return;
        }

        container.scrollTo({
            left: element.offsetLeft,
            behavior: "smooth"
        });
    }, []);

    const handlePrevious = () => {
        if (currentIndicator <= 0) {
            return;
        }

        scrollToIndex(currentIndicator - 1);
    };

    const handleNext = () => {
        if (currentIndicator >= totalCount - 1) {
            return;
        }

        scrollToIndex(currentIndicator + 1);
    };

    useEffect(() => {
        const container = childrenRef.current;

        if (!container) {
            return;
        }

        container.addEventListener(
            "scroll",
            updateIndicator,
            { passive: true }
        );

        return () => {
            container.removeEventListener(
                "scroll",
                updateIndicator
            );
        };
    }, [updateIndicator]);

    if (totalCount === 0) {
        return null;
    }

    return (
        <div
            className={`${styles.container} ${className ?? ""}`}
        >
            <button
                type="button"
                className={`${styles.arrow} ${styles.leftArrow}`}
                onClick={handlePrevious}
                disabled={currentIndicator === 0}
                aria-label="Contenu précédent"
            >
                <LeftToRightArrowSVGComponent
                    width={16}
                    height={16}
                />
            </button>

            <div
                ref={childrenRef}
                className={styles.children}
            >
                {childrenArray.map((child, index) => (
                    <div
                        key={index}
                        className={styles.slide}
                    >
                        {child}
                    </div>
                ))}
            </div>

            {totalCount > 1 && (
                <div className={styles.indicators}>
                    {childrenArray.map((_, index) => (
                        <button
                            key={index}
                            type="button"
                            className={
                                index === currentIndicator
                                    ? styles.activeIndicator
                                    : styles.indicator
                            }
                            onClick={() => scrollToIndex(index)}
                            aria-label={`Aller au contenu ${index + 1}`}
                            aria-current={
                                index === currentIndicator
                                    ? "true"
                                    : undefined
                            }
                        />
                    ))}
                </div>
            )}

            <button
                type="button"
                className={`${styles.arrow} ${styles.rightArrow}`}
                onClick={handleNext}
                disabled={
                    currentIndicator === totalCount - 1
                }
                aria-label="Contenu suivant"
            >
                <RightToLeftArrowSVGComponent
                    width={16}
                    height={16}
                />
            </button>
        </div>
    );
};

export default ContentSlider;
