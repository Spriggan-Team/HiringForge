import React from "react";
import styles from "./SliderInput.module.css";

interface SliderInputProps {
    label: string;
    min: number;
    max: number;
    value?: number;

    onChange?: (value: number) => void;
    
    devise?: string;
    minLabel?: string;
    maxLabel?: string;
    
    step?: number;
}

const SliderInput: React.FC<SliderInputProps> = ({
    label,
    min,
    max,
    
    value,
    onChange,
    
    devise,
    minLabel,
    maxLabel,
    
    step = 1,
}) => {
  return (
    <div className={styles.container}>
      <span className={styles.title}>{label}</span>

      <div className={styles.slider}>
        <span className={styles.min}>
            {value === min ? minLabel ?? min + (devise ? devise : "") : value + (devise ? devise : "") }
        </span>

        <input
          type="range"
          className={styles.input}
          min={min}
          max={max}
          step={step}
          value={value}
          onChange={(e) =>{
            if(onChange)
                onChange(Number(e.target.value))
          }}
        />

        <span className={styles.max}>{maxLabel ?? max}</span>
      </div>
    </div>
  );
};

export default SliderInput;