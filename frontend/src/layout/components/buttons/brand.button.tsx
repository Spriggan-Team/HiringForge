import type React from "react";
import styles from "./style.module.css"


interface BrandButtonProps {
    type?: "submit" | "reset" | "button";
    text: string;
    width?: number | string;
    padding?: string | number;
    borderRadius?: string | number;
    margin?: string | number;
    backgroundColor?: string;
    svg?:  React.FC<React.SVGProps<SVGSVGElement>>
    btnClassName?: string;
    svgClassName?: string;
}


const BrandButton: React.FC<BrandButtonProps> = ({
    type,
    text,
    width,
    padding,
    svg: Icon,
    borderRadius,
    backgroundColor,
    svgClassName,
    btnClassName,
}) => {
    return ( 
        <div 
            style={{
                ["--bg-color" as string]: backgroundColor?? "#0154FE" ,
                ["--width" as string]: (width && typeof width == 'number' ?  `${width}px` : width) ?? "100%" ,
                ["--padding" as string]: (padding && typeof padding == 'number' ?  `${padding}px` : padding) ?? "9px 0" ,
                ["--radius" as string]: (borderRadius && typeof borderRadius == 'number' ?  `${borderRadius}px` : borderRadius) ?? "10px" ,
            }}
            className={styles.container}
        >
           
            <button className={`${styles.btn} ${btnClassName}`} type={type}>
                {text} 
                {Icon && <Icon className={`${styles.svg} ${svgClassName} `}  /> }
            </button>
        </div>
    );
}


export default BrandButton;