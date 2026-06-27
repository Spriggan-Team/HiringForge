import * as d3 from "d3";
import { useRef, useState, useEffect } from "react";
import styles from "./LineChart.module.css";



export interface LineChartPops {
    onChartReady?: ({
        svg,
        xAxisGroup,
        yAxisGroup,
        chartsGridX,
        divContainer,
    }: onChartReadyFuncProps) => void;
    margin?: Readonly<{
        top?: number; right?: number; bottom?: number; left?: number;
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
        tickVisibility?: boolean;
        xTickVisibility?: boolean;
        yTickVisibility?: boolean;
    };
    axisSettings?: {
        axisVisibility?: boolean;
        xAxisVisibility?: boolean;
        yAxisVisibility?: boolean;
    };
}

export interface GraphDatum {
    coords: Point[];
    settings: {
        backupColor?: string;
        type: "line" | "area";
        areaMultiplier?: number;
        line?: Partial<React.SVGAttributes<SVGPathElement>>;
        area?: Partial<React.SVGAttributes<SVGPathElement>>;
    };
}

export interface CurveData {
    x: number[];
    defs?: [
        {
            gradients?: {
                linear?: [
                    {
                        id: string;
                        target: string;
                        stop: { offset: string; stopColor: string; stopOpacity: number }[];
                        coords: { x1: string; y1: string; x2: string; y2: string };
                    }
                ];
            };
        }
    ];
    dotIndicator?: {
        r?: number;
        fill?: string;
        class?: string;
        cursor?: string;
        stroke?: number;
        rPulse?: number;
        strokeWidth?: number;
    };
    areaMultiplier?: number;
    attr?: Partial<React.SVGAttributes<SVGPathElement>>;
    type?: "line" | "area";
}

export interface Dataset {
    dates: Date[];
    data: CurveData[];
    maximum: number;
}

interface Point {
    x: Date;
    y: number;
}

export interface onChartReadyFuncProps {
    divContainer: HTMLDivElement;
    svg: d3.Selection<SVGSVGElement, unknown, null, undefined>;
    chartsGridX: d3.Selection<SVGGElement, unknown, null, undefined>;
    yAxisGroup: d3.Selection<SVGGElement, unknown, null, undefined>;
    xAxisGroup: d3.Selection<SVGGElement, unknown, null, undefined>;
}

