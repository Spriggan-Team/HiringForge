import React from "react";
import styles from "./LineChartPlaceholder.module.css";

export interface LineChartPlaceholderProps {
  text?: string;
  height?: string;
  width?: string;
  className?: string;
  theme?: "light" | "dark";
}

export const LineChartPlaceholder: React.FC<LineChartPlaceholderProps> = ({
  text = "Graphique indisponible",
  height,
  width,
  className = "",
}) => {
  const customStyles: React.CSSProperties = {
    ...(height && { height }),
    ...(width && { width }),
  };


  return (
    <div
      className={`${styles.container}  ${className}`}
      style={customStyles}
    >
      {/* Graph Curve */}
      <svg
        className={styles.icon}
        viewBox="0 0 24 24"
        fill="none"
        strokeWidth="1.5"
        strokeLinecap="round"
        strokeLinejoin="round"
      >
        <path d="M3 3v18h18" />
        <path d="m19 9-5 5-4-4-3 3" />
      </svg>

      <span className={styles.text}>{text}</span>
    </div>
  );
};