

import styles from "./MatchScoreCircle.module.css"

const MatchScoreCircle = ({ score }: { score: number }) => {
    const radius = 16;
    const circumference = 2 * Math.PI * radius;
    const strokeDashoffset = circumference - (score / 100) * circumference;

    // DYnamic color
    const getScoreColor = (val: number) => {
        if (val >= 80) return "#10b981"; // green / high
        if (val >= 60) return "#3b82f6"; // blue / medium
        if (val >= 40) return "#f59e0b"; // orange / average
        return "#ef4444"; // red / low
    };

    return (
        <div className={styles.scoreWrapper} title={`Correspondance : ${score}%`}>
            <svg className={styles.scoreSvg} viewBox="0 0 40 40">
                <circle
                    className={styles.scoreBg}
                    cx="20"
                    cy="20"
                    r={radius}
                />
                <circle
                    className={styles.scoreProgress}
                    cx="20"
                    cy="20"
                    r={radius}
                    stroke={getScoreColor(score)}
                    strokeDasharray={circumference}
                    strokeDashoffset={strokeDashoffset}
                />
            </svg>
            <span className={styles.scoreText}>{score}%</span>
        </div>
    );
};

export default MatchScoreCircle;