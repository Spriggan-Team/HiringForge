
import React, { useRef, useState } from "react";

import PasswordSVG from '/src/assets/svg/security/password-svgrepo-com.svg';
import EyeClosedSVG from "/src/assets/svg/toggle/close.eye.svg";
import EyeOpenSVG from  "/src/assets/svg/toggle/open.eye.svg";
import InputLabel from "./input.label";

import styles from "./BasicInput.module.css";


export interface BasicInputProps {
    value?: any;
    label?: string;
    inputName?: string;

    width?: number | string;
    padding?: string | number;
    placeholder?: string;
    backgroundColor?: string;
    borderRadius?: number | string;
    
    type?: "text" | "password" | string;
    textColor?: string;
    required?: boolean;
    extraInputProps?: React.HTMLAttributes<HTMLInputElement>
    
    className?: string;
    iconClassName?: string;
    icon2ClassName?: string;

    enableViewToggle?: boolean; //-- work with password inputs types
    enableFocusWithinDefaultDesign?: boolean; //-- controle focus state

    svg?: React.FC<React.SVGProps<SVGSVGElement>>;
    leadingSVG?: React.FC<React.SVGProps<SVGSVGElement>>;
    leading?: React.ReactNode;

    inputRef?: React.RefObject<null | HTMLInputElement>;
    onChange?: (event: React.ChangeEvent<HTMLInputElement>)=>void;
    onBlur?: (event: React.FocusEvent<HTMLInputElement>)=> void;
    onFocus?: (event: React.FocusEvent<HTMLInputElement>)=> void;
}



/**
 * A minimal component for built-in input.
 */
const BasicInput: React.FC<BasicInputProps> = ({
    svg: Icon,
    leading: LeadingComponent,
    leadingSVG: Icon2,

    label,
    value,

    width,
    type = "text",
    placeholder,
    inputName,
    required,

    padding,
    className,
    borderRadius,

    inputRef: ref,
    onBlur, onFocus,
    onChange = ()=>{},

    enableViewToggle = true,
    enableFocusWithinDefaultDesign = true,

    iconClassName,
    icon2ClassName,
    backgroundColor = "#ECEAF1",

    extraInputProps
}) => {
    const inputRef = ref ?? useRef<HTMLInputElement>(null);
    
    const [isPasswordVisible, setIsPasswordVisible] = useState(false);
    const currentType = type === "password" && isPasswordVisible ? "text" : type;


    return (
        <div 
            className={`${styles.container} ${enableFocusWithinDefaultDesign ? styles.inputContainerFocusStyle : ""}`}
        >
            {label && <InputLabel className={styles.label} label={label}/> }
            <div
                className={`${styles.inputSection} ${className} ${enableFocusWithinDefaultDesign ? styles.inputSectionFocusStyle : ""}`}
                style={{ 
                    background: backgroundColor,
                    ["--border" as any]:(borderRadius && typeof borderRadius == 'number' ?  `${borderRadius}px` : borderRadius) ??  "8px",  
                    ["--width" as string]: (width && typeof width == 'number' ?  `${width}px` : width) ?? "252px" ,
                    ["--padding" as string]:  (padding && typeof padding == 'number' ?  `${padding}px` : padding) ?? "10px"
                }}
            >
                {Icon ? <Icon className={`${styles.svg} ${iconClassName}`} /> : type === "password" && <PasswordSVG className={styles.svg}/>}
                
                <input
                    ref={inputRef}
                    name={inputName}
                    type={currentType}
                    placeholder={placeholder ? placeholder : type === "password" ? "••••••••" : ""}
                    className={`${styles.input} input-placeholder`}
                    onChange={onChange}
                    onFocus={onFocus}
                    onBlur={onBlur}
                    value={value}
                    {...extraInputProps}
                    required={required}
                />
                
                {type === "password" && enableViewToggle && (
                    <button
                        type="button"
                        className={styles.btn}
                        onClick={() => setIsPasswordVisible(!isPasswordVisible)} // Changement d'état propre
                    >
                        {isPasswordVisible ? (
                            <EyeClosedSVG className={styles.btnSvg} />
                        ) : (
                            <EyeOpenSVG className={styles.btnSvg} />
                        )}
                    </button>
                )}
                {LeadingComponent ? LeadingComponent : null }
                {Icon2 && <Icon2 className={`${styles.svg} ${icon2ClassName}`} />}
            </div>
        </div>
    );
};

export default BasicInput;