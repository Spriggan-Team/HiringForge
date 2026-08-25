import React, { useState, useEffect } from "react";
import styles from "./styles.module.css";
import { useAppContext } from "../../../hooks/context";

interface AppPopupProps {
    timeout?: number; // Optionnel, exprimé en secondes
}

const AppPopup: React.FC<AppPopupProps> = ({  timeout = 3 }) => {
    const { popup, setPopup } = useAppContext();

    const [shouldRender, setShouldRender] = useState(false);
    const [animateIn, setAnimateIn] = useState(false);


    //-- Animation
    useEffect(() => {
        if (popup?.message) {
            setShouldRender(true);
            const timer = setTimeout(() => setAnimateIn(true), 50);
            return () => clearTimeout(timer);
        }
        else {
            setAnimateIn(false);
            const timer = setTimeout(() => setShouldRender(false), 400);
            return () => clearTimeout(timer);
        }
    }, [popup?.message]);

    
    //-- handle closing
    const closePopup = () => {
        setAnimateIn(false); 
        //-- animation timeout
        const timer = setTimeout(() => {
            setShouldRender(false);
            //-- clear
            if (setPopup) {
                setPopup(null); 
            }
        }, 400);

        return () => clearTimeout(timer);
    };


    //-- automatic closure
    useEffect(() => {
        if (shouldRender && animateIn) {
            //-- convert seconds to miliseconds
            const autoDismissTimer = setTimeout(() => {
                closePopup();
            }, timeout * 1000);

            return () => clearTimeout(autoDismissTimer);
        }
    }, [shouldRender, animateIn, timeout]);
    

    return (
        <>
            <div className={styles.container}>
                {shouldRender && (
                    <div className={`
                        ${styles.pop} 
                        ${styles[popup?.status ?? ""] || styles.success} 
                        ${animateIn ? styles.show : ""}
                    `}>
                        <div className={styles.content}>
                            {popup?.message}
                        </div>
                        <button type="button" className={styles.closeBtn} onClick={closePopup}>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                )}
            </div>
        </>
    );
};

export default AppPopup;