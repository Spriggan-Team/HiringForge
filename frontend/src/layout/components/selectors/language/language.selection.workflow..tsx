
import { useEffect, useMemo, useState } from "react";

//-- Services & types
import { LANGUAGES_LEVEL_VALUES, type JobLanguage } from "../../../../features/jobs/JobOffer";

//-- Custom components
import MenuDrawer, { MenuDrawerBody, MenuDrawerItem, MenuDrawerTrigger } from "../../menu/drawer/menu.drawer";
import { globalBasicInputInput } from "../../form/input/basic.input";


//-- CSS Style
import styles from "./LanguageSelectionWorkflow.module.css"
import LanguageQueries from "../../../../api/services/Language/queries";


interface LanguageSelectionWorkflowProps {
    languages: { id: number, code: string }[];
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

    const displayNames = useMemo(
        () =>
            new Intl.DisplayNames(["en"], {
                type: "language",
            }),
        [],
    );

    const [pendingCode, setPendingCode] = useState<{id: number, code: string} | null>(null);
    const [LanguageLevel, setLanguageLevel] = useState<string[]>([]);

    const triggerPlaceholder =
        typeof placeholder === "string"
            ? placeholder
            : pendingCode
                ? (placeholder.selectLevel ?? placeholder.selectLanguage)
                : placeholder.selectLanguage;

    useEffect(()=>{
        
        const intializeData = async () =>{
            try{
                const data  = await LanguageQueries.getLanguagesLevel();
                setLanguageLevel(data);
            }
            catch(error){
                console.error("Something went wrong while loading language level collection");
            }
        }
        intializeData();
    },[])

    return (
        <div className={styles.container}>
            {/* Language selection*/}
            <MenuDrawer
                className={`${styles.drawer} drawer`}
                onChange={({ code }) => {
                    setPendingCode(code);
                }}
            >
                <MenuDrawerTrigger
                    {...globalBasicInputInput}
                    className={`${globalBasicInputInput.className} input-like-placeholder`}
                >
                    <span>
                        {triggerPlaceholder}
                    </span>
                </MenuDrawerTrigger>

                <MenuDrawerBody
                    className={`${styles.selectDropdown} selectDropdown`}
                >
                    {languages.map(lang => (

                        <MenuDrawerItem
                            key={lang.code}
                            value={lang}
                            className={`${styles.selectItem} selectItem`}
                        >
                            {displayNames.of(lang.code)}
                        </MenuDrawerItem>

                    ))}
                </MenuDrawerBody>
            </MenuDrawer>


            {/*   Level selection*/}
            <MenuDrawer
                triggerVisibility={!!pendingCode}
                className={`${styles.drawer} drawer`}
                onChange={({ proficiencyLevel,  }) => {

                    if (!pendingCode) return;

                    onAdd({
                        id:  pendingCode.id,
                        code: pendingCode.code,
                        proficiencyLevel,
                        nativeLabel: displayNames.of(pendingCode.code) ?? ""
                    });

                    setPendingCode(null);

                }}
            >

                <MenuDrawerBody
                    className={`${styles.selectDropdown} selectDropdown`}
                >
                    {LANGUAGES_LEVEL_VALUES.map(level => (
                        <MenuDrawerItem
                            key={level}
                            value={{
                                proficiencyLevel: level,
                            }}
                            className={`${styles.selectItem} selectItem`}
                        >
                            {level}
                        </MenuDrawerItem>
                    ))}
                </MenuDrawerBody>
            </MenuDrawer>
        </div>
    );
};


export default LanguageSelectionWorkflow;