import { useCallback, useEffect, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

//-- Services
import type { Department } from "../../../../../features/jobs/JobOffer";
import type { ContractType } from "../../../../../features/contract/contract";
import { useAppContext, useCurrentUser } from "../../../../../hooks/context";
import type { Location } from "../../../../../features/shared/global";
import { formatLocation } from "../../../../../utils/convertor";
import ContractQueries from "../../../../../api/services/contract/queries";
import DepartmentQueries from "../../../../../api/services/department/queries";
import SkillServices from "../../../../../api/services/shared/skill.service";
import { useUserJobContext } from "../../../../../context/user.job.context";

//-- Custom components
import Title from "../../../../../layout/components/text/title/title";
import InputLabel from "../../../../../layout/components/form/input/input.label";
import TipTapEditor from "../../../../../layout/components/editors/tiptap/tiptap.editor";
import Separator from "../../../../../layout/components/separator/separator";
import BasicInput, { globalBasicInputInput } from "../../../../../layout/components/form/input/basic.input";
import MenuDrawer, { DrawerBuilder, MenuDrawerBody, MenuDrawerItem, MenuDrawerTrigger } from "../../../../../layout/components/menu/dropdown/menu.dropdown";
import JobSkill from "../../../components/skills/job.skill";

//--CSS Module
import styles from "./InfoBoxSection.module.css"



interface InfoBoxSectionProps{
}

const InfoBoxSection: React.FC<InfoBoxSectionProps> = ({
}) => {
    const { t } = useTranslation();
    const currentUser = useCurrentUser();
    
    const { setPopup } = useAppContext();
    const { editingJob, setEditingJob } = useUserJobContext();

    const [skillInput,    setSkillInput]    = useState<string>("");
    const [searchSkills,  setSearchSkills]  = useState<{ 
        id: string; 
        name: string;
        alias?: string
    }[]>([]);
    const [selectedSkill, setSelectedSkill] = useState<{ 
        id: string; 
        name: string;
        alias?:string;
    } | null>(null);

    const [jobContractTypes, setJobContractTypes] = useState<ContractType[]>([]);
    const [departmentList,   setDepartmentList]   = useState<Department[]>([]);
    const [deviseCollection] = useState<string[]>(["CAD", "USD", "EUR"]);


    // -- Static data init --
    useEffect(() => {
        const init = async () => {
            try {
                const [contractTypes, departments] = await Promise.all([
                    ContractQueries.getContractType(),
                    DepartmentQueries.getDepartmentList(currentUser.company.id),
                ]);
                setJobContractTypes(contractTypes);
                setDepartmentList(departments);
            } catch (error) {
                console.warn("Init error:", error);
            }
        };
        init();
    }, [currentUser.company.id]);


    // -- Skill search — N+1 fix --
    const lastQueryRef  = useRef<string>("");
    const skillCacheRef = useRef<Map<string, { id: string; name: string }[]>>(new Map());

    useEffect(() => {
        const trimmed = skillInput.trim();

        // Don't search if empty or identical to last query
        if (!trimmed || trimmed === lastQueryRef.current) return;

        // Serve from cache immediately if available
        if (skillCacheRef.current.has(trimmed)) {
            console.log({skill: skillCacheRef.current.get(trimmed)})
            setSearchSkills(skillCacheRef.current.get(trimmed)!);
            lastQueryRef.current = trimmed;
            return;
        }

        const timeout = setTimeout(async () => {
            try {
                const data = await SkillServices.search(trimmed);
                console.log({skill: data});
                skillCacheRef.current.set(trimmed, data);
                lastQueryRef.current = trimmed;
                setSearchSkills(data);
            }
            catch (error) {
                console.error("Skill search error:", error);
            }
        }, 400); // reduced from 1000ms → snappier UX

        return () => clearTimeout(timeout);
    }, [skillInput]);

    
    // -- Add skill 
    const handleAddSkill = useCallback(() => {
        if (!selectedSkill) return;

        //  guard against duplicates
        setEditingJob(prev => {
            if (!prev) 
                return prev;
            const alreadyAdded = prev.skills?.some(
                s => s.id === selectedSkill.id
            );

            if (alreadyAdded) return prev;
            return {
                ...prev,
                skills: [
                    ...(prev.skills ?? []),
                    selectedSkill
                ]
            };
        });

        setSkillInput("");
        setSelectedSkill(null);
        setSearchSkills([]);
    }, [selectedSkill, setEditingJob]);


    // Remove skill 
    const handleRemoveSkill = useCallback(
        (skillId: string) => {
            setEditingJob(prev => {
                if(!prev) return prev;
                return({
                    ...prev,
                    skills: prev.skills?.filter(s => s.id !== skillId) ?? [],
                })
            });
        },
        [setEditingJob],
    );

    // ---- Render -----
    return (
        <div className={styles.container}>

            {/*------ General information ---- */}
            <div className={`${styles.contextBox} card`}>
                <Title title={t("jobs.createJob.generalInformationSection.title")} />

                <div className={styles.inputs}>
                    {/* Title */}
                    <BasicInput
                        {...globalBasicInputInput}
                        value={editingJob?.title}
                        label={t("jobs.createJob.generalInformationSection.inputs.offerTitle.label")}
                        placeholder={t("jobs.createJob.generalInformationSection.inputs.offerTitle.placeholder")}
                        onChange={e => {
                            setEditingJob(prev => {
                                return {
                                    ...prev,
                                    title: e.target.value,
                                };
                            });
                        }}
                    />

                    {/* Department */}
                    <div className={styles.drawer}>
                        <InputLabel label={t("jobs.createJob.generalInformationSection.drawers.department.label")} />
                        <DrawerBuilder
                            value={editingJob?.department ?? undefined}
                            placeholder={t("jobs.createJob.generalInformationSection.drawers.department.placeholder")}
                            items={departmentList.map((d, key) => ({
                                key,
                                value: d,
                                label: d.label
                            }))}
                            renderValue={(selected) => (
                                <span>
                                    {selected
                                        ? (selected as Department).label
                                        : t("jobs.createJob.generalInformationSection.drawers.department.placeholder")
                                    }
                                </span>
                            )}
                            onChange={(value: Department) => {
                                if (value) {
                                    setEditingJob(prev => {
                                        if(!prev) return prev;
                                        return({
                                            ...prev,
                                            department: value
                                        })}
                                    );
                                }
                            }}
                        />
                    </div>

                    {/* Inline: Location + Contract */}
                    <div className={styles.inlineInputs}>
                        {/* Location */}
                        <div className={styles.drawer}>
                            <InputLabel label={t("jobs.createJob.generalInformationSection.inputs.location.label")} />
                            <DrawerBuilder
                                value={currentUser.company.location[0]}
                                placeholder={formatLocation(currentUser.company.location[0])}
                                renderValue={(selected) => (
                                    <span>
                                        {selected
                                            ? formatLocation(selected as Location)
                                            : "Select location"}
                                    </span>
                                )}
                                items={(currentUser.company?.location ?? []).map((loc, key) => ({
                                    key,
                                    value: loc,
                                    label: formatLocation(loc),
                                }))}
                                renderItem={(item) => (
                                    <span>
                                        {item.label}
                                    </span>
                                )}
                                onChange={(value: Location) => {
                                    if (value) {
                                        setEditingJob(prev => {
                                            if(!prev) return prev;
                                            return({
                                                ...prev,
                                                location: value
                                            })
                                        });
                                    }
                                }}
                            />
                        </div>

                        {/* Contract type */}
                        <div className={styles.drawer}>
                            <InputLabel label={t("jobs.createJob.generalInformationSection.inputs.offerType.label")} />
                            <DrawerBuilder
                                value={editingJob?.contract ?? undefined}
                                items={jobContractTypes.map(c => ({
                                    key: c.id,
                                    value: c,
                                    label: c.label,
                                }))}
                                placeholder={t("jobs.createJob.generalInformationSection.inputs.offerType.placeholder")}
                                renderValue={selected =>
                                    <span className={selected ? `${styles.activate} activate` : ""}>
                                        {(selected as ContractType)?.label
                                            ?? t("jobs.createJob.generalInformationSection.inputs.offerType.placeholder")}
                                    </span>
                                }
                                onChange={(value) => {
                                    if (value)
                                        setEditingJob(prev => {
                                            if(!prev) return prev;
                                            return({
                                                ...prev,
                                                contract: value,
                                            })
                                        });
                                }}
                            />
                        </div>
                    </div>
                </div>
            </div>

            {/* -- Description -- */}
            <div className={`${styles.contextBox} card`}>
                <Title title={t("jobs.createJob.postDescriptionSection.title")} />
                <div className={styles.inputs}>
                    <InputLabel label={t("jobs.createJob.postDescriptionSection.inputs.description.label")} />
                    <TipTapEditor
                        width="100%"
                        className={styles.editor}
                        sizeable={{y: true}}
                        value={editingJob?.content ?? {}}
                        placeholder={t("jobs.createJob.postDescriptionSection.inputs.description.placeholder")}
                        setValue={value =>{ 
                            setEditingJob(prev => {
                                if(!prev) return prev;
                                return ({ ...prev, content: value });
                            })
                        }}
                    />
                </div>
            </div>

            {/* -- Skills -- */}
            <div className={`${styles.contextBox} card`}>
                <Title title={t("jobs.createJob.skillSection.title")} />
                <div className={styles.inputs}>
                    <div className={styles.inputSkillsSection}>
                        <InputLabel label={t("jobs.createJob.skillSection.inputs.addSkill.label")} />
                        <div className={styles.inputSection}>
                            <div className={`${styles.skillDrawerWrapper} drawer`}>
                                <MenuDrawer
                                    onChange={(selected: { id: string; name: string; alias?: string }) => {
                                        if (selected) {
                                            setSkillInput(selected.alias ?? selected.name);
                                            setSelectedSkill(selected);
                                        }
                                    }}
                                >
                                    <MenuDrawerTrigger
                                        disableCursorPointer
                                        displayArrowDown={false}
                                        applyDefaultStyle={false}
                                        style={{ border: 0 }}
                                        className={`${styles.drawerTrigger} input-like-placeholder`}
                                    >
                                        <BasicInput
                                            {...globalBasicInputInput}
                                            value={skillInput}
                                            placeholder={t("jobs.createJob.skillSection.inputs.addSkill.placeholder")}
                                            onChange={e => {
                                                setSkillInput(e.target.value);
                                                if (!e.target.value) setSelectedSkill(null);
                                            }}
                                        />
                                    </MenuDrawerTrigger>

                                    {searchSkills.length > 0 && skillInput !== "" && (
                                        <MenuDrawerBody
                                            left={0} right={0}
                                            position="initial-absolute"
                                            className={`${styles.selectDropdown} selectDropdown`}
                                        >
                                            {searchSkills.map((skill, i) => (
                                                <MenuDrawerItem
                                                    key={skill.id ?? i}
                                                    value={skill}
                                                    className={`${styles.selectItem} selectItem`}
                                                >
                                                    {skill.alias ?? skill.name}
                                                </MenuDrawerItem>
                                            ))}
                                        </MenuDrawerBody>
                                    )}
                                </MenuDrawer>
                            </div>

                            <button
                                className={styles.addSkillButton}
                                disabled={!selectedSkill}
                                onClick={handleAddSkill}
                            >
                                {t("global.buttons.add")}
                            </button>
                        </div>
                    </div>

                    {editingJob?.skills && editingJob.skills.length > 0 && (
                        <div className={styles.skills}>
                            {editingJob.skills.map(skill => (
                                <JobSkill
                                    key={skill.id}
                                    content={skill.alias ?? skill.name}
                                    onClose={() => handleRemoveSkill(skill.id)}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>

            {/* -- Salary -- */}
            <div className={`${styles.contextBox} card`}>
                <Title title={t("jobs.createJob.salarySection.title")} />
                <div className={styles.inputs}>
                    <InputLabel label={t("jobs.createJob.salarySection.inputs.salary.label")} />
                    <div className={styles.inlineInputs}>
                        <BasicInput
                            type="number"
                            {...globalBasicInputInput}
                            value={editingJob?.salary?.min}
                            placeholder={t("jobs.createJob.salarySection.inputs.salary.minSalary.placeholder")}
                            onChange={(e) => {
                                const min = Number(e.target.value);

                                setEditingJob(prev => {
                                    if(!prev) return prev;
                                    return ({
                                        ...prev,
                                        salary: {
                                            ...prev.salary,
                                            min,
                                        },
                                    })
                                });
                            }}
                        />
                        <Separator className={styles.separator} width="15px" height="2px" />
                        <BasicInput
                            type="number"
                            {...globalBasicInputInput}
                            value={editingJob?.salary?.max}
                            placeholder={t("jobs.createJob.salarySection.inputs.salary.maxSalary.placeholder")}
                            onChange={(e) => {
                                const max = Number(e.target.value);

                                setEditingJob(prev => {
                                    if(!prev) return prev;
                                    return ({
                                        ...prev,
                                        salary: {
                                            ...prev.salary,
                                            max,
                                        },
                                    })
                                });
                            }}
                        />
                        <DrawerBuilder
                            defaultValue={"EUR"}
                            items={deviseCollection.map((v, key) => ({ key, value: v, label: v }))}
                            triggerProps={{ className: `${styles.deviseTrigger} card-border ` }}
                            onChange={value =>
                                setEditingJob(prev => {
                                    if(!prev) return prev;
                                    return({
                                        ...prev,
                                        salary: { ...prev.salary, devise: value as string },
                                    })
                                })
                            }
                        />
                    </div>
                </div>
            </div>
        </div>
    );
};

export default InfoBoxSection;