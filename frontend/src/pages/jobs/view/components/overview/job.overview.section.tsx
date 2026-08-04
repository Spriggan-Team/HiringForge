import { format } from "date-fns";
import { useParams } from "react-router-dom";
import { useCallback, useEffect, useMemo, useState, type SVGProps } from "react";
import { useTranslation } from "react-i18next";

//-- Services & types
import type { JobView,  } from "../../../../../features/jobs/JobOffer";
import { formatRemainingTime, formatSalary } from "../../../../../utils/format";
import type { PersonActionType  } from "../../../../../features/shared/account";
import NotificationQueries from "../../../../../api/services/notification/queries";
import type { JobOfferNotification } from "../../../../../api/services/notification/response";
import RouteScheme from "../../../../../route.scheme";
import JobQueries from "../../../../../api/services/jobs/queries";

//-- Custom components
import Title from "../../../../../layout/components/text/title/title";
import DonutChart, { type DonutChartData } from "../../../../../layout/components/charts/donutChart/donus.chart";


//-- SVG Components
import LocationSVGComponent from "/src/assets/svg/location/location-svgrepo-com.svg"
import ContractSVG  from "/src/assets/svg/menu/signing-the-contract-svgrepo-com.svg"
import MoneySVG  from "/src/assets/svg/person/money-bag-svgrepo-com.svg"
import DateSVGComponent from "/src/assets/svg/catalog/date-svgrepo-com.svg"
import TipTapRenderer from "../../../../../layout/components/editors/tiptap/tiptap.renderer";
import JobSkill from "../../../components/skills/job.skill";
import RecentAction from "../../../../components/recentAction/recent.action";


//-- CSS styles 
import styles from "./JobOverviewSection.module.css"




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

interface JobOverviewSectionProps{
    jobView?: JobView
}


