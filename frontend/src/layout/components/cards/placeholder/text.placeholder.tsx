import React, { useMemo } from 'react';

import styles from "./TextPlaceholder.module.css"



interface TextPlaceholderProps {
    line?: number;

    /**
     * General irregularity of text
     * 0 = très régulier
     * 1 = très irrégulier
     */
    roughness?: number;

    /**
     * Vertical space between line
     */
    lineGap?: number;

    /**
     * Average words per line
     */
    wordCount?: number;

    /**
     * Word number variation on a line
     * 0 = same word lenght
     * 1 = imortant variation
     */
    wordVariation?: number;

    /**
     * Variation of break position
     * 0 = hight regular lines
     * 1 = hight irregular line
     */
    breakVariation?: number;

    /**
     * Minimal with of a word
     * (in %).
     */
    minWordWidth?: number;

    /**
     * Simulated max length length of a word 
     * En pourcentage.
     */
    maxWordWidth?: number;

    className?: string;
}

/**
 * Types
 */

interface PlaceholderWord {
    width: number;
}

interface PlaceholderLine {
    words: PlaceholderWord[];
}

/**
 * Components
 */

const TextPlaceholder: React.FC<TextPlaceholderProps> = ({
    line = 4,
    roughness = 0.4,
    lineGap = 8,
    wordCount = 8,
    wordVariation = 0.35,
    breakVariation = 0.4,
    minWordWidth = 4,
    maxWordWidth = 14,
    className
}) => {
    /** Compute parameters */
    const safeLine = Math.max( 1, Math.floor(line));

    const safeRoughness = clamp(roughness,0,1);

    const safeWordVariation = clamp( wordVariation, 0, 1);

    const safeBreakVariation = clamp( breakVariation, 0, 1 );

    const safeWordCount = Math.max( 1, Math.floor(wordCount) );

    const safeLineGap = Math.max( 0, lineGap );

    const safeMinWordWidth = clamp( minWordWidth, 1, 100 );

    const safeMaxWordWidth = clamp( maxWordWidth,safeMinWordWidth,100);

    const lines = useMemo<PlaceholderLine[]>(() => {
        const random = createSeededRandom(safeLine * 17 + safeWordCount * 31 + Math.round(safeRoughness * 100));

        return Array.from(
            { length: safeLine },
            (_, lineIndex) => {
                const isLastLine = lineIndex === safeLine - 1;
                /*
                 * Variation du nombre de mots.
                 */
                const variation = (random() - 0.5) * 2 * safeWordVariation;
                let currentWordCount = Math.round(
                    safeWordCount *
                    (
                        1 + variation
                    )
                );

                /*
                 * La dernière ligne est généralement
                 * plus courte afin de simuler un vrai
                 * paragraphe.
                 */
                if (isLastLine) {
                    const lastLineFactor = 0.45 + random() * (0.35 *safeBreakVariation);
                    currentWordCount = Math.max(
                        1,
                        Math.round(
                            currentWordCount *
                            lastLineFactor
                        )
                    );
                }

                currentWordCount = Math.max( 1, currentWordCount );

                /*
                 * Génération des faux mots.
                 */
                const words = Array.from(
                    {
                        length: currentWordCount
                    },
                    (_, wordIndex) => {
                        const baseWidth =safeMinWordWidth + random() * (safeMaxWordWidth - safeMinWordWidth );

                        /*
                         * Irrégularité individuelle
                         * des mots.
                         */
                        const widthVariation =(random() - 0.5) *2 * safeRoughness;
                        let width = baseWidth *( 1 + widthVariation);

                        /*
                         * Le premier mot est légèrement
                         * plus naturel.
                         */
                        if (wordIndex === 0) {
                            width *= 1.15;
                        }

                        width = clamp(
                            width,
                            safeMinWordWidth,
                            safeMaxWordWidth
                        );

                        return {
                            width
                        };
                    }
                );

                return {
                    words
                };
            }
        );
    }, [
        safeLine,
        safeWordCount,
        safeRoughness,
        safeWordVariation,
        safeBreakVariation,
        safeMinWordWidth,
        safeMaxWordWidth
    ]);

    return (
        <div
            className={`${styles.container} ${className ?? ""}`}
            style={{
                gap: `${safeLineGap}px`
            }}
        >
            {lines.map((currentLine, lineIndex) => (
                <div
                    key={lineIndex}
                    className={styles.line}
                >
                    {currentLine.words.map(
                        (word, wordIndex) => (
                            <span
                                key={wordIndex}
                                className={styles.word}
                                style={{
                                    width: `${word.width}%`
                                }}
                            />
                        )
                    )}
                </div>
            ))}
        </div>
    );
};

export default TextPlaceholder;


/** Helpers */

const clamp = (
    value: number,
    min: number,
    max: number
) => {
    return Math.min(
        max,
        Math.max(min, value)
    );
};

const createSeededRandom = (seed: number) => {
    let value = seed;

    return () => {
        value = (
            value * 9301 +
            49297
        ) % 233280;

        return value / 233280;
    };
};
