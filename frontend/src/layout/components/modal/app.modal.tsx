import React, { useEffect } from "react";
import { useAppContext } from "../../../hooks/context";
import { createPortal } from "react-dom";

import styles from "./AppModal.module.css";


export const AppModal: React.FC = () => {
    const { modal, setModal } = useAppContext();


    const closeModal = () => {
        if (modal?.onClose) {
            modal.onClose();
        }
        setModal(null);
    };

    //-- handle Escape keyboard
    useEffect(() => {
        console.log("APP Modal Effect exécuté, state actuel :", modal);

        const handleKeyDown = (e: KeyboardEvent) => {
            if (e.key === "Escape") {
                closeModal();
            }
        };

        if (modal?.isOpen) {
            console.log("APP Modal est OUVERT !");
            window.addEventListener("keydown", handleKeyDown);
        }

        return () => window.removeEventListener("keydown", handleKeyDown);
    }, [modal]);


    if (!modal || !modal.isOpen) {
        return null;
    }

    // -- Force rendu at the top (of DOM)
    return createPortal(
        <div className={styles.overlay} onClick={closeModal}>
            <div className={styles.container} onClick={(e) => e.stopPropagation()}>
                <header className={styles.header}>
                    {modal.title && <h3>{modal.title}</h3>}
                    <button 
                        type="button" 
                        className={styles.closeBtn} 
                        onClick={closeModal}
                        aria-label="Fermer"
                    >
                        &times;
                    </button>
                </header>

                <div className={styles.body}>
                    {
                        typeof modal.content === "function" ?
                            modal.content()
                            : modal.content
                    }
                </div>
            </div>
        </div>,
        document.body
    );
};

export default AppModal;