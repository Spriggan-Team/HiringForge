
import { useTranslation } from "react-i18next";

//-- Custom Components
import SectionHeader from "../../../../../layout/components/sections/sectionHeader/section.header";
import MenuDrawer, { 
    MenuDrawerBody,
    MenuDrawerInput,
    MenuDrawerItem,
    MenuDrawerTrigger
} from "../../../../../layout/components/menu/drawer/menu.drawer";

//-- CSS Styles
import styles from "./StatsChart.module.css"
import { LineChart } from "../../../../../layout/components/charts/lineChart/lineChart";


const total = {
    candidates: 145,
    interviews: 126,
    hired: 45
}

const StatsDataMock = {};

const StatsChart = () => {
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

            <div className={styles.linechart}>
                <LineChart
                    
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
 