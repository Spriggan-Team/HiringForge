import styles from "./style.module.css";

interface SeparatorProps {
    width?: string;
    height?: string;
    orient?: "vertical" | "horizontal";
    backgroundColor?: string;
    margin?: string
    className?: string;
    radius?: string;
}


const Separator: React.FC<SeparatorProps> = ({
    width,
    height,
    margin,
    radius,
    className,
    orient = "horizontal",
    backgroundColor = "#E5E7EB",
}) => {
    const computedWidth =
        width ?? (orient === "horizontal" ? "100%" : "1px");

    const computedHeight =
        height ?? (orient === "horizontal" ? "1px" : "100%");

    return (
        <div
            aria-hidden="true"
            className={`${styles.container} ${styles[orient]} ${className}`}
            style={{
                ["--separator-margin" as string]: margin,
                ["--separator-width" as string]: computedWidth,
                ["--separator-height" as string]: computedHeight,
                ["--separator-color" as string]: backgroundColor,
                ["--separator-border-radius" as string]: radius ?? "999px",
            }}
        />
    );
};

export default Separator;