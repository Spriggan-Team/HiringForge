
// Pagination.tsx
import styles from "./Pagination.module.css";

export interface PaginationProps {
    currentPage:   number;
    totalPages:    number;
    onChange:      (page: number) => void;

    siblingCount?: number; //  Max number of page buttons shown around the current page 
    className?:    string;
}


export const Pagination: React.FC<PaginationProps> = ({
    currentPage,
    totalPages,
    onChange,
    siblingCount = 2,
    className,
}) => {
    if (totalPages <= 1) return null;

    // Build the page numbers to display
    const pages = buildPageRange(currentPage, totalPages, siblingCount);

    return (
        <nav
            aria-label="Pagination"
            className={`${styles.pagination} ${className ?? ""}`}
        >
            {/* Previous */}
            <button
                className={`${styles.btn} ${styles.arrow}`}
                disabled={currentPage === 0}
                onClick={() => onChange(currentPage - 1)}
                aria-label="Page précédente"
            >
                ‹
            </button>

            {/* Page numbers + ellipsis */}
            {pages.map((p, i) =>
                p === "…" ? (
                    <span key={`ellipsis-${i}`} className={styles.ellipsis}>…</span>
                ) : (
                    <button
                        key={p}
                        className={`${styles.btn} ${p === currentPage ? styles.active : ""}`}
                        onClick={() => onChange(p as number)}
                        aria-current={p === currentPage ? "page" : undefined}
                    >
                        {(p as number) + 1}
                    </button>
                ),
            )}

            {/* Next */}
            <button
                className={`${styles.btn} ${styles.arrow}`}
                disabled={currentPage === totalPages - 1}
                onClick={() => onChange(currentPage + 1)}
                aria-label="Page suivante"
            >
                ›
            </button>
        </nav>
    );
};


//  Helper

function buildPageRange(
    current:      number,
    total:        number,
    siblingCount: number,
): (number | "…")[] {
    const left  = Math.max(0, current - siblingCount);
    const right = Math.min(total - 1, current + siblingCount);

    const pages: (number | "…")[] = [];

    if (left > 0) {
        pages.push(0);
        if (left > 1) pages.push("…");
    }

    for (let i = left; i <= right; i++) pages.push(i);

    if (right < total - 1) {
        if (right < total - 2) pages.push("…");
        pages.push(total - 1);
    }

    return pages;
}