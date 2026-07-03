import { useState } from "react";
import { useTranslation } from "react-i18next";

//-- Custom components
import BasicInput, { type BasicInputProps } from "../../../../../layout/components/form/input/basic.input";
import Title from "../../../../../layout/components/text/title/title";
import InputLabel from "../../../../../layout/components/form/input/input.label";
import TipTapEditor from "../../../../../layout/components/editors/tiptap/tiptap.editor";
import Separator from "../../../../../layout/components/separator/separator";
import MenuDrawer, { MenuDrawerBody, MenuDrawerItem, MenuDrawerTrigger } from "../../../../../layout/components/menu/drawer/menu.drawer";

//--CSS Module
import styles from "./InfoBoxSection.module.css"

interface InfoBoxSectionProps{}

const globalBasicInputInput: BasicInputProps = {
    width: "100%",
    backgroundColor: "#FFFFFF",
    className: `${styles.input} card-border`,
}

const InfoBoxSection: React.FC<InfoBoxSectionProps> = ({

}) => {
    const { t } = useTranslation();
    const [description, setDescription] = useState<string>("");
    
    return (
        <div className={styles.container}>
            {/** GENERALES INFOS SECTION */}
            <div className={`${styles.contextBox} card`}>
                <Title 
                    title={t("jobs.createJob.generalInformationSection.title")}
                />
                <div className={styles.inputs}>
                    <BasicInput
                        {...globalBasicInputInput}
                        label={t("jobs.createJob.generalInformationSection.inputs.offerTitle.label")}
                        placeholder={t("jobs.createJob.generalInformationSection.inputs.offerTitle.placeholder")}
                    />
                    <BasicInput
                        {...globalBasicInputInput}
                        label={t("jobs.createJob.generalInformationSection.inputs.department.label")}
                        placeholder={t("jobs.createJob.generalInformationSection.inputs.department.placeholder")}
                    />
                    <div className={styles.inlineInputs}>
                        <BasicInput
                            {...globalBasicInputInput}
                            label={t("jobs.createJob.generalInformationSection.inputs.location.label")}
                            placeholder={t("jobs.createJob.generalInformationSection.inputs.location.placeholder")}
                        />
                        <BasicInput
                            {...globalBasicInputInput}
                            label={t("jobs.createJob.generalInformationSection.inputs.offerType.label")}
                            placeholder={t("jobs.createJob.generalInformationSection.inputs.offerType.placeholder")}
                        />
                    </div>
                </div>
            </div>

            {/** DESCRIPTION SECTION */}
            <div className={`${styles.contextBox}  card`}>
                <Title title={t("jobs.createJob.postDescriptionSection.title")} />
                <div className={styles.inputs}>
                    <InputLabel label={t("jobs.createJob.postDescriptionSection.inputs.description.label")}/>
                    <TipTapEditor
                        width="100%"
                        value={description}
                        className={styles.editor}
                        setValue={setDescription}
                        placeholder={t("jobs.createJob.postDescriptionSection.inputs.description.placeholder")}
                    />
                </div>
            </div>

            {/**SALARY SECTION */}
            <div className={`${styles.contextBox} card`}>
                <Title title={t("jobs.createJob.salarySection.title")} />
                <div>
                    <InputLabel label={t("jobs.createJob.salarySection.inputs.salary.label")}/>
                    <div className={styles.inlineInputs}>
                        <BasicInput
                            {...globalBasicInputInput}
                            placeholder={t("jobs.createJob.salarySection.inputs.salary.minSalary.placeholder")}
                        />
                        <Separator
                            className={styles.separator}
                            width="15px" height="2px"
                        />
                        <BasicInput
                            {...globalBasicInputInput}
                            placeholder={t("jobs.createJob.salarySection.inputs.salary.minSalary.placeholder")}
                        />
                        <MenuDrawer>
                            <MenuDrawerTrigger className={`${styles.drawerTrigger} card-border`}>
                                {(selected)=> (<span>{selected ?? "EUR"}</span>)}
                            </MenuDrawerTrigger>
                            <MenuDrawerBody>
                                <MenuDrawerItem value="USD" >USD</MenuDrawerItem>
                                <MenuDrawerItem value="CAD" >CAD</MenuDrawerItem>
                                <MenuDrawerItem value="USD" >EUR</MenuDrawerItem>
                            </MenuDrawerBody>
                        </MenuDrawer>
                    </div>
                </div>
            </div>
        </div>
    );
}
 
export default InfoBoxSection;