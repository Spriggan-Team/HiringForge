import type { ParseKeys } from "i18next";


//-- Custom components
import Gauge from "../../../layout/components/progress/gauge/gauge";

//-- Styles
import styles from "./RegisterationHeader.module.css"


export interface RegisterationHeaderProps {
  t: (key: any, options?: any) => string;
  titleKey: ParseKeys;
  subtitleKey: ParseKeys;
  stepKey?: ParseKeys; // Defaut value
  currentStep: number;
  totalSteps: number;
  activeGaugeColor?: string;
  gaugeHeight?: number;
  className?: string;
}

export const RegisterationHeader: React.FC<RegisterationHeaderProps> = ({
  t,
  titleKey,
  subtitleKey,
  stepKey = "userRegister.form.currentStep" as ParseKeys,
  currentStep,
  totalSteps,
  activeGaugeColor = "#003DE7",
  gaugeHeight = 2.5,
  className = "",
}) => {
  // Calcul Gauge
  const percent = totalSteps > 0 ? Math.min(Math.max(currentStep / totalSteps, 0), 1) : 0;

  return (
    <div className={`${styles.header} ${className}`}>
      <h3 className={styles.formTitle}>{t(titleKey)}</h3>
      
      <div className={styles.desc}>
        <p>{t(subtitleKey)}</p>
        
        <div className={styles.progessContainer}>
          <span>
            {t(stepKey, { count: currentStep, totalCount: totalSteps })}
          </span>
          <Gauge
            width="50%"
            height={gaugeHeight}
            activeColor={activeGaugeColor}
            foregroundColor="#D9D9D9"
            percent={percent}
          />
        </div>
      </div>
    </div>
  );
};

export default RegisterationHeader;