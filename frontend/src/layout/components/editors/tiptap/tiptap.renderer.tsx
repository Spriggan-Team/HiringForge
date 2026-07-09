

import { EditorContent, useEditor } from "@tiptap/react";
import StarterKit from "@tiptap/starter-kit";

//-- CSS Styles
import styles from "./TipTapRenderer.module.css"


interface TipTapRendererProps{
    content: object | string;
    className?: string;
}


const TipTapRenderer: React.FC<TipTapRendererProps> = ({
    content, className
}) => {
    const editor = useEditor({
        editable: false,
        extensions: [
            StarterKit
        ],
        content,
        immediatelyRender: false
    })

    return (
        <div className={`${styles.container} ${className}`}>
            <EditorContent editor={editor} />
        </div>
    );
}
 
export default TipTapRenderer;