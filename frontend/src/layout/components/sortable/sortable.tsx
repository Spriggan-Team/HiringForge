
import {
    Children,
    forwardRef,
    isValidElement,
    useCallback,
    useEffect,
    useImperativeHandle,
    useRef,
    useState,
} from "react";


//-- CSS Styles
import styles from "./Sortable.module.css";



export type SortableDirection = "column" | "row";


export interface SortableHandle {
    /** Current order as an array of original indices */
    getOrder: () => number[];

    /** Reset to original order */
    reset:    () => void;
}


export interface SortableProps {
    /** Direct children — each becomes an independently draggable node */
    children:     React.ReactNode;

    /** Layout direction (default: "column") */
    direction?:   SortableDirection;

    /** control space between children */
    gap?:         number;
    
    /** Duration in ms before drag activates on pointer hold (default: 0 = immediate) */
    activationDelay?: number;

    /** Extra className on the wrapper — for spacing / context only */
    className?:   string;

    /**
     * Called after every reorder.
     * e.g. [2, 0, 1] means: child originally at index 2 is now first, etc.
     */
    onReorder?:   (order: number[]) => void;
}



/** Build an identity order array [0, 1, 2, …, n-1] */
const identity = (n: number) => Array.from({ length: n }, (_, i) => i);



export const Sortable = forwardRef<SortableHandle, SortableProps>(
    (
        {
            children,
            direction        = "column",
            gap              = 0,
            activationDelay  = 0,
            className,
            onReorder,
        },
        ref,
    ) => {
        const childArray = Children.toArray(children).filter(isValidElement);
        const count      = childArray.length;

        // order[displayPosition] = originalIndex
        const [order, setOrder] = useState<number[]>(() => identity(count));

        // Reset order if child count changes
        useEffect(() => {
            setOrder(identity(count));
        }, [count]);

        // -- Drag state (all in refs — no re-render during drag) ──
        const draggingIdx      = useRef<number | null>(null); // current display index being dragged
        const ghostEl          = useRef<HTMLDivElement | null>(null);

        const placeholderIdx   = useRef<number | null>(null); // display index of the drop slot
        const itemRefs         = useRef<(HTMLDivElement | null)[]>([]);
        const activationTimer  = useRef<ReturnType<typeof setTimeout> | null>(null);

        const activationFired  = useRef(false);
        const pointerOrigin    = useRef<{ x: number; y: number }>({ x: 0, y: 0 });
        const offsetWithinItem = useRef<{ x: number; y: number }>({ x: 0, y: 0 });


        //-- Force re-render only when the placeholder slot changes (smooth visual reflow)
        const [liveOrder, setLiveOrder] = useState<number[]>(() => identity(count));


        // -- Imperative handle 
        useImperativeHandle(ref, () => ({
            getOrder: () => [...order],
            reset:    () => {
                setOrder(identity(count));
                setLiveOrder(identity(count));
                onReorder?.(identity(count));
            },
        }), [order, count, onReorder]);


        // -- Ghost element 


        const createGhost = useCallback((
            sourceEl: HTMLDivElement,
            clientX:  number,
            clientY:  number,
        ) => {
            const rect   = sourceEl.getBoundingClientRect();
            const ghost  = sourceEl.cloneNode(true) as HTMLDivElement;

            ghost.style.cssText = `
                position:       fixed;
                left:           ${rect.left}px;
                top:            ${rect.top}px;
                width:          ${rect.width}px;
                height:         ${rect.height}px;
                pointer-events: none;
                z-index:        9999;
                opacity:        0.85;
                transform:      scale(1.02);
                transition:     transform 80ms ease, box-shadow 80ms ease;
                box-shadow:     0 8px 24px rgba(0,0,0,0.12);
            `;

            document.body.appendChild(ghost);
            ghostEl.current = ghost;

            offsetWithinItem.current = {
                x: clientX - rect.left,
                y: clientY - rect.top,
            };
        }, []);


        const removeGhost = useCallback(() => {
            ghostEl.current?.remove();
            ghostEl.current = null;
        }, []);


        // -- Find which slot the pointer is hovering 


        const findSlot = useCallback((
            clientX: number,
            clientY: number,
            currentDragging: number,
        ): number => {
            let best      = currentDragging;
            let bestDist  = Infinity;

            liveOrder.forEach((_, displayIdx) => {
                if (displayIdx === currentDragging) return;

                const el = itemRefs.current[displayIdx];
                if (!el) return;

                const rect   = el.getBoundingClientRect();
                const midX   = rect.left + rect.width  / 2;
                const midY   = rect.top  + rect.height / 2;
                const dist   = direction === "column"
                    ? Math.abs(clientY - midY)
                    : Math.abs(clientX - midX);

                if (dist < bestDist) {
                    bestDist = dist;
                    best     = displayIdx;
                }
            });

            return best;
        }, [liveOrder, direction]);



        // --- Apply reorder 


        const applyReorder = useCallback((
            fromIdx: number,
            toIdx:   number,
            committed: boolean,
        ) => {
            const next = [...order];
            const [moved] = next.splice(fromIdx, 1);
            next.splice(toIdx, 0, moved);

            if (committed) {
                setOrder(next);
                onReorder?.(next);
            }
            setLiveOrder(next);
        }, [order, onReorder]);



        // -- Pointer event handlers 

        const onPointerDown = useCallback((
            e:          React.PointerEvent<HTMLDivElement>,
            displayIdx: number,
        ) => {
            if (e.button !== 0 && e.pointerType === "mouse") return;
            e.currentTarget.setPointerCapture(e.pointerId);

            pointerOrigin.current   = { x: e.clientX, y: e.clientY };
            activationFired.current = false;

            const el = itemRefs.current[displayIdx];

            const arm = () => {
                activationFired.current = true;
                draggingIdx.current     = displayIdx;
                placeholderIdx.current  = displayIdx;

                if (el) createGhost(el, e.clientX, e.clientY);

                // Force wrapper cursor while dragging
                document.body.style.cursor = direction === "column" ? "grabbing" : "ew-resize";
            };

            if (activationDelay === 0) 
                arm();
            else 
                activationTimer.current = setTimeout(arm, activationDelay);
        }, [activationDelay, direction, createGhost]);



        const onPointerMove = useCallback((e: PointerEvent) => {
            if (!activationFired.current || draggingIdx.current === null) return;

            // Move ghost
            if (ghostEl.current) {
                ghostEl.current.style.left = `${e.clientX - offsetWithinItem.current.x}px`;
                ghostEl.current.style.top  = `${e.clientY - offsetWithinItem.current.y}px`;
            }

            //-- Find new slot
            const slot = findSlot(e.clientX, e.clientY, draggingIdx.current);

            if (slot !== placeholderIdx.current) {
                placeholderIdx.current = slot;
                applyReorder(draggingIdx.current, slot, false);
                // Update draggingIdx to follow the item as it reflows
                draggingIdx.current = slot;
            }
        }, [findSlot, applyReorder]);



        const onPointerUp = useCallback(() => {
            //-- Cancel activation timer if drag never fired
            if (activationTimer.current) {
                clearTimeout(activationTimer.current);
                activationTimer.current = null;
            }

            if (!activationFired.current) {
                activationFired.current = false;
                draggingIdx.current     = null;
                return;
            }

            //-- Commit
            if (draggingIdx.current !== null && placeholderIdx.current !== null) {
                applyReorder(draggingIdx.current, placeholderIdx.current, true);
            }

            draggingIdx.current     = null;
            placeholderIdx.current  = null;
            activationFired.current = false;

            removeGhost();
            document.body.style.cursor = "";
        }, [applyReorder, removeGhost]);



        // -- Global listeners (pointer may leave item during drag) ──
        useEffect(() => {
            window.addEventListener("pointermove", onPointerMove);
            window.addEventListener("pointerup",   onPointerUp);
            return () => {
                window.removeEventListener("pointermove", onPointerMove);
                window.removeEventListener("pointerup",   onPointerUp);
            };
        }, [onPointerMove, onPointerUp]);


        // Render

        return (
            <div
                className={`${styles.sortable} ${styles[direction]} ${className ?? ""}`}
                style={{ gap: gap ? `${gap}px` : undefined }}
            >
                {liveOrder.map((originalIdx, displayIdx) => {
                    const child     = childArray[originalIdx];
                    const isDragging = draggingIdx.current === displayIdx && activationFired.current;

                    return (
                        <div
                            key={originalIdx}
                            ref={el => { itemRefs.current[displayIdx] = el; }}
                            className={`${styles.item} ${isDragging ? styles.dragging : ""}`}
                            onPointerDown={e => onPointerDown(e, displayIdx)}
                        >
                            {child}
                        </div>
                    );
                })}
            </div>
        );
    },
);


Sortable.displayName = "Sortable";