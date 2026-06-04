import { useEffect, useMemo, useRef, useState } from "react";

//-- Import custom component & props 
import BasicInput, {
    type BasicInputProps
} from "../basic.input";

//-- Import custom svg
import CheckSVG from "/src/assets/svg/check/check-svgrepo-com.svg";
import CloseSVG from "/src/assets/svg/menu/close-svgrepo-com.svg"

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
    onBlur, onFocus,
    activateBubble = true,
    ...props
}) => {

    const { t } = useTranslation();
    const inputRef = useRef(null);

    const [started, setStarted] = useState(false);
    const [isInputFocus, setIsInputFocus] = useState(false);

    const [condition, setCondition] = useState<Conditions>({
                                        safeLength: false,
                                        uppercase: false,
                                        number: false
                                    });

    //-- percent data
    const percent = useMemo(() => {

        const values =
            Object.values(condition);

        const valid =
            values.filter(Boolean).length;

        return valid / values.length;

    }, [condition]);


    //-- eval
    const evalStrongPassword = (password: string)=>{
        const newConditions = {
            safeLength:
                password.length >= 8 &&
                password.length <= 128,

            uppercase:
                /[A-Z]/.test(password),

            number:
                /\d/.test(password)
        };

        setCondition(newConditions);

        if(setter){
            setter(
                Object
                    .values(newConditions)
                    .every(Boolean)
            );
        }
    }

    const toggleHiddenBoxVisibility = (started && isInputFocus);


    return (
        <div className={styles.container}>
            <BasicInput
                type="password"
                inputRef={inputRef}
                onChange={(event)=>{

                    const value =
                        event.target.value;

                    setStarted(
                        value.length > 0
                    );

                    evalStrongPassword(value);

                    onChange?.(event);
                }}
                onFocus={()=>{ setIsInputFocus(true) }}
                onBlur={()=>{ setIsInputFocus(false); }}
                {...props}
            />

            {
                activateBubble &&   (
                    <div
                        style={{ ["--visibility" as string]: toggleHiddenBoxVisibility  ? "visible" : "hidden" }} 
                        className={styles.invisibleSection}
                    >   
                        
                        <Gauge
                            height={toggleHiddenBoxVisibility ? 5 : 0}
                            width={"47%"}
                            percent={percent}
                            activeColor={
                                percent < .4
                                ? "#EF4444"
                                : percent < .8
                                ? "#F59E0B"
                                : "linear-gradient(to right, #009039 33%, #B4D83D 57%,  #41A802 100%)"
                            }
                            className={styles.gauge}
                            foregroundColor="#E5E7EB"
                        />
                        <div className={styles.indicators}>
                            { toggleHiddenBoxVisibility && <CloseSVG className={styles.closeSvg} height={24} width={24}/> }

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
}



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