
import React from 'react'


//-- SVG
import LinkIcon from "/src/assets/svg/net/link-alt-svgrepo-com.svg?react"


//-- CSS
import  styles from './ShortcurtLink.module.css'



interface ShortcurtLinkProps{
    link: string;
    icon?: React.FC<React.SVGProps<SVGSVGElement>>;
    disabled?: boolean;
    className?: string;
}


const ShortcurtLink: React.FC<ShortcurtLinkProps> = ({
    icon: Icon,
    link,
    disabled = false,
    className
}) => {
    return (
        <a
            href={disabled ? undefined : link}
            target={disabled ? undefined : "_blank"}
            rel={disabled ? undefined : "noopener noreferrer"}
            aria-disabled={disabled}
            tabIndex={disabled ? -1 : undefined}
            onClick={(event) => {
                if (disabled) {
                    event.preventDefault();
                }
            }}
            className={`${styles.container} ${disabled ? styles.disabled : ""} ${className ? className : ""}`}
        >
            <div className={styles.icon}>
                {Icon ? (
                    <Icon width={15} height={15} />
                ) : (
                    <LinkIcon width={15} height={15} />
                )}
            </div>

            <span className={styles.link}>
                {link}
            </span>
        </a>
    );
}
 
export default ShortcurtLink;