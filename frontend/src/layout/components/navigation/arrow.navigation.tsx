



//--Custom components
import LeftToRightArrowSVGComponent from "/src/assets/svg/arrows/arrow-left-334-svgrepo-com.svg";
import RightToLeftArrowSVGComponent from "/src/assets/svg/arrows/arrow-right-333-svgrepo-com.svg";

//-- CSS Modules
import styles from "./ArrowNavigation.module.css"


interface ArrowNavigationProps{
    onNext?: ()=>void;
    onPrevious?: ()=>void;

    className?: string;
    disablePrev?: boolean;
    disableNext?: boolean;
}

const ArrowNavigation: React.FC<ArrowNavigationProps> = ({
    onNext,
    onPrevious,

    className,
    disableNext,
    disablePrev,
}) => {
    return (
        <div className={`${styles.leading} ${className}`}>
            <button
                className={`${styles.svgContainer} ${disablePrev ? styles.disable : ""}`}
                onClick={() => {
                    if(disablePrev) return;
                    onPrevious?.();
                }}
            >
                <LeftToRightArrowSVGComponent
                    className={styles.svg}
                    width={15}
                    height={15}
                />
            </button>

            <button
                className={`${styles.svgContainer} ${disableNext ? styles.disable : ""}`}
                onClick={() => {
                    if(disableNext) return;
                    onNext?.();
                }}
            >
                <RightToLeftArrowSVGComponent
                    className={styles.svg}
                    width={15}
                    height={15}
                />
            </button>
        </div>
    );
}
 
export default ArrowNavigation;