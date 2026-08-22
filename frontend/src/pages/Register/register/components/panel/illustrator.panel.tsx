import React from "react";
import styles from "./style.module.css";

interface IllustratorPannelProps {
  image: string;
  width?: number;
  height?: number;
}

const IllustratorPannel: React.FC<IllustratorPannelProps> = ({
  image,
  width = 500, 
  height = 400,
}) => {
  return (
    <div
      className={styles.dashboardContainer}
      style={{
        ["--panel-width" as any]: `${width}px`,
        ["--panel-height" as any]: `${height}px`,
      }}
    >
      {/* back- décorations */}
      <div className={styles.bgDecorator}>
        <div className={styles.blobLeft}></div>
        <div className={styles.blobRight}></div>
        <div className={styles.dotGrid}></div>
      </div>

      {/* image principale */}
      <img 
        alt="Illustration" 
        src={image} 
        className={styles.image} 
      />
    </div>
  );
};

export default IllustratorPannel;