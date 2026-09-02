
import styles from "./TopBarNavigatin.module.css"


export interface TopBarNavigationProps{
    options: TopBarNavigationOptions[];
    onClick?: ()=>void;
    activeColor?: string;
}


interface TopBarNavigationOptions{
    text: string;
    count?: number;
    current?: boolean;
    onClick?: ()=>void;
    key?:string;
}


const TopBarNavigation: React.FC<TopBarNavigationProps> = ({
    options = [],
    onClick,
    activeColor
}) => {
    return (
        <div className={styles.container}>
            {
                options.map((item, index)=>(
                    <div 
                        key={index}
                        onClick={()=>{
                            if(item.onClick)
                                item.onClick();
                            else
                                onClick?.();
                        }}
                        style={{
                            ["--active-color" as string]: activeColor ?? "#2563EB"
                        }}
                        className={`${styles.item} ${item.current ? styles.activate : ""}`}
                    >
                        <span 
                            className={styles.label}
                        >
                            {item.text}
                        </span>
                        {item.count && (
                            <div className={styles.count}>
                                {item.count}
                            </div>
                        )}
                    </div>
                ))
            }
        </div>
    );
}
 
export default TopBarNavigation;