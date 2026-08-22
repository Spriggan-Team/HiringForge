import type { ParseKeys, TFunction } from "i18next";

//-- Custom components
import StatusItem from "../../../layout/components/indicators/statusItem/status.item";
import StageTitle from "../../../layout/components/indicators/stage/stage.title";

//-- Images - Ressources
import OfficeWorkerImage from "/src/assets/images/office-worker.png"


//-- Styles
import styles from "./RegisterationSteps.module.css"




/** Step Section */
export interface StepItem {
  id: number;       // Ex: 1, 2, 3, 4
  translationKey: ParseKeys; // Ex: "userRegister.processDescription.one"
}

interface StepsSectionProps {
  t: TFunction;
  steps: StepItem[];
  currentStep: { current: number; max: number };
  setCurrentStep: React.Dispatch<React.SetStateAction<{ current: number; max: number }>>;
}



const RegisterationSteps: React.FC<StepsSectionProps> = ({
    currentStep, setCurrentStep, t, steps
}) => {
    return (
    <div className={styles.infoBox}>
        <StageSection 
            t={t}
            steps={steps}
            currentStep={currentStep}
            setCurrentStep={setCurrentStep}
        />
        <div className={styles.statusItemSection}>
            <StatusItem text={t("register.processDescription.overall.1")}/>
            <StatusItem text={t("register.processDescription.overall.2")}/>
            <StatusItem text={t("register.processDescription.overall.3")}/>
        </div>
        <img src={OfficeWorkerImage} alt="" />
    </div>
    );
}
 
export default RegisterationSteps;




const StageSection: React.FC<StepsSectionProps> = ({
  steps,
  currentStep,
  setCurrentStep,
  t,
}) => {

  const handleStepClick = (stepId: number) => {
    // Navigate to follwing steps
    if (currentStep.max >= stepId) {
      setCurrentStep((prev) => ({
        ...prev,
        current: stepId,
      }));
    }
  };


  return (
    <div className={styles.stagesSection}>
      {
        steps.map((step) => {
            const isActive = currentStep.current === step.id;
            const isUnlocked = currentStep.max >= step.id;
            const isPassedButNotActive = isUnlocked && !isActive;

            return (
            <StageTitle
                key={step.id}
                step={step.id}
                active={isActive}
                txt={t(step.translationKey)}
                disableCursorPointer={!isUnlocked}
                textColor={isPassedButNotActive ? "#94a3b8" : undefined}
                backgroundColor={isPassedButNotActive ? "#e0e7ff" : undefined}
                onClick={() => handleStepClick(step.id)}
            />
            );
        })
      }
    </div>
  );
};