

import styles from "./style.module.css"

interface SeparatorProps{
    width?: string;
    height?: string;
    backgroundColor?: string;
}

const Separator: React.FC<SeparatorProps> = ({
    width = "100%",
    height = "2px",
    backgroundColor = "#D9D9D9" 
}) => {
    return ( 
        <div
            style={{
                ["--width" as string]: width,
                ["--height" as string]: height,
                ["--bgColor" as string]: backgroundColor,
            }}
            className={styles.container}
        >
        </div>
    );
}
 
export default Separator;