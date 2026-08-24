//-- SVG Compoenents
import CloseSVGComponent from "/src/assets/svg/menu/close-svgrepo-com.svg?react"

//-- CSS Modules
import styles from "./JobSkill.module.css"


interface SkillProps{
    content: string;

    color?:string;
    className?: string;
    backgroundColor?: string;
    svgHoverBackgroundColor?:string;

    onClose?: (content: string)=>void;
}

const JobSkill: React.FC<SkillProps> = ({
    content,
    onClose,

    color,
    className,
    backgroundColor,

    svgHoverBackgroundColor
}) => {
    if(!content)
        return null;
    
    return (
        <div
            style={{
                ["--color" as string]: color ?? "#4338CA",
                ["--bgColor" as string]:  backgroundColor ?? "#EEF2FF",
            }} 
            className={`${styles.container} ${className}`}
        >
            <span className={styles.content}>{content}</span>
            {
                onClose && (
                    <button 
                        type="button"
                        className={styles.closeButton}
                        onClick={() => onClose(content)}
                        aria-label={`Supprimer ${content}`}
                    >
                        <CloseSVGComponent 
                            width={10}
                            height={10}
                            className={styles.svg}
                        />
                    </button>
                )
            }
        </div>
    );
}
 
export default JobSkill;