

import React, { useEffect, useRef,  } from 'react'

import ContentSlider from "../../navigation/slider/content.slider";

//-- SVG Compoenents
import AddSVG from "/src/assets/svg/add/add-svgrepo-com.svg?react";

//-- Styles
import styles from "./Galery.module.css"


interface GaleryProps{
    className?: string;
    images: { id?: number, url: string }[];
    setImages: React.Dispatch<React.SetStateAction<{ id?: number, url: string }[]>>;
    
    isEditable?: boolean;
    onDelete?: (id: number)=> void;
    onAdd?: (file: File, url: string) => void;
}

const Galery: React.FC<GaleryProps> = ({
    className,
    images,
    setImages,

    isEditable,
    onAdd,
    onDelete
}) => {
    const inputRef = useRef<HTMLInputElement | null>(null);
    const objectUrlsRef = useRef<Set<string>>(new Set());

    /**
     * Clear gnerated local url resource
     */
    useEffect(() => {
        return () => {
            objectUrlsRef.current.forEach((url) => {
                URL.revokeObjectURL(url);
            });

            objectUrlsRef.current.clear();
        };
    }, []);

    const handleAddImage = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];

        if (!file) {
            event.target.value = "";
            return;
        }

        //-- verfy if it is an image
        if (!file.type.startsWith("image/")) {
            event.target.value = "";
            return;
        }

        const url = URL.createObjectURL(file);
        objectUrlsRef.current.add(url);

        setImages((prev) => [
            ...(prev ?? []),
            {
                url,
            }
        ]);

        //-- Renitialize input state
        event.target.value = "";
        onAdd?.(file, url);
    };

    const handleRemoveImage = (index: number) => {
        setImages((prev) => {
            if (!prev) {
                return [];
            }

            const image = prev[index];

            if (!image) {
                return prev;
            }

            /**
             * Revoke url only created by this component
             */
            if (objectUrlsRef.current.has(image.url)) {
                URL.revokeObjectURL(image.url);
                objectUrlsRef.current.delete(image.url);
            }


            return prev.filter((_, imageIndex) => imageIndex !== index);
        });
    };


    const handleAddClick = () => {
        inputRef.current?.click();
    };

    return (
        <div 
            className={`${styles.container} ${className ?? ""}`}
        >
            <div className={styles.header}>
                <div className={styles.titleWrapper}>
                    <span className={styles.title}>
                        Galerie d'images
                    </span>

                    <span className={styles.counter}>
                        {images?.length ?? 0}
                    </span>
                </div>

                {isEditable && (
                    <button
                        type="button"
                        className={styles.addBox}
                        onClick={handleAddClick}
                        aria-label="Ajouter une image"
                    >
                        <AddSVG
                            width={16}
                            height={16}
                        />

                        <span>Ajouter</span>
                    </button>
                )}

                <input
                    ref={inputRef}
                    type="file"
                    accept="image/*"
                    onChange={handleAddImage}
                    className={styles.hiddenInput}
                />
            </div>

            <ContentSlider
                className={styles.images}
            >
                   {images.map((image, index) => {
                        if (!image?.url) {
                            return null;
                        }

                        return (
                            <div
                                className={styles.image}
                                key={`${image.url}-${index}`}
                            >
                                <img
                                    src={image.url}
                                    alt={`Image ${index + 1}`}
                                    className={styles.img}
                                />

                                {isEditable && (
                                    <button
                                        type="button"
                                        className={`${styles.removeBtn}`}
                                        onClick={() =>{
                                            handleRemoveImage(index);
                                            if(image.id){
                                                onDelete?.(image.id);
                                            }
                                        }}
                                        aria-label={`Supprimer l'image ${index + 1}`}
                                    >
                                        <span>×</span>
                                    </button>
                                )}
                            </div>
                        );
                    })}
            </ContentSlider>
        </div>
    );
}
 
export default Galery;