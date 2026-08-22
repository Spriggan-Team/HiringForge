import { useEffect } from "react";

//-- External Sercives
import StarterKit from "@tiptap/starter-kit";
import Placeholder from "@tiptap/extension-placeholder";
import { Editor, EditorContent, useEditor, type JSONContent } from "@tiptap/react";
import Link from "@tiptap/extension-link";


//- SVG Components
import ListSVGComponent from "/src/assets/svg/catalog/list-ul-svgrepo-com.svg"
import LinkSVGComponent from "/src/assets/svg/net/link-alt-svgrepo-com.svg"

//-- CSS Module
import styles from "./TipTapEditor.module.css"



interface TipTapEditorProps<T extends string | JSONContent> {
    value: T;
    setValue: (val: T) => void;
    format?: "html" | "json";

    placeholder?: string;
    className?: string;
    width?: string;

    sizeable?: {
        x?: boolean;
        y?: boolean;
        both?: boolean;
    };
}


const TipTapEditor = <T extends string | JSONContent, >({
    value,
    setValue,
    format= "json",

    width,
    sizeable,
    className,
    placeholder = "Start writing here...",
}: TipTapEditorProps<T>)  => {
    const editor = useEditor({
        extensions: [
            StarterKit,
            Placeholder.configure({
                placeholder: placeholder,
            }),
            Link.configure({
                openOnClick: false,
                autolink: true,
                defaultProtocol: "https"
            })
        ],
        content: value, 
        onUpdate({ editor }) {
            //-- Determine output type
            if (format === "html") {
                setValue(editor.getHTML() as T);
            } else {
                setValue(editor.getJSON() as T);
            }
        },
    });

    //-- Deal with update of value
    useEffect(() => {
        if (!editor) return;

        //-- converting
        const currentContent = typeof value === "string" ? editor.getHTML() : JSON.stringify(editor.getJSON());
        const incomingContent = typeof value === "string" ? value : JSON.stringify(value);

        if (currentContent !== incomingContent) {
            editor.commands.setContent(value, { emitUpdate: false }); //-- prevent useless event on update
        }
    }, [value, editor]);

    if (!editor) return null;


    return (
        <div
            style={{
                width: width ?? "100%",
                minWidth: 0,
            }}
            className={`
                ${styles.container}
                ${className ?? ""}
                card-border
            `}
        >
            <Toolbar editor={editor} />
            <div className={styles.separator} />
            <EditorContent
                editor={editor}
                className={`
                    ${styles.editor}
                    ${sizeable?.y 
                        ? styles.flexibleY 
                        : ""
                    }
                    ${sizeable?.x 
                        ? styles.flexibleX 
                        : ""
                    }
                    ${sizeable?.both 
                        ? styles.flexibleBoth 
                        : ""
                    }
                `}
            />
        </div>
    );
};


export default TipTapEditor;


/* -------------------------------------------------------------------------- */

const Toolbar = ({ editor }: { editor: Editor }) => (
    <div className={styles.toolbar}>
        <button
            type="button"
            className={`${styles.button} ${
                editor.isActive("bold") ? styles.active : ""
            }`}
            onClick={() => editor.chain().focus().toggleBold().run()}
        >
            <strong>B</strong>
        </button>

        <button
            type="button"
            className={`${styles.button} ${
                editor.isActive("italic") ? styles.active : ""
            }`}
            onClick={() => editor.chain().focus().toggleItalic().run()}
        >
            <em>I</em>
        </button>

        <button
            type="button"
            className={`${styles.button} ${
                editor.isActive("bulletList") ? styles.active : ""
            }`}
            onClick={() => editor.chain().focus().toggleBulletList().run()}
        >
            <ListSVGComponent
                width={15}
                height={15}
                className={styles.icon}
            />
        </button>

        <button
            type="button"
            className={`${styles.button} ${
                editor.isActive("link") ? styles.active : ""
            }`}
            onClick={async () => {
                const previousUrl = editor.getAttributes("link").href ?? "";
                const url = (await navigator.clipboard.readText()).trim();

                if (!url) {
                    editor.chain().focus().unsetLink().run();
                    return;
                }

                //-- revert link
                if (previousUrl === url) {
                    editor
                        .chain()
                        .focus()
                        .extendMarkRange("link")
                        .unsetLink()
                        .run();

                    return;
                }

                //-- insert lin
                editor
                    .chain()
                    .focus()
                    .extendMarkRange("link")
                    .setLink({ href: url })
                    .run();
            }}
        >
            <LinkSVGComponent
                width={15}
                height={15}
                className={styles.icon}
            />
        </button>
    </div>
);