import type React from "react";

import RightToLeftArrowSVG from '/src/assets/svg/arrows/back-arrow-direction-down-right-left-up-svgrepo-com.svg?react';


import styles from "./style.module.css"


export interface BrandButtonProps {
    text: string;
    type?: "submit" | "reset" | "button";
    svg?:  React.FC<React.SVGProps<SVGSVGElement>> | null;
    onClick?: (event: React.MouseEvent<HTMLButtonElement>) => void;
    
    width?: number | string;
    padding?: string | number;
    margin?: string | number;
    borderRadius?: string | number;

    fill?: boolean;
    fillColor?: string;
    fillForegroundColor?: string;

    backgroundColor?: string;
    color?: string;

    btnClassName?: string;
    svgClassName?: string;
}


const BrandButton: React.FC<BrandButtonProps> = ({
    type,
    width,
    padding,
    
    fill = true,
    fillColor = "#0154FE",
    fillForegroundColor = "#8bb1fd36",

    text,
    svg: Icon,
    onClick,
    svgClassName,

    btnClassName,
    borderRadius,

    backgroundColor = "#0154FE",
    color = "white",
}) => {
    return ( 
        <div 
            style={{
                ["--color" as string]: fill ? color : fillColor,
                ["--bg-color" as string]: fill ? backgroundColor : fillForegroundColor,
                ["--width" as string]: (width && typeof width == 'number' ?  `${width}px` : width) ?? "100%" ,
                ["--padding" as string]: (padding && typeof padding == 'number' ?  `${padding}px` : padding) ?? "9px 10px" ,
                ["--radius" as string]: (borderRadius && typeof borderRadius == 'number' ?  `${borderRadius}px` : borderRadius) ?? "10px" ,
            }}
            className={styles.container}
        >
           
            <button 
                type={type}
                onClick={onClick}
                style={{
                    border: `2px solid ${fill ? backgroundColor :  fillColor}`
                }}
                className={`${styles.btn} ${btnClassName}`}
            >
                {text} 
                {Icon ? 
                    <Icon className={`${styles.svg} ${svgClassName} `}  />
                     : Icon !== null ?
                        <RightToLeftArrowSVG
                            style={{ transform: "rotate(180deg)"  }} //--default transform for the default svg
                            className={`${styles.svg} ${svgClassName} `}
                        />
                        :<></>
                }
            </button>
        </div>
    );
}


export default BrandButton;