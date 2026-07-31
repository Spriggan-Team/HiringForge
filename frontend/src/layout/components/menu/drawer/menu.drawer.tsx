

import { 
    createContext,
    useCallback,
    useContext,
    useEffect,
    useState
} from "react";

//-- SVG- Components
import DownArrowSVG from "/src/assets/svg/arrows/down-arrow-5-svgrepo-com.svg"

//-- CSS module
import styles from "./MenuDrawer.module.css"
import { globalBasicInputInput } from "../../form/input/basic.input";


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
    value?: any;

    children: React.ReactNode;
    defaultValue?: any;
    className?: string;
    style?: React.CSSProperties;

    triggerVisibility?: boolean;
    onChange?: (value: any) => void;
}


const MenuDrawer: React.FC<MenuDrawerProps> = ({
    value,
    children,
    defaultValue,

    onChange,
    triggerVisibility,
    
    style,
    className,

}) => {
    const [isOpen, setIsOpen] = useState(false);
    const [selected, setSelectedState] = useState(defaultValue);

    const toggle = () => setIsOpen((p) => !p);

    const setSelected = (value: any) => {
        setSelectedState(value);
        onChange?.(value);
    };


    useEffect(()=>{
        if(triggerVisibility)
            setIsOpen(triggerVisibility)
    },[triggerVisibility]);

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
    className?: | string | ((selected: any)=> string);
    iconClassName?: string;
    style?: React.CSSProperties;

    displayArrowDown?: boolean;
    applyDefaultStyle?: boolean;
    disableCursorPointer?: boolean;

    children:
        | React.ReactNode
        | ((selected: any) => React.ReactNode);
    leading?:  React.ReactNode;
}


export const MenuDrawerTrigger: React.FC<MenuDrawerTriggerProps> = ({
    style,
    className,

    displayArrowDown = true,
    applyDefaultStyle= true,
    disableCursorPointer = false,

    children,
    leading: Leading,
    iconClassName,
}) => {
    const { isOpen, toggle, selected } = useDrawer();

    return (
        <button
            style={{
                ...(style ?? {}),
                cursor: disableCursorPointer ? "default" : "pointer"
            }}
            type="button"
            onClick={toggle}
            aria-expanded={isOpen}
            className={`
                ${applyDefaultStyle ? styles.trigger : ""}
                ${typeof className === "function" ? className(selected) : className}
            `}
        >
            <span>
                {
                    typeof children === "function" ?
                        children(selected)
                        : children
                }
            </span>
            <div className={`${styles.leading} ${iconClassName}`}>
                {Leading ? 
                    Leading
                    : displayArrowDown && (
                        <DownArrowSVG
                            width={14}
                            height={14}
                            className={`${styles.arrow} ${isOpen ? styles.open : ""}`}
                        />
                    )
                }
            </div>
        </button>
    );
};



/** -- MenuDrawerBody -- */

interface MenuDrawerBodyProps {
    className?: string;

    children: React.ReactNode;
    applyDefaultStyle?: boolean;
    
    left?: string | number;
    right?: string | number;

    style?: React.CSSProperties,
    position?: "top-right" | "initial-absolute" | "initial"
}


export const MenuDrawerBody: React.FC<MenuDrawerBodyProps> = ({
    children,
    
    left,
    right,

    style={},
    className,

    applyDefaultStyle = true, 
    position = "top-right"
}) => {
    const { isOpen } = useDrawer();

    if (!isOpen) return null;

    return (
        <div
            style={{
                ...style,
                ["--menu-body-wrapper-left" as string]: (typeof left === "number" ? `${left}px` : left) ?? 0,
                ["--menu-body-wrapper-right" as string]: (typeof right === "number" ? `${right}px` : right) ?? 0,
            }}
            className={`
                ${applyDefaultStyle ? styles.bodyWrapper : ""} 
                ${position === "top-right" ? 
                        styles.topLeft 
                        : position == "initial-absolute"
                            ? styles.absoluteBottom
                            : styles.initial
                }
            `}
        >
            <div 
                style={style}
                className={`${applyDefaultStyle ? styles.body : ""} ${className}`}
            >
                {children}
            </div>
        </div>
    );
};




