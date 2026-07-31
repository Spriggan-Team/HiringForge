import { useCallback, useEffect, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

//-- Services
import { useJob } from "../../../../../context/job.context";
import type { Department } from "../../../../../features/jobs/JobOffer";
import type { ContractType } from "../../../../../features/contract/contract";
import { useCurrentUser } from "../../../../../hooks/context";
import type { Location } from "../../../../../features/shared/global";
import { formatLocation } from "../../../../../utils/convertor";
import ContractQueries from "../../../../../api/services/contract/queries";
import DepartmentQueries from "../../../../../api/services/department/queries";

//-- Custom components
import Title from "../../../../../layout/components/text/title/title";
import InputLabel from "../../../../../layout/components/form/input/input.label";
import TipTapEditor from "../../../../../layout/components/editors/tiptap/tiptap.editor";
import Separator from "../../../../../layout/components/separator/separator";
import BasicInput, { globalBasicInputInput } from "../../../../../layout/components/form/input/basic.input";
import MenuDrawer, { DrawerBuilder, MenuDrawerBody, MenuDrawerItem, MenuDrawerTrigger } from "../../../../../layout/components/menu/drawer/menu.drawer";
import JobSkill from "../../../components/skills/job.skill";

//--CSS Module
import styles from "./InfoBoxSection.module.css"
import SkillServices from "../../../../../api/services/shared/skill.service";



interface InfoBoxSectionProps{

}

const InfoBoxSection: React.FC<InfoBoxSectionProps> = () => {
    const { t } = useTranslation();
    const currentUser = useCurrentUser();
    const { currentJob, setCurrentJob } = useJob();

    const [skillInput,    setSkillInput]    = useState<string>("");
    const [searchSkills,  setSearchSkills]  = useState<{ id: string; name: string }[]>([]);
    const [selectedSkill, setSelectedSkill] = useState<{ id: string; name: string } | null>(null);

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
            setSearchSkills(skillCacheRef.current.get(trimmed)!);
            lastQueryRef.current = trimmed;
            return;
        }

        const timeout = setTimeout(async () => {
            try {
                const data = await SkillServices.search(trimmed);
                skillCacheRef.current.set(trimmed, data);
                lastQueryRef.current = trimmed;
                setSearchSkills(data);
            } catch (error) {
                console.error("Skill search error:", error);
            }
        }, 400); // reduced from 1000ms → snappier UX

        return () => clearTimeout(timeout);
    }, [skillInput]);

    // -- Add skill 
    const handleAddSkill = useCallback(() => {
        if (!selectedSkill) return;

        // FIX: guard against duplicates
        setCurrentJob(prev => {
            const alreadyAdded = prev.skills?.some(s => s.id === selectedSkill.id);
            if (alreadyAdded) return prev;
            return { ...prev, skills: [...(prev.skills ?? []), selectedSkill] };
        });

        setSkillInput("");
        setSelectedSkill(null);
        setSearchSkills([]);
    }, [selectedSkill, setCurrentJob]);


    // Remove skill 
    const handleRemoveSkill = useCallback(
        (skillId: string) => {
            setCurrentJob(prev => ({
                ...prev,
                skills: prev.skills?.filter(s => s.id !== skillId) ?? [],
            }));
        },
        [setCurrentJob],
    );

    // ── Render ────────────────────────────────────────────────
    return (
        <div className={styles.container}>

            {/* ── General information ── */}
            <div className={`${styles.contextBox} card`}>
                <Title title={t("jobs.createJob.generalInformationSection.title")} />

                <div className={styles.inputs}>
                    {/* Title */}
                    <BasicInput
                        {...globalBasicInputInput}
                        value={currentJob.title}
                        label={t("jobs.createJob.generalInformationSection.inputs.offerTitle.label")}
                        placeholder={t("jobs.createJob.generalInformationSection.inputs.offerTitle.placeholder")}
                        onChange={e =>
                            setCurrentJob(prev => ({ ...prev, title: e.target.value }))
                        }
                    />

                    {/* Department */}
                    <div className={styles.drawer}>
                        <InputLabel label={t("jobs.createJob.generalInformationSection.drawers.department.label")} />
                        <DrawerBuilder
                            value={currentJob.department ?? undefined}
                            placeholder={t("jobs.createJob.generalInformationSection.drawers.department.placeholder")}
                            items={departmentList.map((d, key) => ({ key, value: d, label: d.label }))}
                            onChange={(value: Department) => {
                                if (value) setCurrentJob(prev => ({ ...prev, department: value }));
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
                                items={(currentUser.company?.location ?? []).map((loc, key) => ({
                                    key,
                                    value: loc,
                                    label: formatLocation(loc),
                                }))}
                                onChange={(value: Location) => {
                                    if (value) setCurrentJob(prev => ({ ...prev, location: value }));
                                }}
                            />
                        </div>

                        {/* Contract type */}
                        <div className={styles.drawer}>
                            <InputLabel label={t("jobs.createJob.generalInformationSection.inputs.offerType.label")} />
                            <DrawerBuilder
                                value={currentJob.contract ?? undefined}
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
                                        setCurrentJob(prev => ({
                                            ...prev,
                                            contract: value,
                                        }));
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
                        value={currentJob.content ?? {}}
                        placeholder={t("jobs.createJob.postDescriptionSection.inputs.description.placeholder")}
                        setValue={value => setCurrentJob(prev => ({ ...prev, content: value }))}
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
                                    onChange={(selected: { id: string; name: string }) => {
                                        if (selected) {
                                            setSkillInput(selected.name);
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
                                                    {skill.name}
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

                    {currentJob.skills.length > 0 && (
                        <div className={styles.skills}>
                            {currentJob.skills.map(skill => (
                                <JobSkill
                                    key={skill.id}
                                    content={skill.name}
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
                            value={currentJob?.salary?.min}
                            placeholder={t("jobs.createJob.salarySection.inputs.salary.minSalary.placeholder")}
                            onChange={e =>
                                setCurrentJob(prev => ({
                                    ...prev,
                                    salary: { ...prev.salary, min: Number(e.target.value) },
                                }))
                            }
                        />
                        <Separator className={styles.separator} width="15px" height="2px" />
                        <BasicInput
                            type="number"
                            {...globalBasicInputInput}
                            value={currentJob?.salary?.max}
                            placeholder={t("jobs.createJob.salarySection.inputs.salary.maxSalary.placeholder")}
                            onChange={e =>
                                setCurrentJob(prev => ({
                                    ...prev,
                                    salary: { ...prev.salary, max: Number(e.target.value) },
                                }))
                            }
                        />
                        <DrawerBuilder
                            defaultValue={"EUR"}
                            items={deviseCollection.map((v, key) => ({ key, value: v, label: v }))}
                            triggerProps={{ className: `${styles.deviseTrigger} card-border ` }}
                            onChange={value =>
                                setCurrentJob(prev => ({
                                    ...prev,
                                    salary: { ...prev.salary, devise: value as string },
                                }))
                            }
                        />
                    </div>
                </div>
            </div>
        </div>
    );
};

export default InfoBoxSection;