import type { CurveData, Dataset } from "../layout/components/charts/lineChart/lineChart";
import { currentDays, currentMonth } from "./dates";




const DEFAULT_PALETTE = ["#3b82f6", "#10b981", "#f59e0b", "#ef4444", "#8b5cf6"];



export interface SeriesStyleConfig {
  color?: string;
  gradient?: { stops?: { offset: string; stopColor: string; stopOpacity: number }[] } | null;
  dots?: { r: number; fill: string; stroke: string; strokeWidth: number; rPulse: number } | null;
}


/**
 * Configuration options used to build a chart dataset.
 *
 * Optional visual properties follow a three-state behavior:
 * - `undefined`: Uses the default/fallback configuration.
 * - `null`: Explicitly disables the feature.
 * - Object: Applies the provided custom configuration.
 */
export interface BuildChartDatasetProps {
  metrics: number[][];
  timeframe: "week" | "month";
  /** Default palette colors */
  palette?: string[];
  /** Default style apply to all graph series */
  defaultStyle?: SeriesStyleConfig;
  /** Specific/Custom style for each series */
  seriesOptions?: (SeriesStyleConfig | null)[];
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
        palette = DEFAULT_PALETTE,
        defaultStyle,
        seriesOptions = [],
    }: BuildChartDatasetProps): Dataset  {
        const dates = timeframe === "month" ? currentMonth : currentDays;
        const allValues = metrics.flat();
        const maxVal = allValues.length > 0 ? Math.max(...allValues) : 10;

        
        const chartData: CurveData[] = []; 

        for (let i = 0; i < metrics.length; i++) {
            const values = metrics[i];
            const seriesOverride = seriesOptions[i];

            const { baseColor, dots, gradientStops } = resolveSeriesStyle(
                i,
                defaultStyle,
                seriesOverride,
                palette
            );

            const gradientId = `gradient-${i}-${baseColor.replace("#", "")}`;

            //-- line
            chartData.push({
                type: "line",
                x: values,
                attr: { stroke: baseColor, strokeWidth: 3 },
                dotIndicator: dots ?? undefined,
            });

            //-- Area charts
            chartData.push({
                type: "area",
                areaMultiplier: 1,
                x: values,
                attr: { fill: `url(#${gradientId})` },
                defs: [
                {
                    gradients: {
                    linear: [
                        {
                        id: gradientId,
                        target: "fill",
                        coords: { x1: "0%", y1: "0%", x2: "0%", y2: "100%" },
                        stop: gradientStops ?? [],
                        },
                    ],
                    },
                },
                ],
            });

        }


        return {
            dates,
            maximum: maxVal > 0 ? maxVal : 10,
            data: chartData,
        };
    };
}


//------------
//-- Helpers
//------------------



function resolveSeriesStyle(
  index: number,
  globalDefault?: SeriesStyleConfig,
  seriesOverride?: SeriesStyleConfig | null,
  palette: string[] = DEFAULT_PALETTE
) {
  // Color : Override  -> Global -> Default palette
  const baseColor = seriesOverride?.color ?? globalDefault?.color ?? palette[index % palette.length];

  // Dots : State (null = deactivate, Objet = custom, undefined = fallback)
  const dots = seriesOverride?.dots !== undefined 
    ? seriesOverride.dots 
    : globalDefault?.dots !== undefined 
      ? globalDefault.dots 
      : {
          r: 5,
          fill: baseColor,
          stroke: "#ffffff",
          strokeWidth: 2,
          rPulse: 7,
        };

  // Gradient : State (null = deactivate, Objet = custom, undefined = fallback)
  const gradientStops = seriesOverride?.gradient !== undefined
    ? seriesOverride.gradient?.stops
    : globalDefault?.gradient !== undefined
      ? globalDefault.gradient?.stops
      : [
          { offset: "0%", stopColor: baseColor, stopOpacity: 0.4 },
          { offset: "100%", stopColor: baseColor, stopOpacity: 0.0 },
        ];

  return { baseColor, dots, gradientStops };
}



/** Line chart default params */
export const buildLineChartDefaultParams = (timeframe: "week" | "month") => {
    const axisFormat = { x: (timeframe === "week" ? "day" : "month") as "day" | "month" | "auto" };
    return ({
        animate: { duration: 1000, ease: "easeCubicOut" },
        axisSettings:{
            axisFormat: axisFormat,
        },
        tickSettings:{
            xTickVisibility: true,
            yTickVisibility: true,
        },
        appTheme: {
            CURVE: {
                axes: {
                    fill: "#94a3b8",
                    stroke: "#334155",
                },
            },
        }
    })
}
