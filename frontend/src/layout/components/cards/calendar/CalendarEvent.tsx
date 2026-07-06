import { useState } from "react";
import { useTranslation } from "react-i18next";
import { format } from "date-fns";

//-- Services
import { INITIAL_CALENDAR_EVENT_VIEW, type CalendarEvent as CalendarEventType} from "../../../../features/planning/planning";

//-- Custom Compoenents
import EditableTextInput from "../../form/input/text/EditableTextInput";
import BrandButton from "../../buttons/brand.button";
import MenuDrawer, { MenuDrawerTrigger } from "../../menu/drawer/menu.drawer";
import TimePicker from "../../form/input/date/time.picker";

//-- SVG Components
import CloseSVGComponent from "/src/assets/svg/close-svgrepo-com.svg";
import AddSVGComponent from "/src/assets/svg/add/add-svgrepo-com.svg"

//-- CSS Styles
import styles from "./CalendarEvent.module.css"
import BasicInput from "../../form/input/basic.input";



interface CalendarEventProps{
    date: Date | null;
    onSave: (data: CalendarEventType)=>void;
    onClose?: ()=>void;
}



const CalendarEvent: React.FC<CalendarEventProps> = ({
    date,
    onSave,
    onClose
}) => {
    const { t } = useTranslation(); 
    const [event, setEvent] = useState<CalendarEventType>(INITIAL_CALENDAR_EVENT_VIEW);

    if(!date)
        return null;

    return (
        <div 
            className={styles.container}
        >
            {/** Close Button */}
            <button
                onClick={()=> {
                    if(onClose)
                        onClose()
                    console.log("hdsdhdhdh")
                }}
                className={styles.closeButton}
            >
                <CloseSVGComponent
                    width={15}
                    height={15}
                />
            </button>

            {/** EDITABLE TITLE */}
            <EditableTextInput
                className={styles.title}
                placeholder={t("scheduler.createEvent.inputs.title.placeholder")}
                onChange={(value)=>{
                    setEvent((prev)=>({...prev, title: value}))
                }}
            />

            {/** CONTENT (INPUTS) */}
            <div className={styles.content}>
                {/** Dates */}
                <>
                    <span className={styles.label} >{t("global.dates.date")}</span>
                    <span className={styles.date}>{format(date, "MMMM d, yyyy")}</span>
                </>

                {/** Rates */}
                <>
                    <span className={styles.label} >{t("scheduler.createEvent.inputs.rate.label")}</span>
                    <MenuDrawer>
                        <MenuDrawerTrigger>
                            <div className={styles.rate}>Important</div>
                        </MenuDrawerTrigger>
                    </MenuDrawer>
                </>

                {/** HOURS */}
                <>
                    <span className={styles.label} >{t("global.duration.time.hours.hours_one")}</span>
                    <div className={styles.times}>
                        <TimePicker onChange={()=>{}}/>
                        <TimePicker onChange={()=>{}}/>
                    </div>
                </>

                {/** NOTES */}
                <>
                    <span className={styles.label} >{t("scheduler.createEvent.inputs.note.label")}</span>
                    <BasicInput
                        width="100%"
                        backgroundColor="#E2E8F0"
                        placeholder={t("scheduler.createEvent.inputs.title.placeholder")}
                    />
                </>

                {/** Menmber */}
                <>
                    <span className={styles.label}>{t("scheduler.createEvent.inputs.members.label")}</span>
                    <div className={styles.members}>
                        {/**Image circle */}
                        <div className={styles.entry}>
                            {/** <img src="..." /> */}
                        </div>

                        <button 
                            onClick={()=>{}}
                            className={styles.add}
                        >
                            <AddSVGComponent
                                width={15} height={15}
                                className={styles.svg}
                            />
                        </button>
                    </div>
                </>
            </div>

            {/** BOTTOM */}
            <div className={styles.bottom}>
                <BrandButton 
                    svg={null}
                    text={t("global.messages.save")}
                />
            </div>
        </div>
    );
}
 
export default CalendarEvent;