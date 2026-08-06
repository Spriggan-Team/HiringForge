import { useTranslation } from "react-i18next";
import React, { useEffect, useState, useTransition } from "react";

import ApplicationQueries from "../../../../../api/services/application/queries";
import JobServices from "../../../../../api/services/jobs/command";
import JobQueries from "../../../../../api/services/jobs/queries";
import { currentDay, currentMonth } from '../../../../../utils/dates'

import { LineChart, type Dataset } from "../../../../../layout/components/charts/lineChart/lineChart";
import type { DonutChartData } from "../../../../../layout/components/charts/donutChart/donus.chart";
import DonutChart from "../../../../../layout/components/charts/donutChart/donus.chart";


import styles from "./JobStatisticsSection.module.css";
import type { JobKpis } from "../../../../../features/jobs/JobOffer";



export interface JobStatisticsSectionProps {
    jobId: string;
    jobTitle?: string;
}


/**
 * Generates the Dataset object required by the LineChart without mutating global state
 */
const buildChartDataset = (metrics: number[], timeframe: "week" | "month"): Dataset => {
    const dates = timeframe === "month" ? currentMonth : currentDay;
    const maxVal = metrics.length > 0 ? Math.max(...metrics) : 10;

    return {
        dates,
        maximum: maxVal > 0 ? maxVal : 10,
        data: [
            {
                type: "area",
                areaMultiplier: 1,
                x: metrics,
                attr: {
                    fill: "url(#blue-gradient)",
                },
                defs: [
                    {
                        gradients: {
                            linear: [
                                {
                                    id: "blue-gradient",
                                    target: "fill",
                                    coords: { x1: "0%", y1: "0%", x2: "0%", y2: "100%" },
                                    stop: [
                                        { offset: "0%", stopColor: "#3b82f6", stopOpacity: 0.4 },
                                        { offset: "100%", stopColor: "#3b82f6", stopOpacity: 0.0 },
                                    ],
                                },
                            ],
                        },
                    },
                ],
            },
            {
                type: "line",
                x: metrics,
                attr: {
                    stroke: "#3b82f6",
                    strokeWidth: 3,
                },
                dotIndicator: {
                    r: 5,
                    fill: "#3b82f6",
                    stroke: "#ffffff",
                    strokeWidth: 2,
                    rPulse: 7,
                },
            },
        ],
    };
};


