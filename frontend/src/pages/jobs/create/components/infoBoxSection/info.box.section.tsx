import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";

//-- Services
import { useJob } from "../../../../../context/job.context";

//-- Custom components
import Title from "../../../../../layout/components/text/title/title";
import InputLabel from "../../../../../layout/components/form/input/input.label";
import TipTapEditor from "../../../../../layout/components/editors/tiptap/tiptap.editor";
import Separator from "../../../../../layout/components/separator/separator";
import BasicInput, { type BasicInputProps } from "../../../../../layout/components/form/input/basic.input";
import MenuDrawer, { MenuDrawerBody, MenuDrawerItem, MenuDrawerTrigger } from "../../../../../layout/components/menu/drawer/menu.drawer";
import JobSkill from "../../../user/components/skills/job.skill";

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
    /** i18n */
    const { t } = useTranslation();
    
    /**-- Context --*/
    const { currentJob, setCurrentJob } = useJob();

    const [location, setLocation] = useState<string>("");
    const [skillInput, setSkillInput] = useState<string>("");
    const [jobContratType, setJobContractType] =useState<string[]>([]);
    const [deviseCollecttion, setDeviseCollection] = useState<string[]>(["CAD", "USD", "EUR"]);



    /**Render */
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
                        value={currentJob.title}
                        onChange={(event) => {
                            setCurrentJob((prev) => {
                                return {
                                    ...prev,
                                    title: event.target.value,
                                };
                            });
                        }}
                        label={t("jobs.createJob.generalInformationSection.inputs.offerTitle.label")}
                        placeholder={t("jobs.createJob.generalInformationSection.inputs.offerTitle.placeholder")}
                    />

                    <div  className={styles.drawer}>
                        <InputLabel label={t("jobs.createJob.generalInformationSection.drawers.department.label")} />
                        <MenuDrawer>
                            <MenuDrawerTrigger
                            {...globalBasicInputInput}
                            className={`${globalBasicInputInput.className} input-like-placeholder`}
                            >
                                {(selected) => (
                                    <span>{selected ?? t("jobs.createJob.generalInformationSection.drawers.department.placeholder")}</span>
                                )}
                            </MenuDrawerTrigger>
                            <MenuDrawerBody
                                position="initial-absolute"
                            >
                                <MenuDrawerItem value={"Developper"}>
                                    Developper
                                </MenuDrawerItem>
                            </MenuDrawerBody>
                        </MenuDrawer>
                    </div>

                    <div className={styles.inlineInputs}>
                        <BasicInput
                            value={location}
                            {...globalBasicInputInput}
                            onChange={(event)=> setLocation(event.target.value)}
                            label={t("jobs.createJob.generalInformationSection.inputs.location.label")}
                            placeholder={t("jobs.createJob.generalInformationSection.inputs.location.placeholder")}
                        />

                        <div className={styles.drawer}>
                            <InputLabel label={t("jobs.createJob.generalInformationSection.inputs.offerType.label")} />
                            <MenuDrawer>
                                <MenuDrawerTrigger
                                {...globalBasicInputInput}
                                className={`${globalBasicInputInput.className} input-like-placeholder`}
                                >
                                    {(selected) =>{
                                        if(selected)
                                            setCurrentJob((prev)=>{
                                                return ({...prev, contract: selected})
                                            })
                                        return (
                                            <span className={`${selected ? styles.activate : ""}`}>
                                                {selected ?? t("jobs.createJob.generalInformationSection.inputs.offerType.placeholder")}
                                            </span>
                                        );
                                    }}
                                </MenuDrawerTrigger>
                                <MenuDrawerBody
                                    position="initial-absolute"
                                >
                                    {
                                        jobContratType.map((value, index)=>(
                                            <MenuDrawerItem key={index} value={value}>{value}</MenuDrawerItem>
                                        ))
                                    }
                                </MenuDrawerBody>
                            </MenuDrawer>
                        </div>

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
                        className={styles.editor}
                        setValue={(value)=>{
                            setCurrentJob((prev)=>{
                                return ({...prev, content: value})
                            })
                        }}
                        value={currentJob.content ?? {}}
                        placeholder={t("jobs.createJob.postDescriptionSection.inputs.description.placeholder")}
                    />
                </div>
            </div>


            {/** COMPETENCES Skill */}
            <div className={`${styles.contextBox} card`}>
                <Title  title={t("jobs.createJob.skillSection.title")} />
                <div 
                    style={{ gap: "15px" }}
                    className={styles.inputs}
                >
                    <div className={styles.inputSkillsSection}>
                        <InputLabel label={t("jobs.createJob.skillSection.inputs.addSkill.label")} />
                        <div className={styles.inputSection}>
                            <BasicInput
                                {...globalBasicInputInput}
                                value={skillInput} 
                                onChange={(e) => setSkillInput(e.target.value)}
                                placeholder={t("jobs.createJob.skillSection.inputs.addSkill.placeholder")}
                            />
                            <button
                                onClick={()=>{
                                    const skill = skillInput.trim();
                                    if (!skill) return;

                                    setCurrentJob((prev) => {
                                        return {
                                            ...prev,
                                            skills: [...(prev.skills ?? []), skill],
                                        };
                                    });

                                    setSkillInput("");
                                }}
                            >
                                {t("global.buttons.add")}
                            </button>
                        </div>
                    </div>

                    <div className={styles.skills}>
                        {
                            currentJob.skills.map((skill, index)=>(
                                <JobSkill
                                    key={index}
                                    content={skill}
                                    onClose={(content) =>{
                                        setCurrentJob((prev) => {
                                            return {
                                                ...prev,
                                                skills: prev.skills?.filter((skill) => skill !== content) ?? [],
                                            };
                                        });
                                    }}
                                />
                            ))
                        }
                    </div>
                </div>
            </div>


            {/**SALARY SECTION */}
            <div className={`${styles.contextBox} card`}>
                <Title title={t("jobs.createJob.salarySection.title")} />
                <div>
                    <InputLabel label={t("jobs.createJob.salarySection.inputs.salary.label")}/>
                    <div className={styles.inlineInputs}>
                        <BasicInput
                            type="number"
                            {...globalBasicInputInput}
                            value={currentJob?.salary?.min}
                            onChange={(event)=>{
                                setCurrentJob((prev) => {
                                    return {
                                        ...prev,
                                        salary: {
                                            ...prev.salary,
                                            min: Number(event.target.value),
                                        },
                                    };
                                });
                            }}
                            placeholder={t("jobs.createJob.salarySection.inputs.salary.minSalary.placeholder")}
                        />
                        <Separator
                            className={styles.separator}
                            width="15px" height="2px"
                        />
                        <BasicInput
                            type="number"
                            {...globalBasicInputInput}
                            value={currentJob?.salary?.max}
                            onChange={(event)=>{
                                setCurrentJob((prev) => {
                                    return {
                                        ...prev,
                                        salary: {
                                            ...prev.salary,
                                            max: Number(event.target.value),
                                        },
                                    };
                                });
                            }}
                            placeholder={t("jobs.createJob.salarySection.inputs.salary.minSalary.placeholder")}
                        />
                        <MenuDrawer>
                            <MenuDrawerTrigger className={`${styles.drawerTrigger} card-border`}>
                                {(value)=> {
                                    setCurrentJob((prev)=>({...prev, salary:{ devise: value}}))
                                    return (<span>{value ?? "EUR"}</span>)
                                }}
                            </MenuDrawerTrigger>
                            <MenuDrawerBody>
                                {
                                    deviseCollecttion.map((value, index)=>(
                                        <MenuDrawerItem key={index} value={value} >{value}</MenuDrawerItem>
                                    ))
                                }
                            </MenuDrawerBody>
                        </MenuDrawer>
                    </div>
                </div>
            </div>
        </div>
    );
}
 
export default InfoBoxSection;