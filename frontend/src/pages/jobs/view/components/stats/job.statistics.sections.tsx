import React, { useState } from "react";
import styles from "./JobStatisticsSection.module.css";


import { LineChart, type Dataset } from "../../../../../layout/components/charts/lineChart/lineChart";
import type { DonutChartData } from "../../../../../layout/components/charts/donutChart/donus.chart";
import DonutChart from "../../../../../layout/components/charts/donutChart/donus.chart";



// postulation by weeks
const applicationsOverTimeData: Dataset = {
    dates: [
        new Date("2026-07-01"),
        new Date("2026-07-08"),
        new Date("2026-07-15"),
        new Date("2026-07-22"),
        new Date("2026-07-29"),
        new Date("2026-08-04"),
    ],
    maximum: 120,
    data: [
        {
            type: "area",
            areaMultiplier: 1,
            x: [15, 45, 85, 110, 70, 40],
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
            x: [15, 45, 85, 110, 70, 40],
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
// Breakdown of candidates based on the structure of your DonutChartData
const candidateStatusData: DonutChartData[] = [
    { label: "Présélectionnés", value: 140, color: "#3b82f6" },
    { label: "En Entretien",    value: 45,  color: "#8b5cf6" },
    { label: "Offres Générées", value: 12,  color: "#10b981" },
    { label: "Rejetés",         value: 168, color: "#ef4444" },
];

export interface JobStatisticsSectionProps {
    jobTitle?: string;
    totalApplications?: number;
    rejectionRate?: number;
    offersGenerated?: number;
    avgTimeToHireDays?: number;
}




export const JobStatisticsSection: React.FC<JobStatisticsSectionProps> = ({
    jobTitle = "Développeur Fullstack Senior",
    totalApplications = 365,
    rejectionRate = 46,
    offersGenerated = 12,
    avgTimeToHireDays = 18,
}) => {
    const [timeframe, setTimeframe] = useState<"week" | "month">("week");

    return (
        <section className={styles.container}>
            {/* Header */}
            <div className={styles.header}>
                <div>
                    <h2 className={styles.title}>Statistiques de recrutement</h2>
                    <p className={styles.subtitle}>
                        Offre : <strong>{jobTitle}</strong>
                    </p>
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
                    <span className={styles.kpiValue}>{totalApplications}</span>
                    <span className={`${styles.kpiBadge} ${styles.positive}`}>+14% cette semaine</span>
                </div>
                <div className={styles.kpiCard}>
                    <span className={styles.kpiLabel}>Offres Générées</span>
                    <span className={styles.kpiValue}>{offersGenerated}</span>
                    <span className={styles.kpiSubtext}>3 acceptées</span>
                </div>
                <div className={styles.kpiCard}>
                    <span className={styles.kpiLabel}>Taux de Rejet</span>
                    <span className={styles.kpiValue}>{rejectionRate}%</span>
                    <span className={styles.kpiSubtext}>168 candidats écartés</span>
                </div>
                <div className={styles.kpiCard}>
                    <span className={styles.kpiLabel}>Temps Moyen d'Embauche</span>
                    <span className={styles.kpiValue}>{avgTimeToHireDays} jours</span>
                    <span className={`${styles.kpiBadge} ${styles.positive}`}>-2 jours vs moyenne</span>
                </div>
            </div>

            {/* Geographic Zone */}
            <div className={styles.chartsGrid}>
                {/* Timeline (Curve) */}
                <div className={styles.chartCard}>
                    <div className={styles.cardHeader}>
                        <h3>Volume de postulations ({timeframe === "week" ? "Hebdomadaire" : "Mensuel"})</h3>
                        <span className={styles.cardBadge}>Mise à jour en temps réel</span>
                    </div>
                    <div className={styles.lineChartWrapper}>
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
                            centerValue={totalApplications}
                            centerLabel="Candidats"
                        />

                        {/* Légende liée aux données */}
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