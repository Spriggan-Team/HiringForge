import * as d3 from "d3";
import { useRef, useState, useEffect } from "react";
import styles from "./LineChart.module.css";

// -------------------------------------
// Types
//----------------------------------

export interface LineChartProps {
    onChartReady?: (props: OnChartReadyFuncProps) => void;
    margin?: Readonly<{
        top?: number;
        right?: number;
        bottom?: number;
        left?: number;
    }>;
    appTheme?: {
        CURVE: {
            axes?: {
                fill?: string;
                stroke?: string;
                strokeWidth?: number;
            };
            line?: {
                fill?: string;
            };
        };
    };
    dataset: Dataset;
    className?: string;

    tickSettings?: {
        tickVisibility?:  boolean;
        xTickVisibility?: boolean;
        yTickVisibility?: boolean;
    };

    axisSettings?: {
        /**
         * Controls how X-axis tick labels are formatted.
         *
         * - "month" → always show abbreviated month names  (Jan, Feb …)
         * - "day"   → always show abbreviated day names    (Mon, Tue …)
         * - "auto"  → pick based on the date range:
         *     • ≤ 14 days  → day names
         *     • otherwise  → month names
         */
        axisFormat?: {
            x?: "month" | "day" | "auto";
        };
        axisVisibility?:  boolean;
        xAxisVisibility?: boolean;
        yAxisVisibility?: boolean;
    };

    /**
     * Controls path animation from first point to last point.
     * Pass `true` for defaults (1000ms, easeCubicOut) or pass custom options.
     */
    animate?: boolean | { duration?: number; ease?: string };
}

export interface GraphDatum {
    coords:   Point[];
    settings: {
        backupColor?: string;
        type:          "line" | "area";
        areaMultiplier?: number;
        line?: Partial<React.SVGAttributes<SVGPathElement>>;
        area?: Partial<React.SVGAttributes<SVGPathElement>>;
    };
}

export interface CurveData {
    x:              number[];
    defs?: [{
        gradients?: {
            linear?: [{
                id:     string;
                target: string;
                stop:   { offset: string; stopColor: string; stopOpacity: number }[];
                coords: { x1: string; y1: string; x2: string; y2: string };
            }];
        };
    }];
    dotIndicator?: {
        r?:           number;
        fill?:        string;
        class?:       string;
        cursor?:      string;
        stroke?:      number;
        rPulse?:      number;
        strokeWidth?: number;
    };
    areaMultiplier?: number;
    attr?:           Partial<React.SVGAttributes<SVGPathElement>>;
    type?:           "line" | "area";
}

export interface Dataset {
    dates:   Date[];
    data:    CurveData[];
    maximum: number;
}

interface Point {
    x: Date;
    y: number;
}

export interface OnChartReadyFuncProps {
    divContainer: HTMLDivElement;
    svg:          d3.Selection<SVGSVGElement, unknown, null, undefined>;
    chartsGridX:  d3.Selection<SVGGElement,   unknown, null, undefined>;
    yAxisGroup:   d3.Selection<SVGGElement,   unknown, null, undefined>;
    xAxisGroup:   d3.Selection<SVGGElement,   unknown, null, undefined>;
}

// --------------------
// Helpers
// --------------------------


/**
 * Decide tick format based on the date range and the axisFormat prop.
 *
 * "auto" rule:
 *   - span ≤ 14 days  → one tick per day,  label = "Mon", "Tue" …
 *   - span  > 14 days → one tick per month, label = "Jan", "Feb" …
 */
function resolveXFormat(
    dates:      Date[],
    axisFormat?: "month" | "day" | "auto",
): { mode: "month" | "day" } {
    const mode = axisFormat ?? "auto";
    if (mode === "month") return { mode: "month" };
    if (mode === "day")   return { mode: "day" };

    // auto
    const spanMs   = dates[dates.length - 1].getTime() - dates[0].getTime();
    const spanDays = spanMs / (1000 * 60 * 60 * 24);
    return { mode: spanDays <= 14 ? "day" : "month" };
}


/**
 * Build the array of tick Date values for the X axis.
 *
 * - mode "month" → first day of each month present in the range
 * - mode "day"   → one entry per date in the dataset
 */
function buildXTicks(dates: Date[], mode: "month" | "day"): Date[] {
    if (mode === "day") return dates.map(d => new Date(d));

    // month mode
    const ticks: Date[] = [];
    const dateMin = dates[0];
    const dateMax = dates[dates.length - 1];
    const cursor  = new Date(dateMin.getFullYear(), dateMin.getMonth(), 1);
    const end     = new Date(dateMax.getFullYear(), dateMax.getMonth() + 1, 1);

    while (cursor < end) {
        ticks.push(new Date(cursor));
        cursor.setMonth(cursor.getMonth() + 1);
    }
    return ticks;
}


