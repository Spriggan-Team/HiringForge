
import { useEffect, useRef, useState } from "react";

import ImageSVG from "/src/assets/svg/person/image-combiner-svgrepo-com.svg?react"

//-- CSS styles
import styles from "./style.module.css"



interface ImageInputProps {
  title?: string;
  required?: boolean;
  inputName?: string;
  subtitle?: string;
  maxSize?: number;

  defaultFile?: File | null;
  onChange?: (file: File) => void;
  className?: string 
}


const ImageInput: React.FC<ImageInputProps> = ({
  onChange,
  maxSize,
  inputName,
  defaultFile = null,

  className,
  title = "Sélectionner une image",
  subtitle = "Cliquez ici pour choisir un fichier",
}) => {

  const inputRef = useRef<HTMLInputElement>(null);

  const [currentFile, setCurrentFile] = useState<File | null>(defaultFile);
  const [preview, setPreview] = useState<string>(
    defaultFile ? URL.createObjectURL(defaultFile) : ""
  );
  const [error, setError] = useState("");

  //-- Prevent uncontrolled sudden change of defaultValue
  useEffect(() => {
    if (!defaultFile) return;

    const objectUrl = URL.createObjectURL(defaultFile);

    setCurrentFile(defaultFile);
    setPreview(objectUrl);

    return () => {
        URL.revokeObjectURL(objectUrl);
    };
   }, [defaultFile]);



  const handleFileChange = (
    event: React.ChangeEvent<HTMLInputElement>
  ) => {
    const file = event.target.files?.[0];

    if (!file) return;

    setError("");

    if (!file.type.startsWith("image/")) {
      setError("Le fichier sélectionné n'est pas une image.");

      if (inputRef.current) {
        inputRef.current.value = "";
      }

      return;
    }

    if (maxSize && file.size > maxSize) {
      setError(
        `L'image dépasse la taille maximale autorisée (${(
          maxSize /
          1024 /
          1024
        ).toFixed(2)} MB).`
      );

      if (inputRef.current) {
        inputRef.current.value = "";
      }

      return;
    }

    setCurrentFile(file);
    setPreview(URL.createObjectURL(file));

    onChange?.(file);
  };

  return (
    <div className={`${styles.wrapper} ${className}`}>
      <input
        type="file"
        ref={inputRef}
        name={inputName}
        accept="image/*"
        className={styles.hiddenInput}
        onChange={handleFileChange}
      />

      <div
        className={styles.uploadBox}
        onClick={() => inputRef.current?.click()}
      >
        {preview ? (
          <>
            <img
              src={preview}
              alt="preview"
              className={styles.preview}
            />

            <div className={styles.fileName}>
              {currentFile?.name}
            </div>
          </>
        ) : (
          <>
            <ImageSVG className={styles.icon} />
            <div className={styles.title}>
              {title}
            </div>

            <div className={styles.subtitle}>
              {subtitle}
            </div>
          </>
        )}
      </div>

      {error && (
        <div className={styles.error}>
          {error}
        </div>
      )}
    </div>
  );
};

export default ImageInput;