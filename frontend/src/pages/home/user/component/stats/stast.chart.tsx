
import { useTranslation } from "react-i18next";
import { useCallback, useEffect, useRef, useState } from "react";

//--Services
import StatsQueries from "../../../../../api/services/stats/queries";
import { buildLineChartDefaultParams, ViewModelFactory } from "../../../../../utils/view.model.factory";
import { LineChartPlaceholder } from "../../../../../layout/components/cards/placeholder.php/linechart.placeholder";
import { formatDateInputValue, getTimeframeCacheKey, parseFrenchDate, shiftDate } from "../../../../../utils/dates";

//-- Custom Components
import SectionHeader from "../../../../../layout/components/sections/sectionHeader/section.header";
import MenuDrawer, { 
    MenuDrawerBody,
    MenuDrawerInput,
    MenuDrawerItem,
    MenuDrawerTrigger
} from "../../../../../layout/components/menu/dropdown/menu.dropdown";
import { 
    type Dataset,
    LineChart
} from "../../../../../layout/components/charts/lineChart/lineChart";

//-- CSS Styles
import styles from "./StatsChart.module.css"




interface RecruitmentChartData {
    applications: number[];
    interviews: number[];
    hires: number[];
                
    applicationCount: number;
    interviewsCount: number;
    hiredCandidateCount : number;
}

const colorScheme = {
    candidates: "#3B82F6",
    interviews: "#8B5CF6",
    hired: "#22C55E"
}

interface StatsChartProps{}

const timeframe = "week";
const today = new Date();

const StatsChart: React.FC<StatsChartProps> = ({}) => {
    const {t} = useTranslation();

    const [value, setValue] = useState<string>('');
    const cacheRef = useRef<Record<string, RecruitmentChartData>>({})

    const [chartDataset, setChartDataset] = useState<Dataset | null>(null);
    const [chartLegend, setChardLegend] = useState<{
        applicationCount: number,
        interviewsCount: number,
        hiredCandidateCount: number
    } | null>(null);

    const loadChartsData = useCallback(async (pick: Date)=>{
            try{
                const cache = cacheRef.current;

                const key = getTimeframeCacheKey(
                    pick,
                    timeframe
                );

                let data = cache[key];
                if (!data) {
                    data = await StatsQueries.getRecruitmentStatistics({
                        timeframe,
                        pick
                    });

                    cache[key] = data;
                } 
            
                const {
                    applications,
                    interviews,
                    hires,
                    applicationCount = 0,
                    interviewsCount = 0,
                    hiredCandidateCount = 0
                } = data;

                const metrics: number[][] = Object.values({
                    applications,
                    interviews,
                    hires
                });

                const dataset = ViewModelFactory.buildChartDataset({
                    metrics: metrics,
                    timeframe: timeframe,
                    seriesOptions: [
                        { color: colorScheme.candidates },
                        { color: colorScheme.interviews },
                        { color: colorScheme.hired }
                    ]
                });
                
                setChartDataset(dataset);
                setChardLegend(()=>({  applicationCount, interviewsCount,  hiredCandidateCount  }));
                
            }
            catch(error){
                console.log("Something went wrong while retreiving recruitement chart data")
            }
    },[])

    //-- Fetch data
    useEffect(()=>{
        loadChartsData(new Date());
    },[])


    return (
        <div className={styles.container}>
            <SectionHeader 
                title={t("userHome.statistics.title")}
                action={
                    <MenuDrawer
                        defaultValue={t("global.dates.thisWeek")}
                        onChange={(value) => {
                            if(typeof value == "string" && value.includes("/")){
                                const date = parseFrenchDate(value);
                                if(date){
                                    loadChartsData(date);
                                }
                            }
                            else if(value == t("global.dates.thisWeek"))
                                loadChartsData(today);
                            else 
                                loadChartsData(shiftDate(new Date(), -1, 'week'))
                        }}
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
                                value={value}
                                onChange={(e) => formatDateInputValue(e, setValue)}
                                placeholder={`${t('global.dates.day')}/${t('global.dates.month')}/${t('global.dates.years')}`}
                                formatter={(v) => v.trim()}
                            />
                        </MenuDrawerBody>
                    </MenuDrawer>
                }
            />

            <div className={styles.legend}>
                <LegenItem
                    count={chartLegend?.applicationCount ?? 0}
                    color={colorScheme.candidates}
                    title={t("global.candidate.candidateLabel", { count: chartLegend?.applicationCount ?? 0 })}
                />
                
                <LegenItem
                    count={chartLegend?.interviewsCount ?? 0}
                    color={colorScheme.interviews}
                    title={t("global.interview.interviewLabel", { count: chartLegend?.interviewsCount ?? 0 })}
                />
                
                <LegenItem
                    count={chartLegend?.hiredCandidateCount ?? 0}
                    color={colorScheme.hired}
                    title={t("global.hired.hiredLabel", { count: chartLegend?.hiredCandidateCount ?? 0 })}
                />
            </div>

            {/** LineChart */}
            <div className={styles.linechartWrapper}>
                <LineChartComponent 
                    chartDataset={chartDataset}
                    {...buildLineChartDefaultParams(timeframe)}
                />
            </div>
        </div>
    );
}
 
export default StatsChart;



interface LegenItemProps{
    color: string;
    title: string;
    className?: string;
    increase?: string | number;
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
                { increase && (<span className={`${styles.increase} ${count < 0 ? styles.negative : ""}`}>+{increase}%</span>)}
            </div>
        </div>
    );
}
 



/** -- Linechart -- */

interface LineChartComponentProps{
    chartDataset: Dataset | null
}

const LineChartComponent: React.FC<LineChartComponentProps> = ({chartDataset}) => {
    return (
        <div className={styles.linechart}>
            {
                chartDataset ?
                    (
                        <LineChart
                            className={styles.chart}
                            dataset={chartDataset}
                        />
                    )
                    : <LineChartPlaceholder />
            }
        </div>
    );
};

