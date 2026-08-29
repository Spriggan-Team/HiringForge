import type { Dataset } from "../layout/components/charts/lineChart/lineChart";
import { currentDay, currentMonth } from "./dates";



/**
 * Configuration options used to build a chart dataset.
 *
 * Optional visual properties follow a three-state behavior:
 * - `undefined`: Uses the default/fallback configuration.
 * - `null`: Explicitly disables the feature.
 * - Object: Applies the provided custom configuration.
 */
interface buildChartDatasetProps{
    metrics: number[];
    timeframe: "week" | "month";
    gradient?: 
        |{
            stops?: { offset: string, stopColor: string, stopOpacity: number }[]
        }
        | null;
    dots?: 
        |{
            r: number,
            fill: string,
            stroke: string,
            strokeWidth: number,
            rPulse: number,
        }
        | null;
    color?: string;
}

/**
 * Builds a chart dataset from raw metric values.
 *
 * This method transforms a list of numerical metrics into the `Dataset`
 * structure expected by the LineChart component. It automatically selects
 * the appropriate date labels based on the requested timeframe and computes
 * a suitable maximum value for the chart scale.
 *
 * A fallback maximum value is applied when no metrics are available or when
 * all values are zero, ensuring the chart can still be rendered correctly.
 *
 * @param metrics - The numerical values to display on the chart.
 * @param timeframe - The timeframe used to determine the chart labels
 * (`week` or `month`).
 *
 * @returns A formatted Dataset ready to be consumed by the LineChart component.
 */
export class ViewModelFactory
{
    /**
    * Generates the Dataset object required by the charts (LineChart) without mutating global state
    */
    static buildChartDataset({
        metrics,
        timeframe,
        gradient,
        dots,
        color = "#3b82f6"
    }: buildChartDatasetProps): Dataset  {
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
                                        stop:
                                            gradient === null
                                                ? []
                                                : gradient?.stops ?? [
                                                    {
                                                        offset: "0%",
                                                        stopColor: color ,
                                                        stopOpacity: 0.4,
                                                    },
                                                    {
                                                        offset: "100%",
                                                        stopColor: color,
                                                        stopOpacity: 0.0,
                                                    },
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
                        stroke: color,
                        strokeWidth: 3,
                    },
                    dotIndicator:
                        dots === null
                            ? undefined
                            : dots ?? {
                                r: 5,
                                fill: color,
                                stroke: "#ffffff",
                                strokeWidth: 2,
                                rPulse: 7,
                            }
                },
            ],
        };
    };
}