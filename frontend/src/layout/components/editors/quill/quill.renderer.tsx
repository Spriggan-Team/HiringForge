
import { QuillDeltaToHtmlConverter } from "quill-delta-to-html";

//-- CSS Styles
import styles from "./QuillRenderer.module.css"


interface QuillRendererProps{
    content: any;
}

const QuillRenderer: React.FC<QuillRendererProps> = ({
    content
}) => {
    const converter = new QuillDeltaToHtmlConverter(content.ops || [], {});
    const html = converter.convert();

    return (
        <div 
            className={styles.container}
            dangerouslySetInnerHTML={{ __html: html }}
        />
    );
}
 
export default QuillRenderer;