/** Format a tick Date for display. */
function buildXFormatter(mode: "month" | "day"): (d: Date | d3.NumberValue) => string {
    if (mode === "month") return d => d3.timeFormat("%b")(d as Date);   // "Jan"
    return d => d3.timeFormat("%a")(d as Date);                          // "Mon"
}



// -------------------------------
// Component
// ---------------------

export const LineChart = ({
    dataset,
    margin,
    appTheme,
    tickSettings,
    axisSettings,
    animate,
    onChartReady,
    className,
}: LineChartProps) => {
    const divContainer = useRef<HTMLDivElement>(null);
    const [dimensions, setDimensions] = useState({ width: 0, height: 0 });

    // ----------------- Responsive sizing -----------------
    useEffect(() => {
        if (!divContainer.current) return;
        const ro = new ResizeObserver(entries => {
            if (!entries.length) return;
            const { width, height } = entries[0].contentRect;
            if (width > 0 && height > 0) setDimensions({ width, height });
        });
        ro.observe(divContainer.current);
        return () => ro.disconnect();
    }, []);


    // ------------ Draw ----------
    useEffect(() => {
        if (!divContainer.current || dimensions.width === 0 || dimensions.height === 0) return;
        if (!dataset.dates?.length || !dataset.data?.length) return;

        const { width, height } = dimensions;

        // Resolved margins — always numeric
        const mg = {
            top:    margin?.top    ?? 20,
            right:  margin?.right  ?? 15,
            bottom: margin?.bottom ?? 30,   // extra room for x-axis labels
            left:   margin?.left   ?? 40,   // extra room for y-axis labels
        };

        // Theme defaults — use dark-friendly colours
        const axesFill        = appTheme?.CURVE?.axes?.fill        ?? "#94a3b8";
        const axesStroke      = appTheme?.CURVE?.axes?.stroke      ?? "#475569";
        const axesStrokeWidth = appTheme?.CURVE?.axes?.strokeWidth ?? 1;

        // Animation config
        const isAnimated = Boolean(animate);
        const animDuration = typeof animate === "object" ? animate.duration ?? 1000 : 1000;
        const animEaseStr = typeof animate === "object" ? animate.ease ?? "easeCubicOut" : "easeCubicOut";
        const animEase = d3[animEaseStr as keyof typeof d3] as (normalizedTime: number) => number || d3.easeCubicOut;

        // ----- Clear previous render ---------------
        d3.select(divContainer.current).selectAll("*").remove();

        // ------ SVG --------------
        const svg = d3
            .select(divContainer.current)
            .append("svg")
            .attr("width",   width)
            .attr("height",  height)
            .attr("viewBox", `0 0 ${width} ${height}`)
            .style("display",  "block")
            .style("overflow", "visible");

        // ----- Scales ---------
        const sortedDates = [...dataset.dates].sort((a, b) => +a - +b);
        const [dateMin, dateMax] = d3.extent(sortedDates) as [Date, Date];

        // Small padding so the first/last points aren't clipped
        const spanMs      = dateMax.getTime() - dateMin.getTime();
        const padMs       = spanMs / (Math.max(sortedDates.length, 2) * 2);
        const xDomainStart = new Date(dateMin.getTime() - padMs);
        const xDomainEnd   = new Date(dateMax.getTime() + padMs);

        const xScale = d3
            .scaleTime()
            .domain([xDomainStart, xDomainEnd])
            .range([mg.left, width - mg.right]);

        const yScale = d3
            .scaleLinear()
            .domain([0, dataset.maximum || 100])
            .nice()
            .range([height - mg.bottom, mg.top]);

        // ---- Y Axis -----
        const showYAxis  = axisSettings?.axisVisibility !== false && axisSettings?.yAxisVisibility !== false;
        const showYTicks = tickSettings?.tickVisibility  !== false && tickSettings?.yTickVisibility  !== false;

        const yAxis = d3
            .axisLeft(yScale)
            .ticks(5)
            .tickSizeOuter(0)
            .tickSizeInner(showYTicks ? -(width - mg.left - mg.right) : 0)  // full-width grid lines
            .tickFormat(d => d3.format("~s")(d as number));

        const yAxisGroup = svg
            .append("g")
            .attr("class", "y-axis")
            .attr("transform", `translate(${mg.left}, 0)`)
            .call(yAxis);

        // Remove the vertical domain line
        yAxisGroup.select(".domain").remove();

        // Style grid tick lines
        yAxisGroup.selectAll<SVGLineElement, unknown>(".tick line")
            .attr("stroke",           axesStroke)
            .attr("stroke-width",     axesStrokeWidth)
            .attr("stroke-dasharray", "4 4")
            .attr("opacity",          showYTicks ? 0.45 : 0);

        // Style tick labels
        yAxisGroup.selectAll<SVGTextElement, unknown>(".tick text")
            .attr("fill",         axesFill)
            .attr("x",            -8)
            .attr("dy",           "0.32em")
            .attr("text-anchor",  "end")
            .style("font-size",   "11px")
            .style("display",  () => showYTicks  ? null : "none");

        // ---- X Axis ---------------------
        const showXAxis  = axisSettings?.axisVisibility !== false && axisSettings?.xAxisVisibility !== false;
        const showXTicks = tickSettings?.tickVisibility  !== false && tickSettings?.xTickVisibility  !== false;

        const { mode }    = resolveXFormat(sortedDates, axisSettings?.axisFormat?.x);
        const tickValues  = buildXTicks(sortedDates, mode);
        const tickFormat  = buildXFormatter(mode);

        const xAxis = d3
            .axisBottom(xScale)
            .tickValues(tickValues)
            .tickFormat(tickFormat)
            .tickSizeOuter(0)
            .tickSizeInner(6);

        const chartsGridX = svg.append("g").attr("class", "charts-grid");

        const xAxisGroup = chartsGridX
            .append("g")
            .attr("class", "x-axis")
            .attr("transform", `translate(0, ${height - mg.bottom})`)
            .call(xAxis);

        // Style the horizontal domain line
        xAxisGroup.select(".domain")
            .attr("stroke",       showXAxis ? axesStroke : "none")
            .attr("stroke-width", axesStrokeWidth);

        // Style tick marks
        xAxisGroup.selectAll<SVGLineElement, unknown>(".tick line")
            .attr("stroke",       showXTicks ? axesStroke : "none")
            .attr("stroke-width", axesStrokeWidth);

            
        // Style tick labels
        xAxisGroup.selectAll<SVGTextElement, unknown>(".tick text")
            .attr("fill",       axesFill)
            .attr("dy",         "1em")
            .style("font-size", "11px")
            .style("display",   () => showXTicks ? null : "none");


        // ------- Data preparation ----------------------
        const graphData: GraphDatum[] = dataset.data.map(mark => {
            const type = mark.type ?? "line";
            const settings: GraphDatum["settings"] = {
                type,
                areaMultiplier: mark.areaMultiplier,
                line: type === "line" ? { ...(mark.attr ?? {}) } : undefined,
                area: type === "area" ? { ...(mark.attr ?? {}) } : undefined,
            };

            if (mark.defs) {
                mark.defs.forEach(defs => {
                    const dfsEl = svg.append("defs");
                    defs.gradients?.linear?.forEach(ln => {
                        const grad = dfsEl
                            .append("linearGradient")
                            .attr("id", ln.id)
                            .attr("x1", ln.coords.x1).attr("y1", ln.coords.y1)
                            .attr("x2", ln.coords.x2).attr("y2", ln.coords.y2);

                        ln.stop.forEach(stp =>
                            grad.append("stop")
                                .attr("offset",       stp.offset)
                                .attr("stop-color",   stp.stopColor)
                                .attr("stop-opacity", stp.stopOpacity)
                        );

                        if (type === "area") settings.area = { ...settings.area, fill:   `url(#${ln.id})` };
                        if (type === "line") settings.line = { ...settings.line, stroke: `url(#${ln.id})` };
                    });
                });
            }

            const coords: Point[] = dataset.dates.map((d, idx) => ({
                x: d,
                y: mark.x[idx] ?? 0,
            }));

            return { coords, settings };
        });

        // ------ Draw curves -------------------
        const curveFn = d3.curveCatmullRom.alpha(0.5);

        graphData.forEach((gd, i) => {
            const dot = dataset.data[i].dotIndicator;

            if (gd.settings.type === "area") {
                const mul = gd.settings.areaMultiplier ?? 1;
                
                const areaPath = chartsGridX
                    .append("path")
                    .datum(gd.coords)
                    .attr("fill",         (gd.settings.area?.fill   as string) ?? "transparent")
                    .attr("stroke",       (gd.settings.area?.stroke as string) ?? "none")
                    .attr("stroke-width", (gd.settings.area?.strokeWidth as number) ?? 0)
                    .attr("d",
                        d3.area<Point>()
                            .x(d => xScale(d.x))
                            .y0(yScale(0))
                            .y1(d => yScale(d.y * mul))
                            .curve(curveFn)
                    );

                // Animate area by using a reveal clip-path mask from left to right
                if (isAnimated) {
                    const clipId = `area-clip-${i}-${Math.random().toString(36).substr(2, 9)}`;
                    const clipRect = svg.append("defs")
                        .append("clipPath")
                        .attr("id", clipId)
                        .append("rect")
                        .attr("x", mg.left)
                        .attr("y", 0)
                        .attr("width", 0)
                        .attr("height", height);

                    areaPath.attr("clip-path", `url(#${clipId})`);

                    clipRect
                        .transition()
                        .duration(animDuration)
                        .ease(animEase)
                        .attr("width", width - mg.left);
                }
            }

            if (gd.settings.type === "line") {
                // Check if a matching area series exists to inherit its multiplier
                const areaMatch = graphData.find(
                    g => g.settings.type === "area" &&
                         g.coords.length === gd.coords.length &&
                         g.coords.every((p, idx) => +p.x === +gd.coords[idx].x)
                );
                const mul = areaMatch?.settings?.areaMultiplier ?? 1;
                const transformed = gd.coords.map(d => ({ ...d, y: d.y * mul }));

                const linePath = chartsGridX
                    .append("path")
                    .datum(transformed)
                    .attr("fill",         "none")
                    .attr("stroke",
                        (gd.settings.line?.stroke as string) ||
                        (gd.settings.line?.fill   as string) ||
                        "#3b82f6"
                    )
                    .attr("stroke-width", (gd.settings.line?.strokeWidth as number) ?? 2)
                    .attr("stroke-linejoin", "round")
                    .attr("stroke-linecap",  "round")
                    .attr("d",
                        d3.line<Point>()
                            .x(d => xScale(d.x))
                            .y(d => yScale(d.y))
                            .curve(curveFn)
                    );

                // Animate path line using stroke-dashoffset trick
                if (isAnimated) {
                    const totalLength = (linePath.node() as SVGPathElement).getTotalLength();
                    linePath
                        .attr("stroke-dasharray", `${totalLength} ${totalLength}`)
                        .attr("stroke-dashoffset", totalLength)
                        .transition()
                        .duration(animDuration)
                        .ease(animEase)
                        .attr("stroke-dashoffset", 0);
                }
            }

            if (dot) {
                const dotsSelection = chartsGridX
                    .selectAll(`.dot-${i}`)
                    .data(gd.coords)
                    .enter()
                    .append("circle")
                    .attr("class",        `dot-${i}`)
                    .attr("cx",           d => xScale(d.x))
                    .attr("cy",           d => yScale(d.y))
                    .attr("r",            dot.r            ?? 5)
                    .attr("fill",         dot.fill         ?? "#fff")
                    .attr("stroke",       dot.stroke       ?? axesStroke)
                    .attr("stroke-width", dot.strokeWidth  ?? 1.5)
                    .style("cursor",      dot.cursor       ?? "pointer")
                    .on("mouseover", function () {
                        d3.select(this).transition().duration(120).attr("r", dot.rPulse ?? 8);
                    })
                    .on("mouseout", function () {
                        d3.select(this).transition().duration(120).attr("r", dot.r ?? 5);
                    });

                // Fade/scale in dots sequentially as the line reaches them
                if (isAnimated) {
                    const targetR = dot.r ?? 5;
                    const minX = xScale(xDomainStart);
                    const maxX = xScale(xDomainEnd);
                    const totalXDist = maxX - minX;

                    dotsSelection
                        .attr("r", 0)
                        .style("opacity", 0)
                        .transition()
                        .delay(d => {
                            const pointX = xScale(d.x);
                            const progress = Math.max(0, Math.min(1, (pointX - minX) / totalXDist));
                            return progress * animDuration * 0.85; // slight offset for smooth feeling
                        })
                        .duration(200)
                        .ease(d3.easeBackOut)
                        .attr("r", targetR)
                        .style("opacity", 1);
                }
            }
        });

        // ----- Callback ---------
        onChartReady?.({ svg, chartsGridX, divContainer: divContainer.current!, xAxisGroup, yAxisGroup });

    }, [dimensions, appTheme, margin, dataset, tickSettings, axisSettings, animate, onChartReady]);

    return (
        <div
            ref={divContainer}
            className={`${styles.container} ${className ?? ""}`}
            style={{ width: "100%", height: "100%" }}
        />
    );
};