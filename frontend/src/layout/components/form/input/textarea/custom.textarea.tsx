
import styles from "./style.module.css";


interface CustomTextareaProps{
    inputName?: string;
    placeholder?: string;
    value?: undefined;
    setValue?: (v: string) => void;
}


const CustomTextarea: React.FC<CustomTextareaProps> = ({
    inputName = "textarea",
    placeholder, value, setValue
}) => {
  return (
    <div className={styles.container}>
      <textarea
        value={value}
        name={inputName}
        className={styles.input}
        placeholder={placeholder}
        onChange={(event)=> {
            if(setValue)
                 setValue(event.target.value)
        }}
      />
    </div>
  );
};

export default CustomTextarea;