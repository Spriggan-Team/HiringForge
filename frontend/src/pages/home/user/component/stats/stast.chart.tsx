
import { useTranslation } from "react-i18next";

//-- Custom Components
import SectionHeader from "../../../../../layout/components/sections/sectionHeader/section.header";
import MenuDrawer, { 
    MenuDrawerBody,
    MenuDrawerInput,
    MenuDrawerItem,
    MenuDrawerTrigger
} from "../../../../../layout/components/menu/drawer/menu.drawer";
import { 
    LineChart
} from "../../../../../layout/components/charts/lineChart/lineChart";

//-- CSS Styles
import styles from "./StatsChart.module.css"




const total = {
    candidates: 145,
    interviews: 126,
    hired: 45
}

interface StatsChartProps{}

const StatsChart: React.FC<StatsChartProps> = ({}) => {
    const {t} = useTranslation();
    
    return (
        <div className={styles.container}>
            <SectionHeader 
                title={t("userHome.statistics.title")}
                action={
                    <MenuDrawer
                        defaultValue={t("global.dates.thisWeek")}
                        onChange={(val) => console.log("selected:", val)}
                    >
                        <MenuDrawerTrigger
                            style={{ color: "#64748B"}}
                        >
                            {(selected) => selected ?? t("userHome.statistics.drawer.instrcution")}
                        </MenuDrawerTrigger>

                        <MenuDrawerBody>
                            <MenuDrawerItem value={t("global.dates.thisWeek")}>{t("global.dates.thisWeek")}</MenuDrawerItem>
                            <MenuDrawerItem value={t("global.dates.lastWeek")}>{t("global.dates.lastWeek")}</MenuDrawerItem>
                            <MenuDrawerInput
                                placeholder={ t("userHome.statistics.drawer.addOption")}
                                formatter={(v) => v.trim()}
                            />
                        </MenuDrawerBody>
                    </MenuDrawer>
                }
            />

            <div className={styles.legend}>
                <LegenItem
                    increase={12}
                    color="#3B82F6"
                    count={total.candidates}
                    title={t("global.candidate.candidateLabel", { count: total.candidates })}
                />
                
                <LegenItem
                    increase={1.2}
                    color="#8B5CF6"
                    count={total.interviews}
                    title={t("global.interview.interviewLabel", { count: total.interviews })}
                />
                
                <LegenItem
                    increase={8}
                    color="#22C55E"
                    count={total.candidates}
                    title={t("global.hired.hiredLabel", { count: total.hired })}
                />
            </div>

            {/** LineChart */}
            <div className={styles.linechartWrapper}>
                <LineChartComponent />
            </div>
        </div>
    );
}
 
export default StatsChart;


interface LegenItemProps{
    color: string;
    title: string;
    className?: string;
    increase: string | number;
    count: number;
}

const LegenItem: React.FC<LegenItemProps> = ({
    title,
    count,
    color,
    increase,
    className
}) => {
    return (
        <div 
            className={`${styles.legendItem} ${className}`}
        >
            <div className={styles.header}>
                <div  
                    style={{
                        ['--bgColor' as string]: color
                    }}
                    className={styles.circle}
                />
                <span className={styles.title}>{title}</span>
            </div>
            <div className={styles.main}>
                <span className={styles.count}>{count}</span>
                <span className={`${styles.increase} ${count < 0 ? styles.negative : ""}`}>+{increase}%</span>
            </div>
        </div>
    );
}
 



/** -- Linechart -- */

const startOfWeek = new Date("2026-06-23"); // exemple (lundi)

const getDate = (offset: number) => {
    const d = new Date(startOfWeek);
    d.setDate(startOfWeek.getDate() + offset);
    return d;
};

const statsDataMock = {
    dates: Array.from({ length: 7 }, (_, i) => getDate(i)),

    candidates: [124, 132, 145, 167, 180, 190, 205],
    interviews: [12, 18, 20, 25, 22, 28, 30],
    hired: [1, 2, 3, 3, 4, 5, 6],
};




const LineChartComponent = () => {
    const maxValue = Math.max(
        ...statsDataMock.candidates,
        ...statsDataMock.interviews,
        ...statsDataMock.hired
    );

    return (
        <div className={styles.linechart}>
            <LineChart
                className={styles.chart}
                margin={{
                    top: 20,
                    right: 15,
                    bottom: 15,
                    left: 6,
                }}
                axisSettings={{
                    axisVisibility: true,
                    xAxisVisibility: true,
                    yAxisVisibility: true
                }}
                tickSettings={{
                    tickVisibility: true,
                    xTickVisibility: true,
                    yTickVisibility: true,
                }}
                dataset={{
                    maximum: maxValue,

                    // ✅ Dates réelles
                    dates: statsDataMock.dates,

                    data: [
                        {
                            x: statsDataMock.candidates,
                            type: "line",
                            attr: {
                                stroke: "#3B82F6",
                                strokeWidth: 2.5,
                            },
                        },
                        {
                            x: statsDataMock.interviews,
                            type: "line",
                            attr: {
                                stroke: "#8B5CF6",
                                strokeWidth: 2.5,
                            },
                        },
                        {
                            x: statsDataMock.hired,
                            type: "line",
                            attr: {
                                stroke: "#22C55E",
                                strokeWidth: 2.5,
                            },
                        },
                    ],
                }}
            />
        </div>
    );
};