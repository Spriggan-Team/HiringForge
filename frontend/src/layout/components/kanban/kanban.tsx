import React, {
    createContext,
    useCallback,
    useContext,
    useEffect,
    useState,
} from "react";

import styles from "./style.module.css";


// -----------
// Types
// -----------

export interface CardData {
    id:       string;
    columnId: string;
}

interface DragState {
    cardId:   string;
    columnId: string;
}

interface DragContextValue {
    dragging:   DragState | null;
    overId:     string | null;
    startDrag:  (drag: DragState) => void;
    updateOver: (id: string | null) => void;
    commitDrop: (targetColumnId: string, beforeCardId: string | null) => void;
    abortDrag:  () => void;
}


// ----------
// Context
// ----------


const DragContext = createContext<DragContextValue | null>(null);

const useDrag = () => {
    const ctx = useContext(DragContext);
    if (!ctx) 
        throw new Error("useDrag must be used inside <KanbanBoard>");
    return ctx;
};

// --------------
// KanbanBoard — owns card order state, provides context
// ---------------

export interface KanbanBoardProps {
    className?:   string;
    isDraggable?: boolean;
    cards:        CardData[];
    ref?: React.Ref<HTMLDivElement>;
    onCardMove:   (moved: CardData, index: number ,cards: CardData[]) => void;
    children:     React.ReactNode;
    style?: React.CSSProperties
}

export const KanbanBoard: React.FC<KanbanBoardProps> = ({
    ref,
    className,
    isDraggable = true,
    cards,
    onCardMove,
    children,
    style,
}) => {
    const [dragging, setDragging] = useState<DragState | null>(null);
    const [overId,   setOverId]   = useState<string | null>(null);

    //-- start drag again
    const startDrag = useCallback((drag: DragState) => {
        if (!isDraggable) return;
        setDragging(drag);
    }, [isDraggable]);


    //-- Drag over handler
    const updateOver = useCallback((id: string | null) => setOverId(id), []);

    //-- Muate card[]
    const commitDrop = useCallback((
        targetColumnId: string,
        beforeCardId:   string | null,  // null = append at end of column
    ) => {
        if (!dragging) return;

        // Remove the dragged card from its current position
        const next  = cards.filter(c => c.id !== dragging.cardId);
        const moved = { id: dragging.cardId, columnId: targetColumnId };

        if (beforeCardId === null) {
            next.push(moved);
        }
        else {
            const idx = next.findIndex(c => c.id === beforeCardId);
            next.splice(idx === -1 ? next.length : idx, 0, moved);
        }

        onCardMove(moved, dragging.cardId ? Number(dragging.cardId) : -1, next);   // consumer updates their state → React re-renders
        setDragging(null);
        setOverId(null);
    }, [dragging, cards, onCardMove]);


    //-- Handling abort action
    const abortDrag = useCallback(() => {
        setDragging(null);
        setOverId(null);
    }, []);


    // -- Global mouseup safety net --
    useEffect(() => {
        if (!dragging) 
            return;
        
        const handleUp = () => abortDrag();
        window.addEventListener("mouseup", handleUp);
        
        return () => window.removeEventListener("mouseup", handleUp);
    }, [dragging, abortDrag]);

    return (
        <DragContext.Provider value={{ dragging, overId, startDrag, updateOver, commitDrop, abortDrag }}>
            <div
                ref={ref}
                style={style}
                data-kanban-board
                data-draggable={isDraggable}
                className={`${className} ${styles.board}`}
            >
                {children}
            </div>
        </DragContext.Provider>
    );
};


// ---------------------
// KanbanDragGhost — floating preview that follows the cursor
// Place this once inside <KanbanBoard>, pass the card UI as children
//
// Usage:
//   <KanbanBoard ...>
//     <KanbanDragGhost>
//       <MyCardPreview />   ← rendered while dragging
//     </KanbanDragGhost>
//     ...columns
//   </KanbanBoard>
// --------------------


export const KanbanDragGhost: React.FC<{ children: React.ReactNode }> = ({ children }) => {
    const { dragging } = useDrag();
    const [pos, setPos] = useState<{ x: number; y: number } | null>(null);

    useEffect(() => {
        if (!dragging) 
            { setPos(null); return; }
        const handleMove = (e: MouseEvent) => setPos({ x: e.clientX, y: e.clientY });
        
        window.addEventListener("mousemove", handleMove);
        return () => window.removeEventListener("mousemove", handleMove);
    }, [dragging]);

    if (!dragging || !pos) 
        return null;

    return (
        <div 
            style={{
                position:      "fixed",
                top:           pos.y,
                left:          pos.x,
                pointerEvents: "none",
                zIndex:        9999,
                transform:     "translate(-50%, -50%) rotate(2deg) scale(1.02)",
                opacity:       0.85,
                boxShadow:     "0 8px 24px rgba(0,0,0,0.18)",
            }}
        >
            {children}
        </div>
    );
};