export const JobStatisticsSection: React.FC<JobStatisticsSectionProps> = ({
    jobId,
    jobTitle = null,
}) => {
    const { t } = useTranslation();
    const [timeframe, setTimeframe] = useState<"week" | "month">("week");

    const [candidateStatusData, setCandidateStatusData] = useState<DonutChartData[]>([]);
    const [applicationsOverTimeData, setApplicationsOverTimeData] = useState<Dataset>();
    
    const [isLoading, setIsLoading] = useState<boolean>(true);
    const [jobKpisData, setJobKpisData] = useState<JobKpis>({
        totalApplications: 0,
        applicationIncreaseThisWeek: 0,
        rejectionRate: 0,
        offersGenerated: 0,
        avgTimeToHireDays: 0,
    });

    useEffect(() => {
        let isMounted = true;

        const fetchData = async () => {
            setIsLoading(true);
            try {
                // Fetch stats and metrics in parallel
                const [jobKpi, statsData, postulationMetricsData] = await Promise.all([
                    JobQueries.getJobKpis(jobId),
                    JobQueries.getUserStats(jobId),
                    JobQueries.getPostulationMetrics({ jobId, timeframe }),
                ]);

                if (!isMounted) return;

                // Job Kpis
                if (jobKpi) {
                    setJobKpisData({
                        totalApplications: jobKpi.totalApplications ?? 0,
                        applicationIncreaseThisWeek: jobKpi.applicationIncreaseThisWeek ?? 0,
                        rejectionRate: jobKpi.rejectionRate ?? 0,
                        offersGenerated: jobKpi.offersGenerated ?? 0,
                        avgTimeToHireDays: jobKpi.avgTimeToHireDays ?? 0,
                    });
                }

                //  Donut Chart Data
                const stats = statsData ?? { preselect: 0, interviews: 0, offer: 0, rejected: 0 };
                const candidatesStatus: DonutChartData[] = [
                    { label: t("jobs.candidateStatuses.preselected"), value: stats.preselect, color: "#3b82f6" },
                    { label: t("jobs.candidateStatuses.inInterviews"), value: stats.interviews, color: "#8b5cf6" },
                    { label: t("jobs.candidateStatuses.generatedOffer"), value: stats.offer, color: "#10b981" },
                    { label: t("jobs.candidateStatuses.rejected"), value: stats.rejected, color: "#ef4444" },
                ];
                setCandidateStatusData(candidatesStatus);

                // Format Line Chart Data
                const metricsArray = Array.isArray(postulationMetricsData) ? postulationMetricsData : [];
                setApplicationsOverTimeData(buildChartDataset(metricsArray, timeframe));
                

            }
            catch (error) {
                console.error("Failed to load job statistics:", error);
            }
            finally {
                if (isMounted) 
                    setIsLoading(false);
            }
        };

        fetchData();

        return () => {
            isMounted = false;
        };
    }, [jobId, timeframe, t])


    //----- RENDER

    if(isLoading){
        return (<p>{t('global.messages.loading')}</p>)
    }


    return (
        <section className={styles.container}>
            {/* Header */}
            <div className={styles.header}>
                <div>
                    <h2 className={styles.title}>Statistiques de recrutement</h2>
                    {
                        jobTitle && (
                            <p className={styles.subtitle}>
                                Offre : <strong>{jobTitle}</strong>
                            </p>
                        )
                    }
                </div>
                <div className={styles.filterGroup}>
                    <button
                        className={`${styles.filterBtn} ${timeframe === "week" ? styles.active : ""}`}
                        onClick={() => setTimeframe("week")}
                    >
                        Par semaine
                    </button>
                    <button
                        className={`${styles.filterBtn} ${timeframe === "month" ? styles.active : ""}`}
                        onClick={() => setTimeframe("month")}
                    >
                        Par mois
                    </button>
                </div>
            </div>

            {/* Metrics Card ATS */}
            <div className={styles.kpiGrid}>
                <div className={styles.kpiCard}>
                    <span className={styles.kpiLabel}>Total Postulations</span>
                    <span className={styles.kpiValue}>{jobKpisData.totalApplications}</span>
                    <span className={`${styles.kpiBadge} ${styles.positive}`}>+{jobKpisData.applicationIncreaseThisWeek} {t('global.dates.thisWeek')}</span>
                </div>
                <div className={styles.kpiCard}>
                    <span className={styles.kpiLabel}>Offres Générées</span>
                    <span className={styles.kpiValue}>{jobKpisData.offersGenerated}</span>
                    <span className={styles.kpiSubtext}>3 acceptées</span>
                </div>
                <div className={styles.kpiCard}>
                    <span className={styles.kpiLabel}>Taux de Rejet</span>
                    <span className={styles.kpiValue}>{jobKpisData.rejectionRate}%</span>
                    <span className={styles.kpiSubtext}>168 candidats écartés</span>
                </div>
                <div className={styles.kpiCard}>
                    <span className={styles.kpiLabel}>Temps Moyen d'Embauche</span>
                    <span className={styles.kpiValue}>{jobKpisData.avgTimeToHireDays} jours</span>
                    <span className={`${styles.kpiBadge} ${styles.positive}`}>-2 jours vs moyenne</span>
                </div>
            </div>

            {/* Geographic & Timeline Zone */}
            <div className={styles.chartsGrid}>
                {/* Timeline (Curve) */}
                <div className={styles.chartCard}>
                    <div className={styles.cardHeader}>
                        <h3>Volume de postulations ({timeframe === "week" ? "Hebdomadaire" : "Mensuel"})</h3>
                        <span className={styles.cardBadge}>Mise à jour en temps réel</span>
                    </div>
                    <div className={styles.lineChartWrapper}>
                        {applicationsOverTimeData && (
                            <LineChart
                                dataset={applicationsOverTimeData}
                                animate={{ duration: 1000, ease: "easeCubicOut" }}
                                axisSettings={{
                                    axisFormat: { x: "day" },
                                }}
                                tickSettings={{
                                    xTickVisibility: true,
                                    yTickVisibility: true,
                                }}
                                appTheme={{
                                    CURVE: {
                                        axes: {
                                            fill: "#94a3b8",
                                            stroke: "#334155",
                                        },
                                    },
                                }}
                            />
                        )}
                    </div>
                </div>

                {/* Donut Pipeline ATS + Légende */}
                <div className={styles.chartCard}>
                    <div className={styles.cardHeader}>
                        <h3>Répartition du Pipeline ATS</h3>
                    </div>
                    <div className={styles.donutContent}>
                        <DonutChart
                            data={candidateStatusData}
                            width={220}
                            height={220}
                            innerRadius={65}
                            outerRadius={95}
                            centerValue={jobKpisData.totalApplications}
                            centerLabel="Candidats"
                        />

                        {/* Légende (data) */}
                        <div className={styles.legend}>
                            {candidateStatusData.map((item) => (
                                <div key={item.label} className={styles.legendItem}>
                                    <span
                                        className={styles.legendColor}
                                        style={{ backgroundColor: item.color }}
                                    />
                                    <span className={styles.legendLabel}>{item.label}</span>
                                    <span className={styles.legendValue}>{item.value}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
};