export const LineChart = ({
    dataset,
    margin,
    appTheme,
    tickSettings,
    axisSettings,
    onChartReady,
    className,
}: LineChartPops) => {
    const divContainer = useRef<HTMLDivElement>(null);
    const [dimensions, setDimensions] = useState({ width: 0, height: 0 });

    useEffect(() => {
        if (!divContainer.current) return;
        const resizeObserver = new ResizeObserver((entries) => {
            if (!entries || entries.length === 0) return;
            const { width, height } = entries[0].contentRect;
            if (width > 0 && height > 0) setDimensions({ width, height });
        });
        resizeObserver.observe(divContainer.current);
        return () => resizeObserver.disconnect();
    }, []);

    useEffect(() => {
        if (!divContainer.current || dimensions.width === 0 || dimensions.height === 0) return;

        const { width, height } = dimensions;
        d3.select(divContainer.current).selectAll("*").remove();

        const svg = d3
            .select(divContainer.current)
            .append("svg")
            .attr("width", width)
            .attr("height", height)
            .attr("viewBox", `0 0 ${width} ${height}`)
            .style("display", "block")
            .style("overflow", "visible");

        // === SCALES ===

        //-- Get Min & Max Dates
        const [dateMin, dateMax] = d3.extent(dataset.dates) as [Date, Date];

        // Add a small padding on each side (half a month) so the first/last
        // data points don't sit exactly on the axis edge.
        const domainPadMs = (dateMax.getTime() - dateMin.getTime()) / (dataset.dates.length * 2);
        const xDomainStart = new Date(dateMin.getTime() - domainPadMs);
        const xDomainEnd   = new Date(dateMax.getTime() + domainPadMs);

        const xScale = d3
            .scaleTime()
            .domain([xDomainStart, xDomainEnd])
            .range([(margin?.left || 0), width - (margin?.right || 0)]);

        const yScale = d3
            .scaleLinear()
            .domain([0, dataset.maximum || 100])
            .range([height - (margin?.bottom || 0), (margin?.top || 0)]);

        // === AXES ===

        // Y-Axis
        const yAxis = d3
            .axisLeft(yScale)
            .ticks(5)
            .tickSizeOuter(0)
            .tickFormat((d) => d3.format("~s")(d as number));

        const yAxisGroup = svg
            .append("g")
            .attr("class", "y-axis")
            .attr("transform", `translate(${margin?.left || 0}, 0)`)
            .call(yAxis);

        yAxisGroup.select(".domain").remove();

        yAxisGroup.selectAll("line")
            .attr("x2", width - (margin?.left || 0) - (margin?.right || 0))
            .attr("stroke-dasharray", "4 4")
            .attr("stroke", appTheme?.CURVE?.axes?.stroke || "#444")
            .attr("stroke-width", 1)
            .attr("opacity", 0.8);

        yAxisGroup.selectAll("text")
            .attr("x", -10)
            .attr("dy", 4)
            .attr("text-anchor", "end")
            .attr("fill", appTheme?.CURVE?.axes?.fill || "#f8fafc")
            .style("font-size", "12px");

        svg.selectAll(".tick").filter((t) => t === 0).remove();

        // Main group
        const chartsGridX = svg.append("g").attr("class", "charts-grid");

        //-- Generate one tick per month present in the data range.
        const tickValues: Date[] = [];
        const cursor = new Date(dateMin.getFullYear(), dateMin.getMonth(), 1);
        const endMonth = new Date(dateMax.getFullYear(), dateMax.getMonth() + 1, 1);
        while (cursor < endMonth) {
            tickValues.push(new Date(cursor));
            cursor.setMonth(cursor.getMonth() + 1);
        }

        // X-Axis
        const xAxis = d3
            .axisBottom(xScale)
            .tickValues(tickValues)
            .tickFormat((d) => d3.timeFormat("%b")(d as Date))
            .tickSizeOuter(0);

        const xAxisGroup = chartsGridX
            .append("g")
            .attr("class", "x-axis")
            .attr("transform", `translate(0, ${height - (margin?.bottom || 0)})`)
            .call(xAxis);

        xAxisGroup.selectAll("text")
            .attr("fill", appTheme?.CURVE?.axes?.fill || "#f8fafc")
            .style("font-size", "12px");

        xAxisGroup.selectAll(".domain, .tick line")
            .attr("stroke", appTheme?.CURVE?.axes?.stroke || "#f8fafc")
            .attr("stroke-width", appTheme?.CURVE?.axes?.strokeWidth || 1);

        // === FILTERS (Visibility) ===
        if (tickSettings) {
            if (typeof tickSettings.tickVisibility !== "undefined" && !tickSettings.tickVisibility) {
                xAxisGroup.selectAll(".tick").remove();
                yAxisGroup.selectAll(".tick").remove();
            } else {
                if (typeof tickSettings.xTickVisibility !== "undefined" && !tickSettings.xTickVisibility)
                    xAxisGroup.selectAll(".tick").remove();
                if (typeof tickSettings.yTickVisibility !== "undefined" && !tickSettings.yTickVisibility)
                    yAxisGroup.selectAll(".tick").remove();
            }
        }

        if (axisSettings) {
            if (typeof axisSettings.axisVisibility !== "undefined" && !axisSettings.axisVisibility) {
                xAxisGroup.selectAll(".domain").remove();
                yAxisGroup.selectAll(".domain").remove();
            }
            if (typeof axisSettings.xAxisVisibility !== "undefined" && !axisSettings.xAxisVisibility)
                xAxisGroup.selectAll(".domain").remove();
            if (typeof axisSettings.yAxisVisibility !== "undefined" && !axisSettings.yAxisVisibility)
                yAxisGroup.selectAll(".domain").remove();
        }

        // === DATA PREPARATION ===
        const grapDatum: GraphDatum[] = dataset.data.map((mark: any) => {
            const type = mark.type || "line";
            const settings: any = {
                type,
                areaMultiplier: mark.areaMultiplier,
                line: type === "line" ? { ...mark.attr } : undefined,
                area: type === "area" ? { ...mark.attr } : undefined,
                backupColor: undefined,
            };

            if (mark.defs) {
                mark.defs.forEach((defs: any) => {
                    const dfs = svg.append("defs");
                    defs.gradients?.linear?.forEach((ln: any) => {
                        const gradient = dfs
                            .append("linearGradient")
                            .attr("id", ln.id)
                            .attr("x1", ln.coords.x1)
                            .attr("y1", ln.coords.y1)
                            .attr("x2", ln.coords.x2)
                            .attr("y2", ln.coords.y2);

                        ln.stop.forEach((stp: any) =>
                            gradient
                                .append("stop")
                                .attr("offset", stp.offset)
                                .attr("stop-color", stp.stopColor)
                                .attr("stop-opacity", stp.stopOpacity)
                        );

                        if (type === "area") settings.area = { ...settings.area, fill: `url(#${ln.id})` };
                        if (type === "line") settings.line = { ...settings.line, stroke: `url(#${ln.id})` };
                    });
                });
            }

            const coords = dataset.dates.map((d: any, idx: number) => ({
                x: d,
                y: mark.x[idx],
            }));

            return { coords, settings };
        });

        // === DRAW CURVES ===
        grapDatum.forEach((graphData: GraphDatum, i: number) => {
            const curveDegree = 0.5;
            const dot = dataset.data[i].dotIndicator;

            if (graphData.settings.type === "area") {
                const areaMultiplier = graphData.settings?.areaMultiplier || 1;
                chartsGridX
                    .append("path")
                    .datum(graphData.coords)
                    .attr("fill", (graphData.settings.area?.fill as string) || "black")
                    .attr("stroke", (graphData.settings.area?.stroke as string) || "none")
                    .attr("stroke-width", (graphData.settings.area?.strokeWidth as number) ?? 0)
                    .attr(
                        "d",
                        d3
                            .area<any>()
                            .x((d) => xScale(d.x))
                            .y0(yScale(0))
                            .y1((d) => yScale(d.y * areaMultiplier))
                            .curve(d3.curveCatmullRom.alpha(curveDegree))
                    );
            }

            if (graphData.settings.type === "line") {
                const areaMatch = grapDatum.find(
                    (g: any) =>
                        g.settings.type === "area" &&
                        g.coords.length === graphData.coords.length &&
                        g.coords.every(
                            (p: any, idx: number) => +p.x === +graphData.coords[idx].x
                        )
                );

                const areaMultiplier = areaMatch?.settings?.areaMultiplier ?? 1;
                const transformedLineCoords = graphData.coords.map((d: any) => ({
                    ...d,
                    y: d.y * areaMultiplier,
                }));

                chartsGridX
                    .append("path")
                    .datum(transformedLineCoords)
                    .attr(
                        "stroke",
                        (graphData.settings.line?.stroke as string) ||
                        (graphData.settings.line?.fill as string) ||
                        "black"
                    )
                    .attr("stroke-width", (graphData.settings.line?.strokeWidth as number) ?? 2)
                    .attr("fill", "none")
                    .attr(
                        "d",
                        d3
                            .line<any>()
                            .x((d) => xScale(d.x))
                            .y((d) => yScale(d.y))
                            .curve(d3.curveCatmullRom.alpha(curveDegree))
                    );
            }

            if (dot) {
                chartsGridX
                    .selectAll(dot.class || ".dot-paid")
                    .data(graphData.coords)
                    .enter()
                    .append("circle")
                    .attr("class", dot.class || "dot-paid")
                    .attr("cx", (d) => xScale(d.x))
                    .attr("cy", (d) => yScale(d.y))
                    .attr("r", dot.r || 7)
                    .attr("fill", dot.fill || "black")
                    .attr("stroke", dot.stroke || "var(--app-textColor)")
                    .attr("stroke-width", dot.strokeWidth || 1)
                    .style("cursor", "pointer")
                    .on("mouseover", (event) => {
                        d3.select(event.currentTarget).transition().attr("r", dot.rPulse || 9);
                    })
                    .on("mouseout", (event) => {
                        d3.select(event.currentTarget).transition().attr("r", dot.r || 7);
                    });
            }
        });

        if (onChartReady) {
            onChartReady({ svg, chartsGridX, divContainer: divContainer.current!, xAxisGroup, yAxisGroup });
        }
    }, [dimensions, appTheme, margin, onChartReady, dataset]);

    return (
        <div
            ref={divContainer}
            className={`${styles?.container || ""} ${className || ""}`}
            style={{ width: "100%", height: "100%" }}
        />
    );
};