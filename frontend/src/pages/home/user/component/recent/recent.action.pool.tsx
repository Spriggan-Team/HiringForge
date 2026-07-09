import { useState } from "react";
import { useTranslation } from "react-i18next";

//-- Services
import type { PersonActionType } from "../../../../../features/shared/account";

//-- Custom Components
import SectionHeader from "../../../../../layout/components/sections/sectionHeader/section.header";
import ViewAllLink from "../../../../../layout/components/link/view.all.link";

//-- CSS styles
import styles from "./RecentActionPool.module.css"
import RecentAction from "../../../../components/recentAction/recent.action";



const mockData = [
    {
        delay: "11min",
        person: "Sarah Martin",
        title: "Frontend Developper",
        type: "postulate" as PersonActionType,
    },
    {
        delay: "1h",
        person: "Marc Leroy",
        title: "Entretien Technique",
        type: "create-interview" as PersonActionType,
    },
    {
        delay: "2h",
        person: null,
        title: "Backend Developper",
        type: "publish-offer" as PersonActionType,
    },
    {
        delay: "3h",
        person: "Alice Dupont",
        title: "Entretien Technique",
        type: "confirm-interview" as PersonActionType,
    }
]

interface RecentActionPoolProps{}


const RecentActionPool: React.FC<RecentActionPoolProps> = ({}) => {
    const {t} = useTranslation();
    const [recentActions, setRecentAction] = useState(mockData);

    return (
        <div className={styles.container}>
            <SectionHeader
                title={t("global.actions.recentAction")}
                action={<ViewAllLink />}
            />
            <div className={styles.items}>
                {
                    recentActions.map((item, index)=>(
                        <RecentAction
                            key={index}
                            type={item.type}
                            title={item.title}
                            delay={item.delay}
                            person={item.person}
                            className={styles.item}
                        />
                    ))
                }
            </div>
        </div>
    );
}
 
export default RecentActionPool;

