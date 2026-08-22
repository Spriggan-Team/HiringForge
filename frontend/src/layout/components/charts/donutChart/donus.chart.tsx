import React, {
    useEffect,
    useRef
} from "react";

import * as d3 from "d3";

import styles from "./DonutChart.module.css";


export interface DonutChartData {
    label: string;
    value: number;
    color: string;
}


interface DonutChartProps {
    data: DonutChartData[];

    width?: number;
    height?: number;

    innerRadius?: number;
    outerRadius?: number;

    centerValue?: number | string;
    centerLabel?: string;

    className?: string;
}


const DonutChart: React.FC<DonutChartProps> = ({
    data,

    width = 240,
    height = 240,

    innerRadius = 70,
    outerRadius = 100,

    centerValue,
    centerLabel,

    className,
}) => {
    const svgRef = useRef<SVGSVGElement | null>(null);

    useEffect(() => {

        if (!svgRef.current)
            return;


        const svg = d3.select(svgRef.current);
        svg.selectAll("*").remove();

        const radius = Math.min(width, height) / 2;

        const chart = svg
            .append("g")
            .attr(
                "transform",
                `translate(${width / 2},${height / 2})`
            );


        const pie = d3
            .pie<DonutChartData>()
            .value(d => d.value)
            .sort(null);


            //-- Arcs
        const arc = d3
            .arc<d3.PieArcDatum<DonutChartData>>()
            .innerRadius(
                Math.min(innerRadius, radius)
            )
            .outerRadius(
                Math.min(outerRadius, radius)
            );


        const arcs = chart
            .selectAll(".arc")
            .data(pie(data))
            .enter()
            .append("g");


        arcs
            .append("path")
            .attr("fill", d => d.data.color)
            .attr("stroke", "#ffffff")
            .attr("stroke-width", 2)
            .transition()
            .duration(600)
            .attrTween("d", function(d) {
                const interpolate = d3.interpolate(
                    {
                        startAngle: 0,
                        endAngle: 0
                    },
                    d
                );

                return t => {
                    return arc(
                        interpolate(t) as d3.PieArcDatum<DonutChartData>
                    ) ?? "";
                };
            });


    }, [
        data,
        width,
        height,
        innerRadius,
        outerRadius
    ]);



    return (
        <div
            className={`${styles.container} ${className ?? ""}`}
            style={{
                width,
                height,
            }}
        >
            <svg
                ref={svgRef}
                width={width}
                height={height}
            />
            {(centerValue !== undefined || centerLabel) && (
                <div className={styles.center}>
                    {
                        centerValue !== undefined && (
                            <span className={styles.value}>
                                {centerValue}
                            </span>
                        )
                    }

                    {
                        centerLabel && (
                            <span className={styles.label}>
                                {centerLabel}
                            </span>
                        )
                    }
                </div>
            )}

        </div>
    );
};


export default DonutChart;