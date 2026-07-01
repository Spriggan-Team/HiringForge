
import { useState } from "react";
import { useTranslation } from "react-i18next";

//-- Custom Components
import CurrentJob from "./components/currentJob/current.job";
import JobsSection from "./components/jobs/jobs.section";
import BasicInput from "../../../layout/components/form/input/basic.input";
import BrandButton from "../../../layout/components/buttons/brand.button";
import JobMapFilters from "./components/filters/job.map.filter";


//-- SVG components
import SearchSVGComponent from "/src/assets/svg/menu/search-svgrepo-com.svg"
import LocationSVGComponent from "/src/assets/svg/location/location-svgrepo-com.svg"
import AddSVGComponent from "/src/assets/svg/add/add-svgrepo-com.svg"

//-- CSS styles 
import styles from "./UserJobPage.module.css"
import { jobsData } from "../../../core/mock/job.data";





interface UserJobPageProps{}


const UserJobPage: React.FC<UserJobPageProps> = ({}) => {
    const { t } = useTranslation();
    const [currentJobId, setCurrentJobId] = useState<string>("");

    return (
        <main className={styles.container}>
            {/** -- Header -- */}
            <div className={styles.header}>
                <div className={`${styles.searchInputsSection}`}>
                    <BasicInput
                        backgroundColor="white"
                        svg={SearchSVGComponent}
                        className={`${styles.input}`}
                        enableFocusWithinDefaultDesign = {false}
                        placeholder={t("jobs.inputs.searchJob.placeholder")}
                    />
                    <BasicInput
                        backgroundColor="white"
                        svg={LocationSVGComponent}
                        className={`${styles.input}`}
                        enableFocusWithinDefaultDesign = {false}
                        placeholder={t("jobs.inputs.searchAddress.placeholder")}
                    />
                </div>
                <BrandButton
                    onClick={()=> null}
                    svg={AddSVGComponent}
                    btnClassName={styles.createJob}
                    text={t("jobs.buttons.create")}
                />
            </div>
            
            {/** -- BODY -- */}
            <div className={styles.body}>
                <div className={styles.filter}>
                    <JobMapFilters />
                </div>

                <div className={styles.jobs}>
                    <JobsSection
                        data={jobsData}
                        onClick={(id)=> setCurrentJobId(id)}
                    />
                </div>

                <div className={styles.selectedJob}>
                    <CurrentJob />
                </div>
            </div>
            
            {/** -- FOOTER -- */}
            <div className={styles.footer}>
            </div>
        </main>
    );
}
 
export default UserJobPage;
