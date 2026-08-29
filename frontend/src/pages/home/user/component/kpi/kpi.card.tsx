import type React from "react";
import { LineChart, type Dataset } from "../../../../../layout/components/charts/lineChart/lineChart";
import styles from "./styles.module.css";
import type { number } from "react-i18next/icu.macro";
import { useTranslation } from "react-i18next";


interface KpiCardPrps{
    className?: string;
    displayCurve?: boolean;
    iconBgColor?: string;
    
    increase?: number | null; //-- percent; null for deactivate percent badge display
    trendLabel?: string;

    title?:string;
    children?: React.ReactNode;
    dataset?: Dataset | null; //line chart

    svg?: React.FC<React.SVGProps<SVGSVGElement>>;
    svgStyle?: React.CSSProperties
}




const KpiCard: React.FC<KpiCardPrps> = ({ 
    svg: Icon,
    increase,
    title,
    trendLabel,
    displayCurve = false,
    children = <></>,
    className,
    svgStyle,
    dataset,
    iconBgColor = "#0155fe6c",
}) => {
    const {t} = useTranslation()

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
                increase != null && (
                    <p className={styles.desc}>
                        <span className={styles.badge}>+{increase}%</span>
                        <span>{trendLabel ?? t("global.dates.thisMonth")}</span>
                    </p>
                )
               }
            </div>


            {displayCurve && dataset && (
                <div className={styles.chartWrapper}>
                    <LineChart
                        animate={{ duration: 1000, ease: "easeCubicOut" }}
                        className={styles.chart}
                        dataset={dataset}
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
                    />
                </div>
            )}
        </article>
    );
};


export default KpiCard;


/** Children */

export const KpiPercentage: React.FC<{percent?: string | number}> = ({ percent })=>{
    return (
        <div className={styles.kpiPercentage}>
            <strong>{percent ?? 0}</strong>
            <sup>%</sup>
        </div>
    )
}

export const KpiCount:  React.FC<{count?: string | number | null }>  = ({count})=>{
    return (
        <strong className={styles.count}>{count ?? 0}</strong>
    )
}