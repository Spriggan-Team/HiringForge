import styles from "./Title.module.css"


export interface TitleProps{
    title: string;
    fontSize?: string;
    className?: string;
}

const Title: React.FC<TitleProps> = ({
    title,
    fontSize,
    className
}) => { //fz around 18px
    return ( 
        <h1
            style={{
                fontSize: fontSize ?? "1.125rem" //-18px
            }}
            className={`${styles.title} ${className}`}
        >
            {title}
        </h1>
    );
}
 
export default Title;