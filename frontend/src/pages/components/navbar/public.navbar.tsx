import React from 'react'

//-- Styles 
import styles from "./PublicNavBar.module.css"

interface PublicNavBarProps{
    className?: string
}

const PublicNavBar: React.FC<PublicNavBarProps> = ({
    className
}) => {
    return (
        <div className={`${styles.container} ${className}`}>

        </div>
    );
}
 
export default PublicNavBar;