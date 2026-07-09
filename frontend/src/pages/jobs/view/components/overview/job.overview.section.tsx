import { format } from "date-fns";
import { useState, type SVGProps } from "react";
import { useTranslation } from "react-i18next";

//-- Services & types
import type { JobView,  } from "../../../../../features/jobs/JobOffer";
import { formatSalary } from "../../../../../utils/format";
import type { PersonActionType  } from "../../../../../features/shared/account";

//-- Custom components
import Title from "../../../../../layout/components/text/title/title";
import DonutChart from "../../../../../layout/components/charts/donutChart/donus.chart";


//-- SVG Components
import LocationSVGComponent from "/src/assets/svg/location/location-svgrepo-com.svg"
import ContractSVG  from "/src/assets/svg/menu/signing-the-contract-svgrepo-com.svg"
import MoneySVG  from "/src/assets/svg/person/money-bag-svgrepo-com.svg"
import DateSVGComponent from "/src/assets/svg/catalog/date-svgrepo-com.svg"
import TipTapRenderer from "../../../../../layout/components/editors/tiptap/tiptap.renderer";
import JobSkill from "../../../components/skills/job.skill";
import QuillRenderer from "../../../../../layout/components/editors/quill/quill.renderer";
import RecentAction from "../../../../components/recentAction/recent.action";


//-- CSS styles 
import styles from "./JobOverviewSection.module.css"



interface JobOverviewSectionProps{
    jobView: JobView;
}


const mockData = [
    {
        delay: "11min",
        person: "Sarah Martin",
        title: "Frontend Developper",
        type: "postulate" as PersonActionType,
    },
    {
        delay: "1h",
        person: "Marc Leroy",
        title: "Entretien Technique",
        type: "create-interview" as PersonActionType,
    },
    {
        delay: "2h",
        person: null,
        title: "Backend Developper",
        type: "publish-offer" as PersonActionType,
    },
    {
        delay: "3h",
        person: "Alice Dupont",
        title: "Entretien Technique",
        type: "confirm-interview" as PersonActionType,
    }
]




const JobOverviewSection: React.FC<JobOverviewSectionProps> = ({
    jobView
}) => {
    const {t} = useTranslation();

    const [keyInformationData, setKeyInformationData ] = useState([
        {
            label: t("jobs.department"),
            value: "Senior"
        },
        {
            label: t('jobs.expertiseLevel'),
            value: jobView.expertise
        },
        {
            label: t('jobs.requireLanguage'),
            value: jobView.requireLanguages.map((item) => (
                `${new Intl.DisplayNames(["en"], { type: 'language'}).of(item.code)} - ${item.proficiencyLevel}`
            )).join( " , ")
        },
        {
            label: t('jobs.remoteWorkEnable'),
            value: jobView.jobWorkMode === "remote"
        },
        {
            label: t("global.text.status"),
            value: jobView.activityStatus ?? jobView.publicationStatus
        }
    ])

    const [pieData, setPieData] = useState([
        {
            value: 80,
            count: 147,
            label: "Candidates",
            color: "#2563EB",
        },
        {
            value: 25,
            count: 75,
            label: "Interview",
            color: "#7C3AED",
        },
        {
            count: 25,
            value: 15,
            label: "Rejected",
            color: "#EF4444",
        },
    ]);

    return (
        <div className={styles.container}>
            <div className={styles.main}>
                <div className={`${styles.view} card`}>
                    {/** Header */}
                    <div className={styles.metaItems}>
                        <MetaInfoCard 
                            icon={LocationSVGComponent}
                            text={[
                                    jobView.location?.country,
                                    jobView.location?.city,
                                    jobView.location?.street
                                ].filter(Boolean).join(" , ") ??  t("global.text.notSpecify")
                            }
                            desc={t("global.text.place")}
                        />
                        <MetaInfoCard 
                            icon={ContractSVG}
                            desc={t("global.contract.title")}
                            text={jobView.contract ?? t("global.text.unknown")}
                        />
                        <MetaInfoCard 
                            icon={MoneySVG}
                            desc={t("global.salary.title")}
                            text={formatSalary(jobView.salary)}
                        />
                        <MetaInfoCard 
                            icon={DateSVGComponent}
                            desc={t("jobs.publicationDate")}
                            text={jobView.publicationDate ? format(jobView.publicationDate, "MMMM, d"): t("global.text.unknown")}
                        />
                    </div>

                    {/** TEXT CONTENT (BODY) */}
                    <div className={styles.content}>
                        <QuillRenderer content={jobView.content}/>
                    </div>
                </div>

                {/** SKILLS */}
                <div className={`${styles.skillSection} card`}>
                    <Title title={t("jobs.createJob.skillSection.title")} />
                    <div className={styles.skills}>
                        {
                            jobView.skills.map((item, key)=>(
                                <JobSkill key={key} content={item} />
                            ))
                        }    
                    </div>
                </div>

                {/** RECENT ACTIVITY */}
                <div className={`${styles.recentActivitySection} card`}>
                    <Title  title={t("global.text.recentAction")}/>
                    <div className={styles.actions}>
                        {mockData.map((item)=>(
                            <RecentAction 
                                title={item.title}
                                person={item.person}
                                type={item.type}
                                delay={item.delay}
                            />
                        ))}
                    </div>
                    <button className={styles.button}>{t('global.messages.seeMore')}</button>
                </div>
            </div>


            {/** ASIDE */}
            <div className={`${styles.aside} card`}>
                {/**PIPELINE */}
                <div className={styles.pipeline}>
                    <Title title={t("jobs.pipeline.candidates")} />

                    {/** DONUTS - CHARTS */}
                    <DonutChart
                        data={pieData}
                        innerRadius={65}
                        outerRadius={95}
                        centerValue={120}
                        centerLabel="candidats"
                    />

                    {/** LEGENDS */}
                    <div className={styles.legend}>
                        {
                            pieData.map((item, key)=>(
                                <div 
                                    key={key} className={styles.item}
                                    style={{ ["--bg-color" as string]: item.color }}
                                >
                                    <div className={styles.left}>
                                        <div className={styles.circle}/>
                                        {item.label}
                                    </div>
                                    <span className={styles.right}>{`${item.count} (${item.value}%)`}</span>
                                </div>
                            ))
                        }
                        <button className={styles.button}>{t("jobs.pipeline.seeAllCandidates")}</button>
                    </div>
                </div>

                {/** INFORMATION CLES */}
                <div className={`${styles.keyInfoSection} card`}>
                    {
                        keyInformationData.map((item, key)=>(
                            <div 
                                key={key}
                                className={styles.keyInfo}
                            >
                                <span className={styles.label}>{item.label}</span>
                                <span className={styles.value}>{item.value}</span>
                            </div>
                        ))
                    }
                </div>

            </div>
        </div>
    );
}
 
export default JobOverviewSection;


/** Meta Info card */

interface MetaInfoCardProps{
    icon?: React.FC<React.SVGProps<SVGSVGElement>>;
    iconStyle?: React.CSSProperties;
    text: string
    desc: string;
}


const MetaInfoCard: React.FC<MetaInfoCardProps> = ({
    icon : Icon,
    iconStyle,
    text,
    desc
}) => {
    return (
        <div className={styles.meta}>
            {Icon &&  <Icon className={styles.svg} width={15} height={15} style={iconStyle}/>}
            <div className={styles.metaContent}>
                <span className={styles.title}>{text}</span>
                <span className={styles.desc}>{desc}</span>
            </div>
        </div>
    );
}
 
