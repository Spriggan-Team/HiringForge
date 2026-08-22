import { useNavigate } from "react-router-dom";
import { useEffect, useState, useCallback } from "react";
import { useTranslation } from "react-i18next";

import { 
    type PublicJobOfferLightModel,
    type PublicJobOfferDetailsModel
} from "../../../api/services/public/responses";
import { navigateTo } from "../../../App";
import PublicJobQueries from "../../../api/services/public/queries";
import { useAppContext, useCandidateContext, useCurrentCandidate } from "../../../hooks/context";
import RouteScheme from "../../../route.scheme";


import { ConfirmModal } from "../../../layout/components/conform.box";
import BrandButton from "../../../layout/components/buttons/brand.button";
import BasicInput from "../../../layout/components/form/input/basic.input";
import TipTapRenderer from "../../../layout/components/editors/tiptap/tiptap.renderer";

import SearchSVGComponent from "/src/assets/svg/menu/search-svgrepo-com.svg";
import LocationSVGComponent from "/src/assets/svg/location/location-svgrepo-com.svg";


import styles from "./PublicJobPage.module.css";
import CandidatesQueries from "../../../api/services/candidate/queries";


const PAGINATION_LIMIT = 15;



const PublicJobPage = () => {
    const { t, i18n } = useTranslation();
    const navigate = useNavigate()

    const { currentActor, setModal } = useAppContext();
    const { appliedJobIds, setAppliedJobIds } = useCandidateContext();

    // Search state
    const [searchContext, setSearchContext] = useState({ title: "", address: "" });

    // List & Pagination State
    const [skip, setSkip] = useState<number>(0);
    const [totalJobs, setTotalJobs] = useState<number>(0);
    const [jobOffers, setJobOffers] = useState<PublicJobOfferLightModel[]>([]);
    const [isListLoading, setIsListLoading] = useState<boolean>(false);

    // Selection & Details State
    const [selectedJobId, setSelectedJobId] = useState<string | null>(null);
    const [isDetailsLoading, setIsDetailsLoading] = useState<boolean>(false);
    const [selectedJobDetails, setSelectedJobDetails] = useState<PublicJobOfferDetailsModel | null>(null);


    //-- search for disable application (already done!)
    useEffect(() => {
        const loadAppliedJobs = async () => {
            const jobIds = await CandidatesQueries.getCandidateApplicationJobIds();
            setAppliedJobIds(jobIds);
        };

        if(currentActor?.type == "candidate"){
            loadAppliedJobs();
        }
    }, [currentActor]);

    
    //  Fetching list
    const loadJobOffers = useCallback(
        async (resetSkip = false) => {
        setIsListLoading(true);
        const currentSkip = resetSkip ? 0 : skip;
        if (resetSkip) setSkip(0);

        try {
            const response = await PublicJobQueries.getPublicJobs({
            search: searchContext.title,
            address: searchContext.address,
            limit: PAGINATION_LIMIT,
            skip: currentSkip,
            locale: i18n.language,
            });

            setJobOffers(response.items);
            setTotalJobs(response.total);

            // Auto select first item if none selected or on fresh search
            if (response.items.length > 0 && (resetSkip || !selectedJobId)) {
            setSelectedJobId(response.items[0].id);
            }
            else if (response.items.length === 0) {
            setSelectedJobId(null);
            setSelectedJobDetails(null);
            }
        }
        catch (err) {
            console.error("Error fetching jobs", err);
        }
        finally {
            setIsListLoading(false);
        }
        },
        [searchContext, skip, i18n.language, selectedJobId]
    );


    useEffect(() => {
        loadJobOffers();
    }, [skip]);


    // Fetch details on selected job change
    useEffect(() => {
        if (!selectedJobId) return;

        const fetchDetail = async () => {
        setIsDetailsLoading(true);
        try {
            const details = await PublicJobQueries.getDetailsAboutPublicJob(selectedJobId);
            setSelectedJobDetails(details);
        }
        catch (err) {
            console.error("Error fetching job details", err);
        }
        finally {
            setIsDetailsLoading(false);
        }
        };

        fetchDetail();
    }, [selectedJobId]);

  
    const handleApplyClick = (id: string) => {
        //-- Start postulation
        if (currentActor?.type === "candidate") {
            navigateTo(navigate, RouteScheme.JobApplication, {params: { id }})
            return;
        }

        //-- display modal if not connected
        setModal({
            title: '',
            isOpen: true,
            content: (
            <ConfirmModal
                title={t("jobs.modal.loginRequiredTitle", "Connexion requise")}
                message={t("jobs.modal.loginRequiredMsg", "Vous devez être connecté en tant que candidat pour postuler à cette offre.")}
                cancelText={t("global.buttons.cancel", "Annuler")}
                confirmText={t("global.buttons.connexion", "Connexion")}
                variant="request"
                onCancel={() => setModal(null)}
                onConfirm={() => {
                    setModal(null);
                    navigateTo(navigate, RouteScheme.login);
                }}
            />
            ),
        });
    };

    // Determinate postulate button style
    const getPostulateBtnClass = (jobId?:string) => {
        if (!currentActor){
            return styles.deactivatePostulateBtn; 
        }
        if (currentActor.type === "candidate"){
            if(jobId && appliedJobIds[jobId]){
                return styles.deactivateBtn; 
            }
            return styles.postulateBtn; 
        }
        return styles.hidden; 
    };


    const handleSearchSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        loadJobOffers(true);
    };


    return (
    <main className={styles.container}>
            {/* Header Search Bar */}
            <form className={styles.header} onSubmit={handleSearchSubmit}>
                <div className={styles.searchInputsSection}>
                    <BasicInput
                        backgroundColor="white"
                        svg={SearchSVGComponent}
                        className={styles.input}
                        enableFocusWithinDefaultDesign={false}
                        placeholder={t("jobs.inputs.searchJob.placeholder", "Titre, compétences...")}
                        value={searchContext.title}
                        onChange={(e) => setSearchContext((prev) => ({ ...prev, title: e.target.value }))}
                    />
                    <BasicInput
                        backgroundColor="white"
                        svg={LocationSVGComponent}
                        className={styles.input}
                        enableFocusWithinDefaultDesign={false}
                        placeholder={t("jobs.inputs.searchAddress.placeholder", "Ville, pays...")}
                        value={searchContext.address}
                        onChange={(e) => setSearchContext((prev) => ({ ...prev, address: e.target.value }))}
                    />
                </div>
                <BrandButton
                    padding={"12px 30px"}
                    btnClassName={styles.searchButton}
                    text={t("jobs.buttons.search", "Rechercher")}
                    onClick={() => loadJobOffers(true)}
                />
            </form>

            {/* Main Content Layout */}
            <div className={styles.content}>
                {/* Left Side: Offer Cards */}
                <div className={styles.jobCollections}>
                    <div className={styles.resultsCounter}>
                        <span>{totalJobs}</span> {t("jobs.countLabel", "offres disponibles")}
                    </div>

                    {isListLoading ? (
                        <div className={styles.loader}>
                            <div className={styles.spinner} />
                            Chargement des offres...
                        </div>
                    ) : (
                        <div className={styles.cardsList}>
                            {jobOffers.map((job) => (
                                <div
                                    key={job.id}
                                    className={`${styles.jobCard} ${selectedJobId === job.id ? styles.activeCard : ""}`}
                                    onClick={() => setSelectedJobId(job.id)}
                                >
                                    {/* CARD Header */}
                                    <div className={styles.cardHeader}>
                                        {job.mainImage ? (
                                            <img src={job.mainImage} alt={job.title} className={styles.cardAvatar} />
                                        ): (
                                            <div className={styles.cardAvatarPlaceholder}>
                                                {job.title.charAt(0).toUpperCase()}
                                            </div>
                                        )}

                                        <div className={styles.cardHeaderInfo}>
                                            <h3 className={styles.cardTitle}>{job.title}</h3>
                                            
                                            <p className={styles.cardLocation}>
                                                📍 {job.location?.city ? `${job.location.city}, ${job.location.country}` : "Lieu non spécifié"}
                                            </p>
                                        </div>
                                    </div>

                                    {/* CARD Badges */}
                                    <div className={styles.cardBadges}>
                                        {job.contractType && (
                                            <span className={`${styles.badge} ${styles.badgeContract}`}>
                                                {job.contractType.label}
                                            </span>
                                        )}

                                        {job.jobWorkMode && (
                                            <span className={`${styles.badge} ${styles.badgeWorkMode}`}>
                                                {job.jobWorkMode}
                                            </span>
                                        )}

                                        {job.salary?.min && (
                                            <span className={styles.badgeSalary}>
                                                💰 {job.salary.min} - {job.salary.max} {job.salary.currency}
                                            </span>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}

                    {/* Pagination Controls */}
                    <div className={styles.pagination}>
                        <button
                            className={styles.paginationBtn}
                            disabled={skip === 0 || isListLoading}
                            onClick={() => setSkip((prev) => Math.max(0, prev - PAGINATION_LIMIT))}
                        >
                            ‹ Précédent
                        </button>

                        <span className={styles.paginationInfo}>
                            {Math.floor(skip / PAGINATION_LIMIT) + 1} / {Math.ceil(totalJobs / PAGINATION_LIMIT) || 1}
                        </span>

                        <button
                            className={styles.paginationBtn}
                            disabled={skip + PAGINATION_LIMIT >= totalJobs || isListLoading}
                            onClick={() => setSkip((prev) => prev + PAGINATION_LIMIT)}
                        >
                            Suivant ›
                        </button>
                    </div>
                </div>

                {/* Right Side: Selected Job Detail Panel */}
                <div className={styles.selectedOffer}>
                {
                    isDetailsLoading ? (
                        <div className={styles.loader}>
                        <div className={styles.spinner} />
                            Chargement des détails...
                        </div>
                    ) : selectedJobDetails ? (
                        <div className={styles.detailsContent}>
                            {/* Detailed Offer Header */}
                            <div className={styles.jobHeader}>

                                <div className={styles.jobHeaderTitleArea}>
                                    <h2>{selectedJobDetails.title}</h2>
                                    
                                    <div className={styles.detailsMeta}>
                                        {selectedJobDetails.contractType?.label && (
                                            <span className={styles.metaBadge}>📋 {selectedJobDetails.contractType.label}</span>
                                        )}
                                        {selectedJobDetails.location?.city && (
                                            <span className={styles.metaBadge}>📍 {selectedJobDetails.location.city}</span>
                                        )}
                                        {selectedJobDetails.jobWorkMode && (
                                            <span className={styles.metaBadge}>🏢 {selectedJobDetails.jobWorkMode}</span>
                                        )}
                                    </div>
                                </div>
                                
                                <div className={styles.jobActionArea}>
                                    <button 
                                        onClick={()=>handleApplyClick(selectedJobDetails.id)}
                                        className={getPostulateBtnClass(selectedJobDetails.id)}
                                    >
                                        {t("jobs.buttons.applyNow", "Postuler maintenant")}
                                    </button>
                                </div>
                            </div>

                            <div className={styles.detailsBody}>
                                {/* Rich Content Description */}
                                {
                                    selectedJobDetails.content ? (
                                        <div className={styles.section}>
                                            <h4 className={styles.sectionTitle}>Description du poste</h4>
                                            <div className={styles.descriptionWrapper}>
                                                <TipTapRenderer content={selectedJobDetails.content} />
                                            </div>
                                        </div>
                                    ) : <></>
                                }

                                {/* Skills */}
                                {selectedJobDetails.skills && selectedJobDetails.skills.length > 0 && (
                                    <div className={styles.section}>
                                        <h4 className={styles.sectionTitle}>Compétences requises</h4>
                                        <div className={styles.skillsList}>
                                            {selectedJobDetails.skills.map((skill, index) => (
                                                <span
                                                    key={index}
                                                    className={skill.isRequired ? styles.requiredSkill : styles.optionalSkill}
                                                >
                                                    {skill.name} {skill.isRequired && <span className={styles.requiredMark}>*</span>}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {/* Gallery */}
                                {selectedJobDetails.images && selectedJobDetails.images.length > 0 && (
                                <div className={styles.section}>
                                    <h4 className={styles.sectionTitle}>Galerie</h4>
                                    <div className={styles.imageGallery}>
                                        {selectedJobDetails.images.map((imgUrl, index) => (
                                            <div key={index} className={styles.galleryImageWrapper}>
                                            <img src={imgUrl} alt="Aperçu poste" />
                                            </div>
                                        ))}
                                    </div>
                                </div>
                                )}
                            </div>
                        </div>
                    )
                    : (
                        <div className={styles.emptyState}>
                            <div className={styles.emptyIcon}>💼</div>
                            <p>Sélectionnez une offre pour afficher sa fiche détaillée</p>
                        </div>
                    )
                }
                </div>
            </div>
        </main>
    );
};

export default PublicJobPage;