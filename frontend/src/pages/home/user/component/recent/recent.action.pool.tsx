import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";

//-- Services
import NotificationQueries from "../../../../../api/services/notification/queries";
import NotificationServices from "../../../../../api/services/notification/command";
import type { NotificationTypes,  NotificationValueType } from "../../../../../features/notfication/notification";

//-- Custom Components
import RecentAction from "../../../../components/recentAction/recent.action";
import SectionHeader from "../../../../../layout/components/sections/sectionHeader/section.header";
import ViewAllLink from "../../../../../layout/components/link/view.all.link";

//-- CSS styles
import styles from "./RecentActionPool.module.css"
import { CardPlaceholder } from "../../../../../layout/components/cards/placeholder.php/card.placeholder";


interface RecentActionPoolProps{
    setNotificationCount: React.Dispatch<React.SetStateAction<number>>;
}


const RecentActionPool: React.FC<RecentActionPoolProps> = ({
    setNotificationCount
}) => {
    const {t} = useTranslation();
    const [recentActions, setRecentAction] = useState<NotificationTypes[]>([]);

    useEffect(()=>{
        const initNotificationData = async ()=>{
            try{
                const data = await NotificationQueries.getUserbNotifications({});
                setRecentAction(data);
                
                const ids = data.map(value => value.id);
                await NotificationServices.markNotificationAsRead(ids);

                setNotificationCount((prev) => (prev ?? 0) - (ids.length ?? 0) )
            }
            catch(error){
                console.log("Something went wrong ")
            }
        }
        initNotificationData();
    },[])

    return (
        <div className={styles.container}>
            <SectionHeader
                title={t("global.actions.recentAction")}
                action={<ViewAllLink />}
            />
            {
                recentActions.length > 0 ?
                    (
                        <div className={styles.items}>
                            {
                                recentActions.map((item, index)=>(
                                    <RecentAction
                                        key={index}
                                        notification={item}
                                        className={styles.item}
                                    />
                                ))
                            }
                        </div>
                    ) : (
                        <CardPlaceholder 
                            text={t('notifications.messages.noNotificationFounded')}
                        />
                    )
            }
        </div>
    );
}
 
export default RecentActionPool;

