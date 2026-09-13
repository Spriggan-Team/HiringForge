
import React, { useRef } from 'react';

import NavigateSVG from '/src/assets/svg/net/open-in-new-window-svgrepo-com.svg?react'

//-- CSS STyles
import styles from './ActionCard.module.css'



interface ActionCardProps {
    text: string;
    url?: string;
    className?: string;
    isEditable: boolean;
    width?: string | null;
    onClick?: (event: React.MouseEvent<HTMLButtonElement>)=> void;
    onDelete?: () => void
}

const ActionCard: React.FC<ActionCardProps> = ({
    text,
    url,

    width,
    className,

    onClick,
    isEditable,
    onDelete,

}) => {
    const cardRef = useRef<HTMLDivElement|null>(null);

    const handleOpen = (
        event: React.MouseEvent<HTMLButtonElement>
    ) => {
        onClick?.(event);

        if (url) {
            window.open(
                url,
                "_blank",
                "noopener,noreferrer"
            );
        }
    };

    const handleDelete = () => {
        onDelete?.();
    };


    const computeWidth = width === null
                        ? "100%"
                        : width === undefined ?
                            "345px"
                            : width;

    return (
        <div 
            ref={cardRef}
            style={{ width: computeWidth }}
            className={`${styles.card} ${className ?? ""}`}
        >
            <span className={styles.text}>{text}</span>
            <div className={styles.actions}>
                {url && (
                    <button
                        onClick={(event) => {
                            handleOpen(event);
                        }}
                        className={styles.actionButton}
                        type="button"
                        aria-label="Ouvrir dans un nouvel onglet"
                    >
                        <NavigateSVG height={16} width={16} />
                    </button>
                )}
                {isEditable && (
                    <button
                        type="button"
                        className={styles.removeButton}
                        onClick={handleDelete}
                        aria-label={`Supprimer ${text}`}
                    >
                        ×
                    </button>
                )}
            </div>
        </div>
    );
};

export default ActionCard;