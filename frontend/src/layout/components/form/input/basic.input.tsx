
import React, { useRef } from "react";
import styles from "./style.module.css";


interface BasicInputProps {
    inputName?: string;
    label?: string;
    textColor?: string;
    width?: number | string;
    padding?: number;
    placeholder?: string;
    onChange?: ()=>void;
    backgroundColor?: string;
    type?: "text" | "password";
    svg: React.FC<React.SVGProps<SVGSVGElement>>;
}



/**
 * A minimal component for built-in input.
 */
const BasicInput: React.FC<BasicInputProps> = ({
    svg: Icon,
    label,
    width,
    type = "text",
    placeholder,
    inputName,
    onChange = ()=>{}
}) => {
    const inputRef = useRef<HTMLInputElement>(null);
    
    return (
        <div className={styles.container}>
            {label && <label htmlFor={inputName} className={styles.label} >{label}</label>}
            <div
                className={styles.inputSection}
                style={{ 
                    background: "#ECEAF1",  
                    ["--width" as string]: (width && typeof width == 'number' ?  `${width}px` : width) ?? "252px"  
                }}
            >
                <Icon className={styles.svg} />

                <input
                    ref={inputRef}
                    name={inputName}
                    type={type}
                    placeholder={
                        placeholder ? placeholder 
                        : type == "password" ?  
                            "••••••••"
                            : ""
                    }
                    className={styles.input}
                    onChange={onChange}
                />
                { type == "password" && 
                    <button
                        className={styles.btn}
                        onClick={()=>{
                            const input = inputRef.current;
                            if(input){
                                if(input.type === "password")
                                    input.type = "text";
                                else
                                    input.type = "password";
                            }
                        }}
                    >
                        👁
                    </button>
                }
            </div>
        </div>
    );
};

export default BasicInput;