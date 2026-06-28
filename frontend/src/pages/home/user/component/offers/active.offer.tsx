import React, { useState } from "react";
import { useTranslation } from "react-i18next";

//-- Custom component
import SectionHeader from "../../../../../layout/components/sections/sectionHeader/section.header";
import ViewAllLink from "../../../../../layout/components/link/view.all.link";
import Gauge from "../../../../../layout/components/progress/gauge/gauge";
import Title from "../../../../../layout/components/text/title/title";

//-- SVG Components
import CandidateSVG from "/src/assets/svg/menu/candidate-for-elections-svgrepo-com.svg"

//-- Image Object
import ImagePlaceholder from "/src/assets/images/image-placeholder.png"

//-- CSS Styles
import styles from "./ActiveOffer.module.css"


const mockData = [
    {
        title: "Développeur Front-End React",
        image: null,
        candidates: 24,
        interviews: 8,
        tags: ["CDI", "Hybride", "Paris"],
        treatmentProgress: 0.72,
        remainingCandidates: 6,
        delay: "1j",
    },
    {
        title: "UX/UI Designer",
        image: null,
        candidates: 17,
        interviews: 5,
        tags: ["CDI", "Remote", "Lyon"],
        treatmentProgress: 0.48,
        remainingCandidates: 9,
        delay: "3j",
    },
    {
        title: "Développeur Back-End Node.js",
        image: null,
        candidates: 31,
        interviews: 12,
        tags: ["CDI", "Hybride", "Bordeaux"],
        treatmentProgress: 0.81,
        remainingCandidates: 4,
        delay: "Aujourd'hui",
    },
    {
        title: "Product Manager",
        image: null,
        candidates: 14,
        interviews: 4,
        tags: ["CDI", "Paris"],
        treatmentProgress: 0.36,
        remainingCandidates: 10,
        delay: "5j",
    },
    {
        title: "Data Analyst",
        image: null,
        candidates: 19,
        interviews: 6,
        tags: ["CDD", "Remote", "Nantes"],
        treatmentProgress: 0.57,
        remainingCandidates: 5,
        delay: "2j",
    },
    {
        title: "Ingénieur DevOps",
        image: null,
        candidates: 11,
        interviews: 3,
        tags: ["CDI", "Télétravail", "Lille"],
        treatmentProgress: 0.29,
        remainingCandidates: 8,
        delay: "6j",
    },
];

interface ActiveOfferSectionProps{}


const ActiveOfferSection: React.FC<ActiveOfferSectionProps> = () => {
    const { t } = useTranslation();
    const [activeOffer, setActiveOffers] = useState(mockData);

    return (
        <div className={`${styles.container}`}>
            <SectionHeader
                title={t("userHome.acitveOffer.title")}
                action={<ViewAllLink />}
            />
            <div className={`${styles.content} scrollbar`}>
                {
                    activeOffer.map((item, key)=>(
                        <ActiveOffer
                            key={key}
                            
                            title={item.title}
                            image={item.image}
                            
                            tags={item.tags}
                            delay={item.delay}

                            interviews={item.interviews}
                            candidates={item.candidates}

                            remainingCandidates={item.remainingCandidates ?? 0}
                            treatmentProgress={item.treatmentProgress ?? 0}
                        />
                    ))
                }
            </div>
        </div>
    );
}
 
export default ActiveOfferSection;


/** -- ActiveOffer -- */




interface ActiveOfferprops{
    title: string;
    tags?: string[];
    delay: string;
    candidates: number;
    interviews: number;
    image?: string | null;
    treatmentProgress: number;
    remainingCandidates: number;
    onClick?: React.MouseEventHandler<HTMLDivElement>
}


const ActiveOffer: React.FC<ActiveOfferprops> = ({
    title,
    image,

    tags,
    delay,

    interviews,
    candidates,
    treatmentProgress = 0,
    remainingCandidates,

    onClick,
}) => {
    const { t } = useTranslation();

    if (treatmentProgress < 0 || treatmentProgress > 1) {
        console.warn("[ActiveOffer] treatmentProgress value must be between 0 & 1");
        return null;
    }

    return (
        <div
            onClick={onClick}
            style={{
                ["--cursor" as string]: onClick ? "pointer" : "default"
            }}
            className={styles.item}
        >
            <div className={styles.header}>
                <img className={styles.img} src={image ?? ImagePlaceholder} alt="" />
                <Title title={title} />
            </div>
            
            {/** TAGS */}
            <div className={styles.tags}>
                {(tags ?? []).map((item, key)=>(
                    <span
                        key={key} 
                        className={styles.tag}
                    >
                        {item}
                    </span>
                ))}
            </div>

            {/** CARDINALS */}
            <div className={styles.cardinal}>
                <p>
                    <span>{candidates}</span>{" "}
                    {t("global.candidate.candidateLabel_one" , { count: candidates })}
                </p>

                <p>
                    <span>{interviews}</span>{" "}
                    {t("global.interview.interviewLabel", { count: interviews })}
                </p>
            </div>
            
            <span className={styles.publishedTime}>{t("global.duration.publishSince", { time: delay })}</span>
            <div className={styles.gaugeSection}>
                    <Gauge
                        height={5}
                        width={"100%"}
                        percent={treatmentProgress}
                        activeColor={ "linear-gradient(to right, #3B82F6, #60A5FA)"}
                        className={styles.gauge}
                        foregroundColor="#E5E7EB"
                    />
                <span className={styles.percent}>{(treatmentProgress * 100).toFixed(2)}%</span>
            </div>
            <div className={styles.bottom}>
                <CandidateSVG width={15} height={15}/>
                <span className={styles.untreated}>+ {remainingCandidates}</span>
            </div>
        </div>
    );
}
 