// -----
// KanbanColumn — receives drops on empty space
// -----

export interface KanbanColumnProps {
    id:               string;       // required — must be stable
    title?:           string;
    count?:           number | string;
    color?:           string;
    backgroundColor?: string;
    className?:       string;
    bannerClassName?:  string;
    children:         React.ReactNode;
}


export const KanbanColumn: React.FC<KanbanColumnProps> = ({
    id,
    title,
    count,
    color,
    backgroundColor,
    className,
    bannerClassName,
    children,
}) => {
    const { dragging, commitDrop } = useDrag();

    // Fires when the user drops on empty column space (not on a card)
    const handleMouseUp = useCallback((e: React.MouseEvent) => {
        e.stopPropagation();
        if (!dragging)
            return;
        commitDrop(id, null);   // append at end
    }, [dragging, id, commitDrop]);

    return (
        <div
            data-column-id={id}
            className={`${className} ${styles.columns}`}
            style={{
                ["--bgColor" as string]: backgroundColor,
                ["--color"   as string]: color,
            }}
        >
            <div className={`${styles.txtSection} ${bannerClassName}`}>
                <span className={styles.title}>{title}</span>
                {count !== undefined && <span className={styles.count}>{count}</span>}
            </div>
            <div 
                className={styles.content}
                onMouseUp={handleMouseUp}
            >
                {children}
            </div>
        </div>
    );
};



// ----
// KanbanCard — fires events only, never mutates DOM
// ----

export interface KanbanCardProps {
    id:         string;     // must match CardData.id
    columnId:   string;     // must match CardData.columnId
    cardClassName?: string;
    dashedstrokeCard?: string;
    children:   React.ReactElement;
}

export const KanbanCard: React.FC<KanbanCardProps> = ({
    id,
    columnId,
    children,
    dashedstrokeCard,
    cardClassName,
}) => {    
    const { dragging, startDrag, updateOver, commitDrop, abortDrag } = useDrag();
    const isDraggingThis = dragging?.cardId === id;


    // ── Start drag 
    const handleMouseDown = useCallback((e: React.MouseEvent) => {
        if (e.button !== 0) return;

        e.preventDefault();
        e.stopPropagation();
        
        startDrag({ cardId: id, columnId });
    }, [id, columnId, startDrag]);

    
    // ── Drop & insert dragged card
    const handleMouseUp = useCallback((e: React.MouseEvent) => {
        e.stopPropagation();
        if (!dragging || dragging.cardId === id)
            return;
        commitDrop(columnId, id);
    }, [dragging, id, columnId, commitDrop]);


    // ── Hover — visual feedback
    const handleMouseEnter = useCallback(() => {
        if (dragging && dragging.cardId !== id)
            updateOver(id);
    }, [dragging, id, updateOver]);

    const handleMouseLeave = useCallback(() => updateOver(null), [updateOver]);
    
    // ── Abort listeners — only active while THIS card is dragged ──
    useEffect(() => {
        if (!isDraggingThis) 
            return;
        
        const onKey        = (e: KeyboardEvent) => { if (e.key === "Escape") abortDrag(); };
        const onBlur       = () => abortDrag();
        const onVisibility = () => { if (document.visibilityState === "hidden") abortDrag(); };

        window.addEventListener("keydown",             onKey);
        window.addEventListener("blur",                onBlur);
        document.addEventListener("visibilitychange",  onVisibility);

        return () => {
            window.removeEventListener("keydown",            onKey);
            window.removeEventListener("blur",               onBlur);
            document.removeEventListener("visibilitychange", onVisibility);
        };
    }, [isDraggingThis, abortDrag]);

    // ── While dragging: render a placeholder in the original slot

    if (isDraggingThis) {
        return (
            <div
                className={`${dashedstrokeCard} ${styles.dashedstrokeCard}`}
                data-card-id={id}
                data-placeholder
                style={{
                    opacity:       0.3,
                    border:        "2px dashed currentColor",
                    borderRadius:  "6px",
                    pointerEvents: "none",
                    minHeight:     "60px",
                }}
            />
        );
    }

    return (
        <div
            data-card-id={id}
            onMouseDown={handleMouseDown}
            onMouseUp={handleMouseUp}
            onMouseEnter={handleMouseEnter}
            onMouseLeave={handleMouseLeave}
            className={`${cardClassName} ${styles.card} ${dragging  ? styles.grabbedCard : "" }`}
        >
            {React.Children.only(children)}
        </div>
    );
};