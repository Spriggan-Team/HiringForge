import React, {
    Children,
    createContext,
    forwardRef,
    isValidElement,
    useCallback,
    useContext,
    useEffect,
    useImperativeHandle,
    useRef,
    useState,
} from "react";

//-- CSS checkbox input
import CheckBoxInput from "../checkbox/checkbox.input";

//-- CSS modules
import styles from "./Selection.module.css";




export interface SelectionContextHandle {
    exitSelection: () => SelectionResult; /** Programmatically/force (an) exit selection mode and return selected keys */
    
    selectedKeys: string[];  /** Currently selected keys */
    isSelecting: boolean;  /** Whether selection mode is currently active */
}

export interface SelectionResult {
    keys:     string[];
    elements: HTMLElement[];
}


export interface SelectionContextProps {
    children: React.ReactNode; /** Selectable children */
    longPressDuration?: number; /** Long-press delay (ms) */

    getKey?: (index: number, child: React.ReactElement) => string; /** Custom item key */

    showCheckbox?: boolean; /** Display selection checkboxes */
    disableDefaultStyle?: boolean; /** Disable built-in selected styles */

    className?: string; /** Wrapper class */
    selectedClassName?: string; /** Selected item class */

    onSelectionEnd?: (result: SelectionResult) => void; /** Selection ended */
    onSelectionChange?: (result: SelectionResult) => void; /** Selection updated */
}



/**-- Context items --*/
interface InternalCtx {
    isSelecting:      boolean;
    selectedKeys:     Set<string>; //-- selected items keys

    showCheckbox:     boolean; //show or hide checkbox
    disableDefault:   boolean; //disable default styele

    selectedClass:    string | undefined;
    toggleKey:        (key: string, el: HTMLElement) => void;
    startSelection:   (key: string, el: HTMLElement) => void;
}


/**
 * Build Context for handling selectionnable items
 */


const Ctx = createContext<InternalCtx | null>(null);
const useSelCtx = () => {
    const c = useContext(Ctx);
    if (!c) 
        throw new Error("Must be inside SelectionContext");
    return c;
};



/**
 * Selection container
 */
export const SelectionContainer = forwardRef<SelectionContextHandle, SelectionContextProps>(
    (
        {
            children,
            getKey,
            longPressDuration = 500,
            showCheckbox      = false,
            disableDefaultStyle = false,
            className,
            selectedClassName,
            onSelectionChange,
            onSelectionEnd,
        },
        ref,
    ) => {
        const [isSelecting,  setIsSelecting]  = useState(false);
        const [selectedKeys, setSelectedKeys] = useState<Set<string>>(new Set());

        //-- Track DOM elements for each key so we can expose them via the handle
        const elementMap = useRef<Map<string, HTMLElement>>(new Map());

        //-- Prepare selection result
        const buildResult = useCallback((keys: Set<string>): SelectionResult => ({
            keys:     [...keys],
            elements: [...keys]
                .map(k => elementMap.current.get(k))
                .filter(Boolean) as HTMLElement[],
        }), []);


        // -- Toggle a single key
        const toggleKey = useCallback((key: string, el: HTMLElement) => {
            elementMap.current.set(key, el);

            setSelectedKeys(prev => {
                const next = new Set(prev);
                if (next.has(key)) next.delete(key);
                else               next.add(key);

                //-- exit selecton when last item deselected
                if (next.size === 0) {
                    setIsSelecting(false);
                    onSelectionEnd?.(buildResult(next));
                }
                else {
                    onSelectionChange?.(buildResult(next));
                }

                return next;
            });
        }, [buildResult, onSelectionChange, onSelectionEnd]);

        // -- trigger selection mode on long press
        const startSelection = useCallback((key: string, el: HTMLElement) => {
            elementMap.current.set(key, el);
            setIsSelecting(true);

            setSelectedKeys(prev => {
                const next = new Set(prev);
                next.add(key);

                onSelectionChange?.(buildResult(next));
                return next;
            });

        }, [buildResult, onSelectionChange]);

        //-- expose properties through ref
        useImperativeHandle(ref, () => ({
            exitSelection: () => {
                const result = buildResult(selectedKeys);

                setIsSelecting(false);
                setSelectedKeys(new Set());

                elementMap.current.clear();
                onSelectionEnd?.(result);

                return result;
            },
            isSelecting,
            selectedKeys: [...selectedKeys],
        }), [isSelecting, selectedKeys, buildResult, onSelectionEnd]);

        // -- Wrap each direct child
        const wrappedChildren = Children.map(children, (child, index) => {
            if (!isValidElement(child))
                return child;

            const key = getKey?.(index, child as React.ReactElement) ?? String(index);

            return (
                <SelectableItem
                    key={key}
                    itemKey={key}
                    longPressDuration={longPressDuration}
                >
                    {child}
                </SelectableItem>
            );
        });


        return (
            <Ctx.Provider value={{
                isSelecting,
                selectedKeys,
                showCheckbox,
                disableDefault:  disableDefaultStyle,
                selectedClass:   selectedClassName,
                toggleKey,
                startSelection,
            }}>
                <div className={`${styles.wrapper} ${className ?? ""}`}>
                    {wrappedChildren}
                </div>
            </Ctx.Provider>
        );
    },
);


