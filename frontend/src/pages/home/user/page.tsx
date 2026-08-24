

import {  useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useTranslation } from "react-i18next";

//-- Services
import RouteScheme from "../../../route.scheme";
import UserQueriesServices from "../../../api/services/user/queries";
import type {  RecruiterDashboardKpis } from "../../../features/dashboard/KpiData";

//-- Custom Component
import KpiCard, { KpiCount, KpiPercentage } from "./component/kpi/kpi.card";
import RecruitmentPipeline from "./component/pipeline/recrutement.pipeline";
import DatePicker from "../../../layout/components/cards/calendar/datepicker/DatePicker";
import Separator from "../../../layout/components/separator/separator";
import PriorityTask from "./component/task/priority.task";
import SectionHeader from "../../../layout/components/sections/sectionHeader/section.header";
import ViewAllLink from "../../../layout/components/link/view.all.link";
import Agenda from "./component/agenda/agenda";
import ActiveOfferSection from "./component/offers/active.offer";
import RecentActionPool from "./component/recent/recent.action.pool";
import StatsChart from "./component/stats/stast.chart";


//-- SVG Components
import JobOfferSVG from "/src/assets/svg/menu/work-svgrepo-com-v2.svg?react"
import ReviewJobOfferSVG from "/src/assets/svg/menu/aethersx2-svgrepo-com.svg?react"
import InterviewsSVG from "/src/assets/svg/menu/user-speak-rounded-svgrepo-com.svg?react"
import HiredSVG from "/src/assets/svg/menu/hire-a-helper-svgrepo-com.svg?react"

//-- CSS Styles
import styles from "./UserHome.module.css"





const UserHome = () => {
    const navigate = useNavigate();
    const { t } = useTranslation();
    
    const [kpiData, setKpiData] = useState<RecruiterDashboardKpis>()

    useEffect(()=>{
        const token = localStorage.getItem("token") ?? undefined;
        if(!token)
            navigate(RouteScheme.main);

        const retreiveKPI =  async ()=>{
            const kpiData = await UserQueriesServices.getKPI();
            setKpiData(kpiData);
        }

        retreiveKPI();
    },[]);


    
    return ( 
        <div className={styles.container}>
            <main className={styles.main}>
                {/** KPI SECTIONS */}
                <div className={styles.kpiSection}>
                    <KpiCard  
                        title={t("userHome.kpi.postulationRate")}
                        increase={2.2}
                        displayCurve={true}
                    >
                        <KpiPercentage percent="90" />
                    </KpiCard>
                    <KpiCard
                        title={t("userHome.kpi.openOffers")}
                        increase={12}
                        svg={JobOfferSVG}
                        iconBgColor="#f1f0fe"
                    >
                        <KpiCount count="12" />
                    </KpiCard>
                    <KpiCard
                        title={t("userHome.kpi.reviewOffers")}
                        increase={0.3}
                        svg={ReviewJobOfferSVG}
                        iconBgColor="#feeed2"
                    >
                        <KpiCount count="8" />
                    </KpiCard>
                    <KpiCard
                        increase={1.2}
                        svg={InterviewsSVG}
                        trendLabel={t("global.dates.today")}
                        title={t("userHome.kpi.interviews")}
                        iconBgColor="#f0e8fd"
                        svgStyle={{ color: "#cdaaec" }}
                    >
                        <KpiCount count="4" />
                    </KpiCard>
                    <KpiCard
                        increase={5.2}
                        svg={HiredSVG}
                        title={t("userHome.kpi.hired")}
                        iconBgColor="#e2f6ec"
                    >
                        <KpiCount count="124" />
                    </KpiCard>
                </div>
                

                {/** SPLIT VIEW */}
                <div className={styles.splitView}>
                    {/** MAIN VIEW */}
                    <div className={styles.boardContent}>
                        <div className={styles.recruitmentPipeline}>
                            <SectionHeader
                                title={t("userHome.borad.kanban.title")} 
                                action={<ViewAllLink />}
                            />
                            <RecruitmentPipeline />
                        </div>

                        {/** ACTIVE OFFERS */}
                        <Separator height="2px"/>
                        <ActiveOfferSection />

                        {/** CURVES & RECENT ACTIONS */}
                        <div className={styles.bottom}>
                            <div className={styles.recent}>
                                <RecentActionPool />
                            </div>
                            <div className={styles.stats}>
                                <StatsChart />
                            </div>
                        </div>
                    </div>


                    {/** SIDE ITEMS */}
                    <div className={styles.sidebar}>
                        {/** PRIORITY TASK */}
                        <div className={styles.priorityTaskSection}>
                            <PriorityTask />
                        </div>
                        {/** DATES/ CALENDAR */}
                        <div className={styles.calendarSection}>
                            <SectionHeader
                                title={t("global.dates.calendar")}
                                action={<ViewAllLink />}
                            />
                            <DatePicker className={styles.calendar} width="100%"/>
                        </div>
                        {/** AGENDA */}
                        <div className={styles.agenda}>
                            <Agenda />
                        </div>
                    </div>
                </div>
            </main>
        </div>
    );
}
 
export default UserHome;

