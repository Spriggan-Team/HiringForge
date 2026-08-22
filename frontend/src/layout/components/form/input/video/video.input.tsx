import React, { useEffect, useRef, useState } from "react";
import styles from "./styles.module.css";

interface VideoInputProps {
  title?: string;
  inputName?:string;
  subtitle?: string;
  maxDuration?: number;
  maxSize?: number;
  defaultFile?: File | null;
  onChange?: (file: File) => void;
}


const VideoInput: React.FC<VideoInputProps> = ({
  onChange,
  maxSize,
  inputName,
  maxDuration, //-- the length of the element in second
  defaultFile = null,
  title = "Sélectionner une vidéo",
  subtitle = "Cliquez ici pour choisir un fichier",
}) => {
  const inputRef = useRef<HTMLInputElement>(null);

  const [currentFile, setCurrentFile] = useState<File | null>(null);
  const [previewUrl, setPreviewUrl] = useState("");
  const [error, setError] = useState("");

  //-- Handle sudden change of default value
  useEffect(() => {
    if (!defaultFile) return;

    const objectUrl = URL.createObjectURL(defaultFile);

    setCurrentFile(defaultFile);
    setPreviewUrl(objectUrl);

    return () => URL.revokeObjectURL(objectUrl);
  }, [defaultFile]);



  //-- Handle file change (upload)
  const handleFileChange = (
    event: React.ChangeEvent<HTMLInputElement>
  ) => {
    const file = event.target.files?.[0];

    if (!file) return;

    setError("");

    if (maxSize && file.size > maxSize) {
      setError(
        `La vidéo dépasse la taille maximale autorisée (${(
          maxSize /
          1024 /
          1024
        ).toFixed(2)} MB).`
      );

      inputRef.current && (inputRef.current.value = "");
      return;
    }

    const updateVideo = () => {
      const objectUrl = URL.createObjectURL(file);
      setCurrentFile(file);
      setPreviewUrl(objectUrl);
      onChange?.(file);
    };

    if (!maxDuration) {
      updateVideo();
      return;
    }

    const video = document.createElement("video");
    video.preload = "metadata";

    video.onloadedmetadata = () => {
      URL.revokeObjectURL(video.src);

      if (video.duration > maxDuration) {
        setError(
          `La vidéo dépasse la durée maximale autorisée (${maxDuration}s).`
        );

        inputRef.current && (inputRef.current.value = "");
        return;
      }
      updateVideo();
    };

    video.src = URL.createObjectURL(file);
  };

  return (
    <div className={styles.wrapper}>
      <input
        ref={inputRef}
        type="file"
        name={inputName}
        accept="video/*"
        className={styles.hiddenInput}
        onChange={handleFileChange}
      />

      <div
        className={styles.uploadBox}
        onClick={() => inputRef.current?.click()}
      >
        {previewUrl ? (
          <>
            <video
              src={previewUrl}
              className={styles.preview}
              controls
              onClick={(e)=>e.stopPropagation()}
            />

            <div className={styles.fileName}>
              {currentFile?.name}
            </div>
          </>
        ) : (
          <>
            <div className={styles.icon}>🎥</div>

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