SelectionContainer.displayName = "SelectionContainer";




/**
 * Slectionnable items
 * transform an item inside a selection container
 * into a selectionnable item.
 * The items is mark as selection after beeing pressed over a duration of time
 */

interface SelectableItemProps {
    itemKey:          string;
    longPressDuration: number;
    children:         React.ReactNode;
}



const SelectableItem: React.FC<SelectableItemProps> = ({
    itemKey,
    longPressDuration,
    children,
}) => {
    const {
        isSelecting,
        selectedKeys,
        showCheckbox,
        disableDefault,
        selectedClass,
        toggleKey,
        startSelection,
    } = useSelCtx();

    const itemRef    = useRef<HTMLDivElement>(null);
    const isSelected = selectedKeys.has(itemKey);
   
    const longPressFiredRef = useRef(false);
    const timerRef   = useRef<ReturnType<typeof setTimeout> | null>(null);
    
    // -- Press detection

    const onPointerDown = useCallback((e: React.PointerEvent) => {
        //-- In selection mode a simple tap toggles — no timer needed.
        if (isSelecting) return;

        if (e.pointerType === "mouse" && e.button !== 0) 
            return;

        longPressFiredRef.current = false;

        timerRef.current = setTimeout(() => {
            // Timer fired before pointer-up → long press confirmed
            longPressFiredRef.current = true;

            if (itemRef.current) {
                startSelection(itemKey, itemRef.current);
            }
        }, longPressDuration);
    }, [isSelecting, itemKey, longPressDuration, startSelection]);


    const cancelTimer = useCallback(() => {
        if (timerRef.current) {
            clearTimeout(timerRef.current);
            timerRef.current = null;
        }
    }, []);

    

    //-- Tap while in selection mode → toggle
    const onClick = useCallback((e: React.MouseEvent) => {
        //this click is the release at the end of a long-press.
        if (longPressFiredRef.current) {
            longPressFiredRef.current = false;
            e.preventDefault();
            e.stopPropagation();
            return;
        }

        //-- genuine tap while already in selection mode → toggle.
        if (!isSelecting) return;

        e.preventDefault();
        e.stopPropagation();

        if (itemRef.current) toggleKey(itemKey, itemRef.current);
    }, [isSelecting, itemKey, toggleKey]);


    useEffect(() => () => {
        if (timerRef.current) clearTimeout(timerRef.current);
    }, []);


    //-- CSS classes/state
    const classes = [
        styles.item,
        isSelecting && styles.selectionActive,
        isSelected && !disableDefault && styles.selected,
        isSelected && selectedClass,
    ].filter(Boolean).join(" ");


    return (
        <div
            ref={itemRef}
            className={classes}
            onPointerDown={onPointerDown}
            onPointerUp={cancelTimer}
            onPointerLeave={cancelTimer}
            onPointerCancel={cancelTimer}
            onClick={onClick}
            onContextMenu={e => { if (isSelecting) e.preventDefault(); }}
        >
            {/* Checkbox overlay */}
            {showCheckbox && isSelecting && (
                <div className={styles.checkboxOverlay}>
                    <CheckBoxInput
                        checked={isSelected}
                        onChange={() => {
                            if (itemRef.current) toggleKey(itemKey, itemRef.current);
                        }}
                    />
                </div>
            )}

            {/* Dim overlay when selected */}
            {isSelecting && !isSelected && (
                <div className={styles.dimOverlay} aria-hidden />
            )}

            {children}
        </div>
    );
};