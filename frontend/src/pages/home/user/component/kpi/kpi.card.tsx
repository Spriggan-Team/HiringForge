import type React from "react";
import { LineChart } from "../../../../../layout/components/charts/lineChart/lineChart";
import styles from "./styles.module.css";


interface KpiCardPrps{
    displayCurve?: boolean;
    iconBgColor?: string;
    children?: React.ReactNode,
    svg?: React.FC<React.SVGProps<SVGSVGElement>>;
}

const mockMonthlyViews = [
    { month: "2025-07-01", count: 124 },
    { month: "2025-08-01", count: 152 },
    { month: "2025-09-01", count: 141 },
    { month: "2025-10-01", count: 178 },
    { month: "2025-11-01", count: 167 },
    { month: "2025-12-01", count: 214 },
    { month: "2026-01-01", count: 201 },
    { month: "2026-02-01", count: 246 },
    { month: "2026-03-01", count: 232 },
    { month: "2026-04-01", count: 289 },
    { month: "2026-05-01", count: 271 },
    { month: "2026-06-01", count: 347 }
];


const chartData = mockMonthlyViews.map(({ count }) => count);
const chartDates = mockMonthlyViews.map(({ month }) => new Date(month));



const KpiCard: React.FC<KpiCardPrps> = ({ 
    displayCurve = false,
    svg: Icon,
    children = <></>,
    iconBgColor = "#0154FE",
}) => {
    const maxValue = Math.max(...chartData);

    return (
        <article className={styles.container}>
            <div className={styles.content}>
                <span className={styles.title}>
                    Taux de postulation
                </span>

                <div className={styles.percent}>
                    { children }
                </div>

                <p className={styles.desc}>
                    <span className={styles.badge}>+12%</span>
                    ce mois
                </p>
            </div>
            {
                Icon && (
                    <div 
                        className={styles.iconContainer}
                        style={{
                            ['--icon-bg-color' as string]: iconBgColor,
                        }}
                    >
                        <Icon width={35} height={35} />
                    </div>
                )
            }

            {displayCurve && (
                <div className={styles.chartWrapper}>
                    <LineChart
                        className={styles.chart}
                        margin={{
                            top: 5,
                            right: 12,
                            bottom: 4,
                            left: 4
                        }}
                        axisSettings={{
                            axisVisibility: false
                        }}
                        tickSettings={{
                            tickVisibility: false
                        }}
                        dataset={{
                            maximum: maxValue,
                            data: [
                                {
                                    x: chartData,
                                    type: "line",
                                    attr: {
                                        stroke: "#10b981",
                                        strokeWidth: 2.75
                                    }
                                },
                                {
                                    x: chartData,
                                    type: "area",
                                    defs: [
                                        { 
                                            gradients: { 
                                                linear: [{ 
                                                    id: "green",
                                                    stop: [
                                                        { offset: "0%", stopColor: "limegreen", stopOpacity: 0.5 },
                                                        { offset: "100%", stopColor: "limegreen", stopOpacity: 0 },
                                                    ],
                                                    target: "fill",
                                                    coords: { x1: "0%", y1: "0%", x2: "0%", y2: "100%" },
                                                }]
                                            }
                                        }
                                    ]
                                }
                            ],
                            dates: chartDates
                        }}
                    />
                </div>
            )}
        </article>
    );
};


export default KpiCard;


export const KpiPercentage: React.FC<{percent: string}> = ({ percent })=>{
    return (
        <>
            <strong>{percent}</strong>
            <sup>%</sup>
        </>
    )
}