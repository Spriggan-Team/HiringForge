

import {  useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useTranslation } from "react-i18next";

//-- Services
import RouteScheme from "../../../route.scheme";
import ApplicationQueries from "../../../api/services/application/queries";


//-- Custom Component
import KpiCard, { KpiCount, KpiPercentage } from "./component/kpi/kpi.card";
import RecruitmentPipeline from "./component/pipeline/recrutement.pipeline";
import DatePicker from "../../../layout/components/cards/calendar/datepicker/DatePicker";
import Separator from "../../../layout/components/separator/separator";
import SectionHeader from "../../../layout/components/sections/sectionHeader/section.header";
import Agenda from "./component/agenda/agenda";
import ActiveOfferSection from "./component/offers/active.offer";
import RecentActionPool from "./component/recent/recent.action.pool";
import StatsChart from "./component/stats/stast.chart";


//-- SVG Components
import JobOfferSVG from "/src/assets/svg/menu/work-svgrepo-com-v2.svg?react"
// import ReviewJobOfferSVG from "/src/assets/svg/menu/aethersx2-svgrepo-com.svg?react"
import InterviewsSVG from "/src/assets/svg/menu/user-speak-rounded-svgrepo-com.svg?react"
import HiredSVG from "/src/assets/svg/menu/hire-a-helper-svgrepo-com.svg?react"

//-- CSS Styles
import styles from "./UserHome.module.css"
import type { Dataset } from "../../../layout/components/charts/lineChart/lineChart";
import { ViewModelFactory } from "../../../utils/view.model.factory";
import { useAppContext } from "../../../hooks/context";



const UserHome = () => {
    const navigate = useNavigate();
    const { t } = useTranslation();
    const { kpiData, setNotificationCount } = useAppContext();
    
    const [applicationsKpiOverThisWeekChartDataset, setApplicationsKpiOverThisWeekChartDataset] = useState<Dataset | null>(null) //Line chart dataset

    useEffect(()=>{
        const token = localStorage.getItem("token") ?? undefined;
        if(!token)
            navigate(RouteScheme.main);

        const initApplicationKpiLineChartData = async ()=>{
            try{
                // Postulation Kpi Line chart
                const postulationMetricsData = await ApplicationQueries.getJobPostulationMetrics({
                    timeframe: 'week'
                })

                const metricsArray = Array.isArray(postulationMetricsData) ? postulationMetricsData : [];
                setApplicationsKpiOverThisWeekChartDataset(ViewModelFactory.buildChartDataset({
                    metrics: [metricsArray], 
                    timeframe: "week",
                    seriesOptions: [
                        { dots: null, color:  "#22C55E" }
                    ]
                }));
            }
            catch(error){
                console.log("Something went wrong while retreiving kpis charts")
            }
        }

        initApplicationKpiLineChartData();
    },[]);


    
    return ( 
        <div className={styles.container}>
            <main className={styles.main}>
                {/** KPI SECTIONS */}
                <div className={styles.kpiSection}>
                    {/**Postulation */}
                    <KpiCard
                        displayCurve={true}
                        dataset={applicationsKpiOverThisWeekChartDataset}
                        increase={kpiData?.applicationIncreaseThisWeek}
                        title={t("userHome.kpi.postulationRate")}
                    >
                        <KpiPercentage percent={kpiData?.applicationRate} />
                    </KpiCard>
                    {/** Published Offer */}
                    <KpiCard
                        title={t("userHome.kpi.openOffers")}
                        // increase={kpiData.inc}
                        increase={null}
                        svg={JobOfferSVG}
                        iconBgColor="#f1f0fe"
                    >
                        <KpiCount count={kpiData?.publicOffers} />
                    </KpiCard>
                    {/** Interviews */}
                    <KpiCard
                        svg={InterviewsSVG}
                        iconBgColor="#f0e8fd"
                        trendLabel={t("global.dates.thisMonth")}
                        title={t("userHome.kpi.interviews")}
                        svgStyle={{ color: "#cdaaec" }}
                        increase={kpiData?.interviewsIncreaseThisWeek}
                    >
                        <KpiCount count={kpiData?.scheduledInterviews} />
                    </KpiCard>
                    {/** Hired  */}
                    <KpiCard
                        svg={HiredSVG}
                        iconBgColor="#e2f6ec"
                        title={t("userHome.kpi.hired")}
                        increase={kpiData?.hiredIncreaseThisWeek}
                    >
                        <KpiCount count={kpiData?.hiredApplicationCount} />
                    </KpiCard>
                </div>
                

                {/** SPLIT VIEW */}
                <div className={styles.splitView}>
                    {/** MAIN VIEW */}
                    <div className={styles.boardContent}>
                        <div className={styles.recruitmentPipeline}>
                            <SectionHeader
                                title={t("userHome.borad.kanban.title")} 
                                // action={<ViewAllLink />}
                            />
                            <RecruitmentPipeline />
                        </div>

                        {/** ACTIVE OFFERS */}
                        <Separator height="2px"/>
                        <ActiveOfferSection />

                        {/** CURVES & RECENT ACTIONS */}
                        <div className={styles.bottom}>
                            <div className={styles.recent}>
                                <RecentActionPool  setNotificationCount={setNotificationCount} />
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
                            {/* <PriorityTask /> */}
                        </div>
                        {/** DATES/ CALENDAR */}
                        <div className={styles.calendarSection}>
                            <SectionHeader
                                title={t("global.dates.calendar")}
                                // action={<ViewAllLink />}
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