/**--- MenuDrawerItem -- */

interface MenuDrawerItemProps {
    className?: string;
    applyDefaultStyle?: boolean;
    style?: React.CSSProperties,

    value?: any;
    children: React.ReactNode;

    onClick?: (e: React.MouseEvent)=>void;     //-- helps detect click ( e.g: it is used for define custom behaviour )
    selectionStateTriggerer?: boolean;  //-- determine how an drawer item should be selected when its default behaviour is disable
    disableDefaultBehaviour?: boolean; //-- disabling default drawer item behaviour means desactivating selection on click
}


export const MenuDrawerItem: React.FC<MenuDrawerItemProps> = ({
    style,
    className,
    applyDefaultStyle = true,
    value = null,
    children,

    onClick,
    selectionStateTriggerer,
    disableDefaultBehaviour = false,
}) => {
    const { setSelected, setIsOpen } = useDrawer();

    const handleSelect = useCallback((e?: React.MouseEvent) => {
        if (!disableDefaultBehaviour) {
            setSelected(value);
            setIsOpen(false);
        }

        onClick?.(e as any);
    }, [disableDefaultBehaviour, setSelected, setIsOpen, value, onClick]);


    useEffect(() => {
        if (selectionStateTriggerer) {
            handleSelect();
        }
    }, [selectionStateTriggerer, handleSelect]);

    return (
        <div
            style={style}
            className={`
                ${className}
                ${applyDefaultStyle ? styles.item : ""}
            `}
            onClick={handleSelect}
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



//-----------------------------
//---- Global view on drawer (app design)
//------------------------------

type DrawerItem<T = unknown> = {
    key?: string | number;
    value: T;
    label: React.ReactNode;
};


type DrawerBuilderProps<T = unknown> = {
    value?: T;
    defaultValue?: T;

    items: DrawerItem<T>[];

    onChange: (value: T) => void;

    placeholder?: React.ReactNode;

    renderValue?: (value?: T) => React.ReactNode;
    renderItem?: (item: DrawerItem<T>) => React.ReactNode;

    triggerProps?: Omit<
        React.ComponentProps<typeof MenuDrawerTrigger>,
        "children"
    >;

    bodyProps?: Omit<
        React.ComponentProps<typeof MenuDrawerBody>,
        "children"
    >;

    itemProps?: Omit<
        React.ComponentProps<typeof MenuDrawerItem>,
        "children"
    >;
};

export function DrawerBuilder<T = unknown>({
    value,
    defaultValue,

    items,
    onChange,
    placeholder,

    renderValue,
    renderItem,

    triggerProps = {},
    bodyProps = {},
    itemProps = {},
}: DrawerBuilderProps<T>) {

    return (
        <MenuDrawer
            value={value}
            onChange={onChange}
            defaultValue={defaultValue}
        >
            <MenuDrawerTrigger
                {...globalBasicInputInput}
                {...triggerProps}
                className={(selected) =>
                    `${globalBasicInputInput.className} ${
                        !selected ? "input-like-placeholder" : ""
                    } ${
                        typeof triggerProps.className === "function"
                            ? triggerProps.className(selected)
                            : triggerProps.className ?? ""
                    }`
                }
            >
                {(selected) => {

                    if (renderValue)
                        return renderValue(selected);

                    return (
                        <span
                            className={
                                selected
                                    ? `${styles.activate} activate`
                                    : ""
                            }
                        >
                            {selected ?? placeholder}
                        </span>
                    );
                }}
            </MenuDrawerTrigger>

            <MenuDrawerBody
                left={0}
                right={0}
                position="initial-absolute"
                className={`${styles.selectDropdown} selectDropdown ${
                    bodyProps.className ?? ""
                }`}
                {...bodyProps}
            >
                {items.map((item, index) => (

                    <MenuDrawerItem
                        key={item.key ?? index}
                        value={item.value}
                        className={`${styles.selectItem} selectItem ${
                            itemProps.className ?? ""
                        }`}
                        {...itemProps}
                    >
                        {renderItem
                            ? renderItem(item)
                            : item.label}
                    </MenuDrawerItem>

                ))}
            </MenuDrawerBody>

        </MenuDrawer>
    );
}