
import { useCallback, useEffect, useRef, useState } from "react";
import { useTranslation } from "react-i18next";
import { useNavigate } from "react-router-dom";

//--Custom Comoponents
import BreadCrumbs from "../../../layout/components/navigation/auth/link/bread.crumbs";
import InfoBoxSection from "./components/infoBoxSection/info.box.section";
import OptionBoxSection from "./components/optionBoxSection/option.box.section";
import ImageInput from "../../../layout/components/form/input/image/image";
import InputLabel from "../../../layout/components/form/input/input.label";

//-- Services
import { navigateTo } from "../../../App";
import RouteScheme from "../../../route.scheme";
import { useAppContext } from "../../../hooks/context";
import JobContextProvider  from "../../../context/job.context";
import { INITIAL_JOB_VIEW, type JobView } from "../../../features/jobs/JobOffer";
import JobServices from "../../../api/services/jobs/command";
import { FailedJobAssetsUpload } from "../../../api/services/jobs/exceptions";

//-- SVG components
import RightToLeftArrowSVG from '/src/assets/svg/arrows/back-arrow-direction-down-right-left-up-svgrepo-com.svg';

//-- CSS Styles
import styles from "./CreateJobPage.module.css"
import { validateSalary } from "../../../utils/validators";




interface CreateJobPageProps{}


const CreateJobPage: React.FC<CreateJobPageProps> = () => {
    const navigate = useNavigate();
    const { t } = useTranslation();
    const { setNavbar, setLoading, setPopup, setCurrentJob } = useAppContext();

    const [image, setImage] = useState<File | null>(null);
    const savedJobOffer = useRef<{offerId?: string} | null>(null);

    //  Navbar 
    useEffect(() => {
        const linkData = [
            { route: RouteScheme.userJobs,  text: t("jobs.jobs"),           current: false },
            { route: RouteScheme.createJob, text: t("jobs.buttons.create"), current: true  },
        ];
        
        setNavbar({
            title: t("jobs.buttons.create"),
            description: <BreadCrumbs overlayColor="#4338CA" links={linkData} />,
        });

        setCurrentJob(INITIAL_JOB_VIEW);

        return () => setNavbar(null);
    }, [setNavbar, t]);


        //-- Validate date
    const validateCurrentJob = useCallback((currentJob: JobView) => {
        const isSalaryValid = validateSalary(
            currentJob,
            () => setPopup({
                status: "warning",
                title: t('jobs.createJob.constraints.minSalary.title'),
                message: `${t('jobs.createJob.constraints.minSalary.minSalaryConstraint')} (${currentJob.salary?.max}).`,
            }),
            () => setPopup({
                status: "warning",
                title: t('jobs.createJob.constraints.maxSalary.title'),
                message: `${t('jobs.createJob.constraints.maxSalary.maxSalaryConstraint')} (${currentJob.salary?.min}).`,
            }),
        );
        
        const isTitleValid = currentJob.title.length >= 10 && currentJob.title.length <= 255;
        return isSalaryValid && isTitleValid;
    }, [t, setPopup]);



    //-- Handlers
    const handleSave = useCallback(
        async (currentJob: JobView) => {
            // -- Validation
            if (!validateCurrentJob(currentJob)) {
                console.warn("There are still invalid inputs in your job");
                return;
            }

            try {
                setLoading({ state: true, subtitle: t("jobs.createJob.messages.creatingJob") });

                // -- Create or Retrieve offer ID
                let offerId = savedJobOffer.current?.offerId;

                if (!offerId) {
                    const response = await JobServices.createJob(currentJob);
                    offerId = response.offerId;
                    
                    //-- update saved
                    savedJobOffer.current = { 
                        ...savedJobOffer.current, 
                        offerId 
                    };

                    console.log({ savedJobOffer: savedJobOffer.current })
                }

                // -- Upload image (id present)
                if (image) {
                    setLoading({ state: true, subtitle: t("jobs.createJob.messages.uploadingImage") }); 
                    try {
                        await JobServices.uploadJobAssets(offerId, [{ file: image, isMain: true }]);
                    }
                    catch (error) {
                        setLoading({ state: false, subtitle: undefined });

                        if (error instanceof FailedJobAssetsUpload) {
                            setPopup({ status: "error", message: t("jobs.createJob.messages.failedUploadMainImage") });
                            return; //--  stop
                        }
                        
                        throw error; //-- execute global catch
                    }
                }

                // -- Total Success  & Redirection
                setPopup({ status: "success", message: t("jobs.createJob.messages.jobCreate") });
                setLoading({ state: false, subtitle: undefined });

                setTimeout(() => {
                    navigate(RouteScheme.userJobs);
                }, 1500);

            }
            catch (error) {
                setLoading({ state: false, subtitle: undefined });
                if (error instanceof Error) {
                    setPopup({ status: "error", message: t("jobs.createJob.messages.failToCreateJob") });
                } else {
                    setPopup({ status: "error", message: t("global.messages.error") });
                }
                console.error("Unknown error:", error);
            }
        },
        [setLoading, setPopup, t, image, navigate, validateCurrentJob],
    );





    // Render 
    return (
        <JobContextProvider>
            <main className={styles.container}>
                {/* Side rail */}
                <aside className={styles.side}>
                    <button
                        className={`${styles.backButton} card`}
                        onClick={() => navigateTo(navigate, RouteScheme.userJobs)}
                        aria-label={t("global.buttons.back")} 
                    >
                        <RightToLeftArrowSVG width={15} height={15} />
                    </button>
                </aside>

                {/* Content */}
                <section className={styles.content}>
                    <div className={styles.columns}>
                        <div className={styles.mainInfoBox}>
                            <InfoBoxSection />
                        </div>
                        <div className={`${styles.image} card`}>
                            <InputLabel label="Main image" />
                            <ImageInput
                                onChange={(file)=> setImage(file)}
                            />
                        </div>
                        <div className={styles.paramBox}>
                            <OptionBoxSection onComplete={handleSave} />
                        </div>
                    </div>
                </section>
            </main>
        </JobContextProvider>
    );
};


export default CreateJobPage ;