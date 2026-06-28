import styles from "./Title.module.css"


export interface TitleProps{
    title: string;
    fontSize?: string;
}

const Title: React.FC<TitleProps> = ({
    title,
    fontSize
}) => { //fz around 18px
    return ( 
        <h1 className={styles.title}>{title}</h1>
    );
}
 
export default Title;