const JobOverviewSection: React.FC<JobOverviewSectionProps> = ({ 
    jobView: defaultJobView = null
}) => {
    const { t } = useTranslation();
    const [jobView, setJobView] = useState<JobView | null>(defaultJobView);

    const { id } = useParams<{ id: string }>();
    const [notifications, setNotifications] = useState<JobOfferNotification[]>([]);

    const keyInformationData = useMemo(() =>{
        if(!jobView)
            return [];
        return ( [
                {
                    label: t("jobs.department"),
                    value: jobView.department?.label
                },
                {
                    label: t('jobs.expertiseLevel'),
                    value: jobView.expertise
                },
                {
                    label: t('jobs.requireLanguage'),
                    value: (jobView.requireLanguages ?? []).map((item) => (
                        `${new Intl.DisplayNames(["en"], { type: 'language' }).of(item.code)} - ${item.proficiencyLevel}`
                    )).join(", ")
                },
                {
                    label: t('jobs.remoteWorkEnable'),
                    value: jobView.jobWorkMode === "remote" ? t("global.text.yes") : t("global.text.no")
                },
                {
                    label: t("global.text.status"),
                    value: jobView.activityStatus ?? jobView.publicationStatus
                }
            ]
        )
    }, [jobView, t]);


    const pieData: DonutChartData[] = useMemo(() =>{
        if(!jobView)
            return [];
        return( [
                {
                    value: jobView.cardinal?.candidates ?? 0,
                    label: "Candidates",
                    color: "#2563EB",
                },
                {
                    value: jobView.cardinal?.interviews ?? 0,
                    label: "Interview",
                    color: "#7C3AED",
                },
                {
                    value: 15,
                    label: "Rejected",
                    color: "#EF4444",
                },
            ]
        )
    }, [jobView]);



    const initializingData = useCallback(async ()=>{
        try{
            if(!id)
                return;

            const data = await JobQueries.getJobView(id);
            setJobView(data);
        
            const notifications = await NotificationQueries.getJobNotfication(id);
            setNotifications(notifications ?? []);
        }
        catch(error){
            console.log('Something went wrong', error)
        }
    }, []);
    

    useEffect(() => {
        initializingData();
    }, []);


    if(!jobView){
        return <p>{t('global.messages.loading')}</p>
    }

    return (
        <div className={styles.container}>
            <div className={styles.main}>
                <div className={`${styles.view} card`}>
                    {/** Header */}
                    <div className={styles.metaItems}>
                        <MetaInfoCard
                            icon={LocationSVGComponent}
                            text={t("global.text.place")}
                            desc={[
                                    jobView.location?.country,
                                    jobView.location?.city,
                                    jobView.location?.street
                                ].filter(Boolean).join(", ") || t("global.text.notSpecify")
                            }
                        />
                        <MetaInfoCard 
                            icon={ContractSVG}
                            text={t("global.contract.title")}
                            desc={jobView.contract?.label || t("global.text.unknown")}
                        />
                        <MetaInfoCard 
                            icon={MoneySVG}
                            text={t("global.salary.title")}
                            desc={formatSalary(jobView.salary) || t('global.text.unknown')}
                        />
                        <MetaInfoCard 
                            icon={DateSVGComponent}
                            text={t("jobs.publicationDate")}
                            desc={jobView.publicationDate ? format(new Date(jobView.publicationDate), "MMMM, d") : t("global.text.unknown")}
                        />
                    </div>

                    {/** TEXT CONTENT (BODY) */}
                    <div className={styles.content}>
                        <TipTapRenderer content={jobView.content}/>
                    </div>
                </div>

                {/** SKILLS */}
                <div className={`${styles.skillSection} card`}>
                    <Title title={t("jobs.createJob.skillSection.title")} />
                    <div className={styles.skills}>
                        {(jobView.skills ?? []).map((item) => (
                            <JobSkill key={item.id ?? item.name} content={item.name} />
                        ))}    
                    </div>
                </div>

                {/** RECENT ACTIVITY */}
                {
                    notifications.length > 0 && (
                        <div className={`${styles.recentActivitySection} card`}>
                            <Title title={t("global.text.recentAction")}/>
                            <div className={styles.actions}>
                                {notifications.map((item, index) => (
                                    <RecentAction 
                                        key={ index} 
                                        title={item.data.jobTitle}
                                        person={`${item.account?.firstName} ${item.account?.lastName}`}
                                        type={item.type}
                                        delay={formatRemainingTime(item.createdAt)}
                                    />
                                ))}
                            </div>
                            <button className={styles.button}>{t('global.messages.seeMore')}</button>
                        </div>
                    )
                }
            </div>

            {/** ASIDE */}
            <div className={`${styles.aside} card`}>
                {/** PIPELINE */}
                <div className={styles.pipeline}>
                    <Title title={t("jobs.pipeline.candidates")} />

                    <DonutChart
                        data={pieData}
                        innerRadius={65}
                        outerRadius={95}
                        centerValue={120}
                        centerLabel="candidats"
                    />

                    {/** LEGENDS */}
                    <div className={styles.legend}>
                        {pieData.map((item, index) => (
                            <div 
                                key={index} 
                                className={styles.item}
                                style={{ ["--bg-color" as string]: item.color }}
                            >
                                <div className={styles.left}>
                                    <div className={styles.circle}/>
                                    {item.label}
                                </div>
                                <span className={styles.right}>{`${item.value}%`}</span>
                            </div>
                        ))}
                        <button className={styles.button}>{t("jobs.pipeline.seeAllCandidates")}</button>
                    </div>
                </div>

                {/** INFORMATION CLES */}
                <div className={`${styles.keyInfoSection} card`}>
                    {keyInformationData.map((item, index) => (
                        <div key={index} className={styles.keyInfo}>
                            <span className={styles.label}>{item.label}</span>
                            {item.value && <span className={styles.value}>{String(item.value)}</span>}
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
};



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
 
