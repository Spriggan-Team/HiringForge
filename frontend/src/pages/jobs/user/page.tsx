
import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useTranslation } from "react-i18next";

//-- Services
import { navigateTo } from "../../../App";
import RouteScheme from "../../../route.scheme";
import { jobsData, jobsViewData } from "../../../core/mock/job.data";


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






interface UserJobsPageProps{}


const UserJobsPage: React.FC<UserJobsPageProps> = ({}) => {
    const { t } = useTranslation();
    const navigate = useNavigate();

    const [currentJobId, setCurrentJobId] = useState<string>("1");

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
                    svg={AddSVGComponent}
                    btnClassName={styles.createJob}
                    text={t("jobs.buttons.create")}
                    onClick={()=> navigateTo(navigate, RouteScheme.createJob)}
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
                    <CurrentJob job={jobsViewData[currentJobId]} />
                </div>
            </div>
            
            {/** -- FOOTER -- */}
            <div className={styles.footer}>
            </div>
        </main>
    );
}
 
export default UserJobsPage;
