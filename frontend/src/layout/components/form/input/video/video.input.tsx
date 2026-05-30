import React, { useRef, useState } from "react";
import styles from "./styles.module.css";

interface VideoInputProps {
  title?: string;
  inputName?:string;
  subtitle?: string;
  maxDuration?: number;
  maxSize?: number
  onChange?: (file: File) => void;
}

const VideoInput: React.FC<VideoInputProps> = ({
  onChange,
  maxSize,
  inputName,
  maxDuration,
  title = "Sélectionner une vidéo",
  subtitle = "Cliquez ici pour choisir un fichier",
}) => {
  const inputRef = useRef<HTMLInputElement>(null);
  const [currentFile, setCurrentFile] = useState<File | null>(null);
  const [error, setError] = useState("");

  const handleFileChange = (
    event: React.ChangeEvent<HTMLInputElement>
  ) => {
    const file = event.target.files?.[0];

    if (!file) return;

    setError("");

    //-- size checking
    if (maxSize && file.size > maxSize) {
      setError(
        `La vidéo dépasse la taille maximale autorisée (${(
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

    //-- no control
    if (!maxDuration) {
      setCurrentFile(file);
      onChange?.(file);
      return;
    }

    const video = document.createElement("video");

    video.preload = "metadata";

    video.onloadedmetadata = () => {
      URL.revokeObjectURL(video.src);

      if (maxDuration && video.duration > maxDuration) {
        setError(
          `La vidéo dépasse la durée maximale autorisée (${maxDuration}s).`
        );

        if (inputRef.current) {
          inputRef.current.value = "";
        }

        return;
      }

      setCurrentFile(file);
      onChange?.(file);
    };

    video.src = URL.createObjectURL(file);
  };

  return (
    <div className={styles.wrapper}>
      <input
        type="file"
        ref={inputRef}
        name={inputName}
        accept="video/*"
        className={styles.hiddenInput}
        onChange={handleFileChange}
      />

      <div
        className={styles.uploadBox}
        onClick={() => inputRef.current?.click()}
      >
        <div className={styles.icon}>🎥</div>

        {currentFile ? (
          <div className={styles.fileName}>
            {currentFile.name}
          </div>
        ) : (
          <>
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

export default VideoInput;