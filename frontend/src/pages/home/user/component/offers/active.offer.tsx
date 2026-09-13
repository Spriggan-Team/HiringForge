import React, { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";

//--Services
import { activeJobOfferData } from "../../../../../core/mock/job.data";
import JobQueries from "../../../../../api/services/jobs/queries";
import type { JobOfferViewLightModel } from "../../../../../features/jobs/JobOffer";
import { CardPlaceholder } from "../../../../../layout/components/cards/placeholder/card.placeholder";
import { useAppNavigate } from "../../../../../hooks/navigation";


//-- Custom component
import SectionHeader from "../../../../../layout/components/sections/sectionHeader/section.header";
import ViewAllLink from "../../../../../layout/components/link/view.all.link";
import Gauge from "../../../../../layout/components/progress/gauge/gauge";
import Title from "../../../../../layout/components/text/title/title";
import TagList from "../../../../../layout/components/text/tag.list";

//-- SVG Components
import CandidateSVG from "/src/assets/svg/menu/candidate-for-elections-svgrepo-com.svg?react"

//-- Image Object
import ImagePlaceholder from "/src/assets/images/image-placeholder.png"

//-- CSS Styles
import styles from "./ActiveOffer.module.css"
import RouteScheme from "../../../../../route.scheme";






interface ActiveOfferSectionProps{}



const ActiveOfferSection: React.FC<ActiveOfferSectionProps> = () => {
    const { t } = useTranslation();
    const [activeOffer, setActiveOffers] = useState<JobOfferViewLightModel[] | null>(null);
    const navigate = useAppNavigate();

    useEffect(()=>{
        const fetchActiveOffer = async ()=>{
            try{
                const data = await JobQueries.getJobOffersWithCriteria({
                    filters: {
                        activityStatus: 'active'
                    }
                });

                setActiveOffers(data);
            }
            catch(error){
                console.log("Something went wrong while retreieving active offer", error)
            }
        }
        fetchActiveOffer();
    },[])


    return (
        <div className={`${styles.container}`}>
            <SectionHeader
                title={t("userHome.acitveOffer.title")}
                action={<ViewAllLink onClick={()=> navigate(RouteScheme.userJobs, { menuId: 'poste' })} />}
            />
            <div className={`${styles.content} scrollbar`}>
                {
                    activeOffer && activeOffer.length > 0 ?
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
                        : <CardPlaceholder text={t("userHome.acitveOffer.noActiveOffers")}/>
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
                <TagList tags={tags ?? []} />
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
 