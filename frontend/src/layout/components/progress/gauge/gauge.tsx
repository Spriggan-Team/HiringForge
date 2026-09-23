
import styles from "./styles.module.css"


export interface GaugeProps{
    /**Percent btween 0-1 */
    percent?: number;
    color?: string;
    width?: string | number;
    
    activeColor?: string;
    foregroundColor?: string;
    
    height?: string | number;
    className?: string;
    borderRadius?: string | number;
}


const Gauge: React.FC<GaugeProps> = ({
    percent = 0,
    width,
    height,

    activeColor = "#3B82F6",
    foregroundColor,
    
    className,
    borderRadius,
}) => {
    if(percent < 0 || percent > 1)
        return;

    return ( 
        <div 
            style={{ 
                ["--currentWidth" as string]:`${percent * 100}%`,
                ["--activeColor" as string]: activeColor,
                ["--foregroundColor" as string]: foregroundColor,
                ["--width" as string]: width ? (typeof width == "number" ? `${width}px`: width) : "100%",
                ["--height" as string]: height ? (typeof height == "number" ? `${height}px` : height) : "4.5px",
                ["--borderRadius" as string] : borderRadius ? (typeof borderRadius == "number"  ? `${borderRadius}px` : borderRadius) : "15px",
            }} 
            className={`${styles.gauge} ${className}`}
        />
    );
}
 
export default Gauge;