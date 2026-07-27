
import { useMemo, useState } from "react";

//-- Services & types
import { LANGUAGES_LEVEL_VALUES, type JobLanguage } from "../../../../features/jobs/JobOffer";

//-- Custom components
import MenuDrawer, { MenuDrawerBody, MenuDrawerItem, MenuDrawerTrigger } from "../../menu/drawer/menu.drawer";
import { globalBasicInputInput } from "../../form/input/basic.input";


//-- CSS Style
import styles from "./LanguageSelectionWorkflow.module.css"


interface LanguageSelectionWorkflowProps {
    languages: string[];
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

    const [pendingCode, setPendingCode] = useState<string | null>(null);

    const triggerPlaceholder =
        typeof placeholder === "string"
            ? placeholder
            : pendingCode
                ? (placeholder.selectLevel ?? placeholder.selectLanguage)
                : placeholder.selectLanguage;

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
                    {languages.map(code => (

                        <MenuDrawerItem
                            key={code}
                            value={{ code }}
                            className={`${styles.selectItem} selectItem`}
                        >
                            {displayNames.of(code)}
                        </MenuDrawerItem>

                    ))}
                </MenuDrawerBody>
            </MenuDrawer>


            {/*   Level selection*/}
            <MenuDrawer
                triggerVisibility={!!pendingCode}
                className={`${styles.drawer} drawer`}
                onChange={({ proficiencyLevel }) => {

                    if (!pendingCode) return;

                    onAdd({
                        code: pendingCode,
                        proficiencyLevel,
                        nativeLabel: displayNames.of(pendingCode) ?? ""
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