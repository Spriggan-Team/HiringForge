
import { useRef } from "react";
import { useTranslation } from "react-i18next";


import UploadSVG from "/src/assets/svg/load/export-svgrepo-com.svg"

import styles from "./style.module.css"

interface DownloadButton{
    onNext: (file?: File) => void;
}

const DownloadButton: React.FC<DownloadButton> = ({
    onNext
}) => {
    const {t} = useTranslation()
    const inputRef = useRef<HTMLInputElement | null>(null);

    return (
        <div className={styles.container}>
            <input
                ref={inputRef}
                type="file" 
                multiple={false}
                style={{ display: "contents"}}
                onChange={(event)=>{
                    const file = event.target.files?.[0];
                    if(onNext)
                        onNext(file);
                }}
            />
            <div 
                onClick={()=>{
                    const current = inputRef.current;
                    if(!current)
                        return;
                    current.click();
                }}
            >
                <UploadSVG width={25} height={25} />
                <span>{t("register.form.downloadAssets.logo.tagline")}</span>
            </div>
        </div>
    );
}
 
export default DownloadButton;