import {  useEffect, useMemo, useRef, useState } from "react";

//-- Import custom component & props 
import BasicInput, {
    type BasicInputProps
} from "../basic.input";

//-- Import custom svg
import CheckSVG from "/src/assets/svg/check/check-svgrepo-com.svg?react";
import CloseSVG from "/src/assets/svg/menu/close-svgrepo-com.svg?react"

import styles from "./style.module.css";

import { useTranslation } from "react-i18next";

import Gauge from "../../../progress/gauge/gauge";

type HardPasswordProps =
    BasicInputProps & {
        setter?: (b: boolean)=>void;
        activateBubble?: boolean;
    };


type Conditions = {
    safeLength: boolean;
    uppercase: boolean;
    number: boolean;
}



const HardPassword: React.FC<HardPasswordProps> = ({
    onChange,
    setter,
    value,
    onBlur,
    onFocus,
    activateBubble = true,
    ...props
}) => {
    const { t } = useTranslation();
    const inputRef = useRef<HTMLInputElement>(null);
    const [isInputFocus, setIsInputFocus] = useState(false);
    const [condition, setCondition] = useState<Conditions>({
        safeLength: false,
        uppercase: false,
        number: false
    });

    const started = (value?.length ?? 0) > 0;
    const isBubbleVisible = activateBubble && started && isInputFocus;


    const percent = useMemo(() => {
        const values = Object.values(condition);
        const valid = values.filter(Boolean).length;

        return valid / values.length;

    }, [condition]);


    useEffect(() => {
        const password = value ?? "";
        const newConditions: Conditions = {
            safeLength:
                password.length >= 8 &&
                password.length <= 128,
            uppercase:
                /[A-Z]/.test(password),
            number:
                /\d/.test(password)
        };

        setCondition(newConditions);

        setter?.(
            Object
                .values(newConditions)
                .every(Boolean)
        );

    }, [value, setter]);

    

    return (
        <div className={styles.container}>
            <BasicInput
                {...props}
                type="password"
                inputRef={inputRef}
                value={value}
                onChange={(event) => {
                    onChange?.(event);
                }}
                onFocus={(event) => {
                    setIsInputFocus(true);
                    onFocus?.(event);
                }}
                onBlur={(event) => {
                    setIsInputFocus(false);
                    onBlur?.(event);
                }}
            />

            {
                isBubbleVisible && (
                    <div
                        className={styles.invisibleSection}
                    >
                        <Gauge
                            height={5}
                            width={"47%"}
                            percent={percent}
                            activeColor={
                                percent < .4
                                    ? "#EF4444"
                                    : percent < .8
                                        ? "#F59E0B"
                                        : "linear-gradient(to right, #009039 33%, #B4D83D 57%, #41A802 100%)"
                            }
                            className={styles.gauge}
                            foregroundColor="#E5E7EB"
                        />

                        <div className={styles.indicators}>
                            <CloseSVG
                                className={styles.closeSvg}
                                height={24}
                                width={24}
                            />

                            <span className={styles.ruleTitle}>
                                {
                                    t(
                                        "global.rules.password.title"
                                    )
                                }
                            </span>

                            <div
                                className={
                                    styles.detailsSection
                                }
                            >
                                <RuleItem
                                    active={
                                        condition.safeLength
                                    }
                                    text={
                                        t(
                                            "global.rules.password.lenght"
                                        )
                                    }
                                />

                                <RuleItem
                                    active={
                                        condition.uppercase
                                    }
                                    text={
                                        t(
                                            "global.rules.password.uppercase"
                                        )
                                    }
                                />

                                <RuleItem
                                    active={
                                        condition.number
                                    }
                                    text={
                                        t(
                                            "global.rules.password.number"
                                        )
                                    }
                                />
                            </div>
                        </div>
                    </div>
                )
            }
        </div>
    );
};




const RuleItem = ({
    active,
    text
}:{
    active: boolean;
    text: string;
}) => {

    return (
        <div
            className={styles.item}
        >

            <div
                className={
                    `
                    ${styles.svgContainer}
                    ${active
                        ? styles.active
                        : ""}
                    `
                }
            >
                <CheckSVG
                    width={14}
                    height={14}
                />
            </div>

            <span className={styles.txt}>
                {text}
            </span>
        </div>
    );
}

export default HardPassword;