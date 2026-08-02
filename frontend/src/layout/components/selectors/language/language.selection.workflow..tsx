
import { useEffect, useMemo, useState } from "react";

//-- Services & types
import { LANGUAGES_LEVEL_VALUES, type JobLanguage, type LanguageLevel } from "../../../../features/jobs/JobOffer";
import LanguageQueries from "../../../../api/services/Language/queries";

//-- Custom components
import {  MultiStepDrawer } from "../../menu/drawer/menu.drawer";
import { globalBasicInputInput } from "../../form/input/basic.input";


//-- CSS Style
import styles from "./LanguageSelectionWorkflow.module.css"




interface LanguageSelectionWorkflowProps {
    languages: { id: number; code: string }[];
    onAdd: (language: JobLanguage) => void;
    placeholder?: {
        selectLanguage: string;
        selectLevel?: string;
    } | string;
    enableInputSearch?: boolean;
}



const LanguageSelectionWorkflow: React.FC<LanguageSelectionWorkflowProps> = ({
    languages,
    onAdd,
    placeholder = "Select language",
    enableInputSearch = false
}) => {
    const [languageLevels, setLanguageLevels] = useState<string[]>([]);

    const displayNames = useMemo(
        () => new Intl.DisplayNames(["en"], { type: "language" }),
        []
    );

    useEffect(() => {
        const initializeData = async () => {
            try {
                const data = await LanguageQueries.getLanguagesLevel();
                setLanguageLevels(data);
            } catch (error) {
                console.error("Something went wrong while loading language level collection", error);
            }
        };
        initializeData();
    }, []);


    //-- Langages step 1 
    const step1Items = useMemo(() => {
        return languages.map((lang) => ({
            label: displayNames.of(lang.code) ?? lang.code,
            value: lang
        }));
    }, [languages, displayNames]);


    //-- Steps 2 items
    const step2Items = useMemo(() => {
        const levels: LanguageLevel[] = languageLevels.length > 0 ? 
                languageLevels as LanguageLevel[]
                : (LANGUAGES_LEVEL_VALUES as LanguageLevel[]);
        return levels.map((level) => ({
            label: level,
            value: level 
        }));
    }, [languageLevels]);


    const handleComplete = (
        selectedLang: { id: number; code: string },
        selectedLevel: LanguageLevel
    ) => {
        onAdd({
            id: selectedLang.id,
            code: selectedLang.code,
            proficiencyLevel: selectedLevel,
            nativeLabel: displayNames.of(selectedLang.code) ?? ""
        });
    };


    return (
        <MultiStepDrawer<{ id: number; code: string }, LanguageLevel>
            className={styles.container}
            drawerClassName={`${styles.drawer} drawer`}
            dropdownClassName={`${styles.selectDropdown} selectDropdown`}
            itemClassName={`${styles.selectItem} selectItem`}
            step1Items={step1Items}
            step2Items={step2Items}
            onComplete={handleComplete}
            placeholder={placeholder}
            triggerProps={{
                ...globalBasicInputInput,
                className: `${globalBasicInputInput?.className ?? ""} input-like-placeholder`
            }}
        />
    );
};


export default LanguageSelectionWorkflow;