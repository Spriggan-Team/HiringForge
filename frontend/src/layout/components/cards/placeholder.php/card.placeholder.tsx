import React from "react";
import styles from "./CardPlaceholder.module.css";

interface CardPlaceholderProps {
  text: string;
  height?: string;
  width?: string;
  className?: string;
}

export const CardPlaceholder: React.FC<CardPlaceholderProps> = ({
  text,
  height,
  width,
  className = "",
}) => {
  // Size parameters
  const customStyles: React.CSSProperties = {
    ...(height && { height }),
    ...(width && { width }),
  };

  const isCustomSized = Boolean(height || width);

  return (
    <div
      className={`${styles.placeholder} ${!isCustomSized ? styles.fullSize : ""} ${className}`}
      style={customStyles}
    >
      <span>{text}</span>
    </div>
  );
};