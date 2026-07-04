

//-- Css styles
import styles from "./TagList.module.css"


interface TagListProps{
    tags: string[];
}


const TagList : React.FC<TagListProps> = ({
    tags
}) => {
    return (
        <div className={styles.tags}>
            {tags.map((item, key)=>(
                <span
                    key={key} 
                    className={styles.tag}
                >
                    {item}
                </span>
            ))}
        </div>
    );
}

 
export default TagList;