import type React from "react";
import { LineChart } from "../../../../../layout/components/charts/lineChart/lineChart";
import styles from "./styles.module.css";
import type { number } from "react-i18next/icu.macro";
import { useTranslation } from "react-i18next";


interface KpiCardPrps{
    className?: string;
    displayCurve?: boolean;
    iconBgColor?: string;
    
    increase?: number; //-- percent
    trendLabel?: string;

    title?:string;
    children?: React.ReactNode,
    svg?: React.FC<React.SVGProps<SVGSVGElement>>;
    svgStyle?: React.CSSProperties
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
    svg: Icon,
    increase,
    title,
    trendLabel,
    displayCurve = false,
    children = <></>,
    className,
    svgStyle,
    iconBgColor = "#0155fe6c",
}) => {
    const {t} = useTranslation()
    const maxValue = Math.max(...chartData);

    return (
        <article className={`${styles.container} ${className}`}>
            <div className={styles.content}>
                <div className={styles.header}>
                    <span className={styles.title}>
                        {title}
                    </span>
                    {
                        Icon && (
                            <div 
                                className={styles.iconContainer}
                                style={{
                                    ['--icon-bg-color' as string]: iconBgColor,
                                }}
                            >
                                <Icon style={svgStyle} width={25} height={25} />
                            </div>
                        )
                    }
                </div>

                <div className={styles.percent}>
                    { children }
                </div>
                {
                    increase && (
                        <p className={styles.desc}>
                            <span className={styles.badge}>+{increase}%</span>
                            <span>{trendLabel ?? t("global.dates.thisMonth")}</span>
                        </p>
                    )
                }
            </div>


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


/** Children */

export const KpiPercentage: React.FC<{percent: string}> = ({ percent })=>{
    return (
        <div className={styles.kpiPercentage}>
            <strong>{percent}</strong>
            <sup>%</sup>
        </div>
    )
}

export const KpiCount:  React.FC<{count: string}>  = ({count})=>{
    return (
        <strong className={styles.count}>{count}</strong>
    )
}