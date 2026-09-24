import React from "react";

//-- Css styles 
import styles from "./style.module.css"

type InfoPillStyle = React.CSSProperties & {
    "--pill-color"?: string;
    "--pill-background"?: string;
    "--pill-border-radius"?: string;
};

export interface InfoPillProps{
    text: string;
    indicator?: boolean;
    
    txtColor?: string;
    className?: string;
    backgroundColor?: string;
    borderRadius?: string | number;
}


/**
 * Displays a compact information pill.
 *
 * Styling is controlled through CSS variables:
 * --pill-color
 * --pill-background
 * --pill-border
 * --pill-border-radius
 *
 * Props can override these variables when needed.
 */
const InfoPill: React.FC<InfoPillProps> = ({
    text,
    indicator,
    className,

    txtColor,
    borderRadius,
    backgroundColor,
}) => {
    if(!text)
        return null;
    
    const style: InfoPillStyle = {};

    if (txtColor !== undefined) {
        style["--pill-color"] = txtColor;
    }

    if (backgroundColor !== undefined) {
        style["--pill-background"] = backgroundColor;
    }

    if (borderRadius !== undefined) {
        style["--pill-border-radius"] =
            typeof borderRadius === "string"
                ? borderRadius
                : `${borderRadius}px`;
    }

    return (
        <div 
            className={`${styles.container} ${className ?? ""}`}
            style={style}
        >
            { indicator && <div className={styles.indicator} /> }
            <span>{ text }</span>
        </div>
    );
}
 
export default InfoPill;