import { useState } from 'react';

//-- SVG - Components
import BrowserSVG from '/src/assets/svg/net/internet-svgrepo-com.svg';
import DownArrowSVG from '/src/assets/svg/arrows/down-arrow-5-svgrepo-com.svg';

import styles from "./style.css.module.css"

const LanguageSelector = () => {
    const [language, setLanguage] = useState("Français");
    
    return ( 
        <div className={`${styles.translateSection} faint-border`}>
            <BrowserSVG className={styles.browserSVG} width={25} height={25}  />
            <span>{language}</span>
            <DownArrowSVG className={styles.arrowSVG} width={25} height={25} />
        </div>
    );
}
 
export default LanguageSelector;