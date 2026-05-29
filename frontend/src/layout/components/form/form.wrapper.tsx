
import styles from "./style.module.css"


interface FormWrapperProps{
    children: React.ReactNode,
    formData: FormData;
    handleNext?: React.SubmitEventHandler<HTMLFormElement>
}


const FormWrapper: React.FC<FormWrapperProps> = ({children, handleNext, formData}) => {
    //-- Handle submit event 
    const submitHandler = (event: React.SubmitEvent<HTMLFormElement>)=>{
        event.preventDefault();
        const formElements = event.currentTarget.elements;

        //-- collect data
        for (let i = 0; i < formElements.length; i++) {
            const element = formElements[i] as HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement;
            
            if (element.name && element.type !== "submit" && element.type !== "button") {
                if ((element.type === "checkbox" || element.type === "radio") && !(element as HTMLInputElement).checked) {
                    continue; 
                }
                
                formData.append(element.name, element.value);
            }
        }
        if(handleNext)
            handleNext(event);
    }

    return (
        <div className={styles.container}>
            <form className={styles.form} action="" onSubmit={submitHandler}>
                {children}
            </form>
        </div>
    );
}
 

export default FormWrapper;


//-- title of a form
export const FormTitle = ({title}: {title: string}) => {
    return (<span className={styles.formTitle}>{title}</span>);
}
 

//-- inputs sections
export const FormInputs = ({children}: {children: React.ReactNode})=>{
    return (
        <div className={styles.inputSection}>
            {children}
        </div>
    )
}

//-- form footer section
export const SubmitSection = ({children}: {children: React.ReactNode}) => {
    return (
        <div className={styles.nextSection}>
            {children}
        </div>
    );
}


//-- form hint
export const FormHint = ({text}: {text: string})=>{
    return(
        <div className={styles.undertext}>
            <p>{text}</p>
        </div>
    )
}
 
