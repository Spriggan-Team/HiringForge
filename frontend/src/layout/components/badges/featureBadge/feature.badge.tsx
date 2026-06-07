
import styles from "./style.module.css"



interface FeatureBadgeProps{
    title: string;
    description: string;
    svg: React.FC<React.SVGProps<SVGSVGElement>>
}

const FeatureBadge: React.FC<FeatureBadgeProps> = ({
    title,
    description,
    svg: Icon
}) => {
    return (
        <div className={styles.container}>
            {Icon && <Icon width={40} height={40} />}
            <div>
                <span className={styles.title}>{title}</span>
                <p className={styles.description}>
                    {description}
                </p>
            </div>
        </div>
    );
}
 
export default FeatureBadge;