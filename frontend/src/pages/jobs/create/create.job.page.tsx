
import { useEffect } from "react";
import { useTranslation } from "react-i18next";

//--Custom Comoponents
import BreadCrumbs from "../../../layout/components/navigation/auth/link/bread.crumbs";
import InfoBoxSection from "./components/infoBoxSection/info.box.section";
import OptionBoxSection from "./components/optionBoxSection/option.box.section";


//-- Services
import { useAppContext } from "../../../hooks/context";
import RouteScheme from "../../../route.scheme";


//-- CSS Styles
import styles from "./CreateJobPage.module.css"



interface CreateJobPageProps{}


const CreateJobPage: React.FC<CreateJobPageProps> = () => {
    const { t } = useTranslation();
    const { setNavbar } = useAppContext();

    useEffect(()=>{
        //--navbar
        const linkData = [
                        { route: RouteScheme.userJobs, text: t("jobs.jobs"), current: false },
                        { route: RouteScheme.createJob, text: t("jobs.buttons.create"), current: true }
                    ];
        setNavbar({
            title: t("jobs.buttons.create"),
            description: (
                <BreadCrumbs
                    overlayColor="#4338CA"
                    links={linkData} 
                />
            )
        })
        return ()=>{
            setNavbar(null);
        };
    },[setNavbar])
    
    return (
        <main className={styles.container}>
            <div className={styles.mainInfoBox}>
                <InfoBoxSection />
            </div>

            <div className={styles.paramBox}>
                <OptionBoxSection />
            </div>
        </main>
    );
}


export default CreateJobPage;