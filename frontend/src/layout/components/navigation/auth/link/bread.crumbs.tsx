import { Link } from "react-router-dom";
import type { CSSProperties } from "react";

//-- CSS Mosule
import styles from "./BreadCrumbs.module.css";



export interface BreadCrumbsProps {
    links: LinkData[];
    onClick?: React.MouseEventHandler;

    color?: string;
    overlayColor?: string;
    className?:string;
}

export interface LinkData {
    text: string;
    route: string;
    current?: boolean;
}


const BreadCrumbs: React.FC<BreadCrumbsProps> = ({
    links,
    onClick,

    color,
    overlayColor,
    className,
}) => {
    const style = {
        color,
        "--overlayColor": overlayColor,
    } as CSSProperties;

    return (
        <nav
            style={style}
            aria-label="breadcrumb"
            className={`${styles.container}`}
        >
            <ol className={styles.list}>
                {links.map(({ text, route, current }, index) => {
                    const normalizedRoute =
                        route.startsWith("/") ? route : `/${route}`;

                    return (
                        <li
                            key={normalizedRoute}
                            className={styles.item}
                        >
                            {current ? (
                                <span
                                    aria-current="page"
                                    className={styles.current}
                                >
                                    {text}
                                </span>
                            ) : (
                                <>
                                    <Link
                                        onClick={onClick}
                                        to={normalizedRoute}
                                        className={styles.link}
                                    >
                                        {text}
                                    </Link>

                                    {
                                        index !== links.length - 1 &&(
                                            <span
                                                className={styles.separator}
                                                aria-hidden="true"
                                            >
                                                &gt;
                                            </span>
                                        )
                                        
                                    }
                                </>
                            )}
                        </li>
                    );
                })}
            </ol>
        </nav>
    );
};

export default BreadCrumbs;