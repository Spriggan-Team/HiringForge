
//--custom components
import BrandButton, { type BrandButtonProps } from "../../buttons/brand.button";

//-- SVG Components 
import OKCircleSVG from "../../../../assets/svg/check/ok-circle-svgrepo-com.svg";
import LeftToRightArrowSVG from '/src/assets/svg/arrows/back-arrow-direction-down-right-left-up-svgrepo-com.svg';

//-- Styles
import styles from "./style.module.css"


interface ProcessChecklistCardProps{
    title: string;
    svg?:  React.FC<React.SVGProps<SVGSVGElement>>
    checkList: string[],
    description?: string;
    
    onClick?: (event: React.MouseEvent<HTMLButtonElement>)=>void;

    primaryColor?: string;
    secondaryColor?: string;

    buttonText: string;
    fillButton?: boolean;
    fillButtonForegroundColor?: string;
}



const ProcessChecklistCard: React.FC<ProcessChecklistCardProps> = ({
    title,
    svg: Icon,
    description,
    checkList = [],
    onClick,

    primaryColor,
    secondaryColor,

    buttonText,
    fillButton,
    fillButtonForegroundColor
}) => {
    return (
        <div 
            style={{
                ["--primaryColor" as string]: primaryColor,
                ["--secondaryColor" as string]: secondaryColor
            }}
            className={styles.container}
        >
            <div className={styles.top}>
                <div className={styles.svgContainer}>
                    {Icon && <Icon  color={primaryColor} width={45} height={45} />}
                </div>
                <div className={styles.headline}>
                    <span className={`${styles.title} boldSecondaryTxt`}>{title}</span>
                    <p className={styles.description}> {description}</p>
                </div>
            </div>

            <ul className={styles.list}>
                {
                    checkList.map((text)=> (
                        <li className={styles.checkListItem}>
                            <OKCircleSVG color={primaryColor} width={25} height={25}/>
                            <span>{text}</span>
                        </li>
                    ))
                }
            </ul>
            
            <div className={styles.btn}>
                <BrandButton
                    text={buttonText}
                    fill={fillButton}
                    fillColor={primaryColor}
                    svg={LeftToRightArrowSVG}
                    onClick={onClick}
                    color={secondaryColor ?? "white"}
                    backgroundColor={primaryColor}
                    fillForegroundColor={fillButtonForegroundColor ?? secondaryColor}
                />
            </div>
        </div>
    );
}
 
export default ProcessChecklistCard;