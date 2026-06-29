

import { createContext, useContext, useState } from "react";

//-- SVG- Components
import DownArrowSVG from "/src/assets/svg/arrows/down-arrow-5-svgrepo-com.svg"

//-- CSS module
import styles from "./MenuDrawer.module.css"


/**----- Drawer Context ---- */

interface DrawerContextProps {
    isOpen: boolean;
    setIsOpen: React.Dispatch<React.SetStateAction<boolean>>;
    toggle: () => void;

    selected: any;
    setSelected: (value: any) => void;
}

const DrawerContext = createContext<DrawerContextProps | null>(null);

export const useDrawer = () => {
    const ctx = useContext(DrawerContext);
    if (!ctx) {
        throw new Error("useDrawer must be used inside <MenuDrawer>");
    }
    return ctx;
};


/**----- MenuDrawer  ---- */

interface MenuDrawerProps {
    children: React.ReactNode;
    defaultValue?: any;
    className?: string;
    style?: React.CSSProperties,
    onChange?: (value: any) => void;
}


const MenuDrawer: React.FC<MenuDrawerProps> = ({
    children,
    defaultValue,
    onChange,
    
    className,
    style,
}) => {
    const [isOpen, setIsOpen] = useState(false);
    const [selected, setSelectedState] = useState(defaultValue);

    const toggle = () => setIsOpen((p) => !p);

    const setSelected = (value: any) => {
        setSelectedState(value);
        onChange?.(value);
    };

    return (
        <DrawerContext.Provider
            value={{
                isOpen,
                setIsOpen,
                toggle,
                selected,
                setSelected,
            }}
        >
            <div 
                style={style}
                className={`${styles.container} ${className}`}
            >
                {children}
            </div>
        </DrawerContext.Provider>
    );
};

export default MenuDrawer;



/**-- MenuDrawerTriggerProps --  */

interface MenuDrawerTriggerProps {
    className?: string;
    style?: React.CSSProperties,
    children: (selected: any) => React.ReactNode;
}

export const MenuDrawerTrigger: React.FC<MenuDrawerTriggerProps> = ({
    className,
    style,
    children,
}) => {
    const { isOpen, toggle, selected } = useDrawer();

    return (
        <button
            style={style}
            type="button"
            onClick={toggle}
            className={`${styles.trigger} ${className}`}
            aria-expanded={isOpen}
        >
            <span>{children(selected)}</span>

            <DownArrowSVG
                width={14}
                height={14}
                className={`${styles.arrow} ${isOpen ? styles.open : ""}`}
            />
        </button>
    );
};



/** -- MenuDrawerBody -- */

interface MenuDrawerBodyProps {
    className?: string;
    children: React.ReactNode;
    
    style?: React.CSSProperties,
}

export const MenuDrawerBody: React.FC<MenuDrawerBodyProps> = ({
    children,
    className, style,
}) => {
    const { isOpen } = useDrawer();

    if (!isOpen) return null;

    return (
        <div 
            className={styles.bodyWrapper}
        >
            <div 
                style={style}
                className={`${styles.body} ${className}`}
            >
                {children}
            </div>
        </div>
    );
};




/**--- MenuDrawerItem -- */

interface MenuDrawerItemProps {
    className?: string;
    style?: React.CSSProperties,

    value: any;
    children: React.ReactNode;
}

export const MenuDrawerItem: React.FC<MenuDrawerItemProps> = ({
    className,
    style,

    value,
    children,
}) => {
    const { setSelected, setIsOpen } = useDrawer();

    return (
        <div
            style={style}
            className={`${styles.item} ${className}`}
            onClick={() => {
                setSelected(value);
                setIsOpen(false);
            }}
        >
            {children}
        </div>
    );
};

/**-- MenuDrawerInput -- */

interface MenuDrawerInputProps {
    placeholder?: string;
    formatter?: (value: string) => any;

    className?: string;
    style?: React.CSSProperties,
}

export const MenuDrawerInput: React.FC<MenuDrawerInputProps> = ({
    placeholder,
    formatter,

    style,
    className,
}) => {
    const { setSelected, setIsOpen } = useDrawer();
    const [value, setValue] = useState("");

    return (
        <input
            style={style}
            className={`${styles.input} ${className}`}
            placeholder={placeholder}
            value={value}
            onChange={(e) => setValue(e.target.value)}
            onKeyDown={(e) => {
                if (e.key === "Enter") {
                    const finalValue = formatter
                        ? formatter(value)
                        : value;

                    setSelected(finalValue);
                    setIsOpen(false);
                }
            }}
        />
    );
};