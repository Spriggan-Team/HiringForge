
import type React from "react";
import { useNavigate, useParams } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { useCallback, useEffect, useRef, useState } from "react";

//-- Services
import CandidatesQueries from "../../../api/services/candidate/queries";
import type { ResumeMetada } from "../../../features/candidates/candidates";
import type { PublicJobOfferDetailsModel } from "../../../api/services/public/responses";
import { useAppContext, useCurrentCandidate } from "../../../hooks/context";
import PublicJobQueries from "../../../api/services/public/queries";


//-- Custom compoenents
import JobSkill from "../../jobs/components/skills/job.skill";
import  TipTapRenderer from "../../../layout/components/editors/tiptap/tiptap.renderer";

//-- SVG Components
import CVFileSVG  from "/src/assets/svg/cv-file-interface-symbol-svgrepo-com.svg"

//-- Styles
import styles from "./JobApplicationPage.module.css"
import CandidateServices from "../../../api/services/candidate/command";
import { navigateTo } from "../../../App";
import RouteScheme from "../../../route.scheme";


interface JobApplicationPageProps{}



const JobApplicationPage: React.FC<JobApplicationPageProps> = () => {
    const naviagate = useNavigate();
    const { id }       = useParams<{ id: string }>();

    const candidate    = useCurrentCandidate();
    const { t }        = useTranslation();
    const { setPopup, setLoading } = useAppContext();
 
    const blobCache = useRef<Map<string, string>>(new Map());
 
    const [resumes,          setResumes]          = useState<ResumeMetada[]>([]);
    const [selectedResumeId, setSelectedResumeId] = useState<string>("");
    const [currentResumeUrl, setCurrentResumeUrl] = useState<string>("");
    const [isResumeLoading,  setIsResumeLoading]  = useState<boolean>(false);
 
    const [jobDetails,       setJobDetails]       = useState<PublicJobOfferDetailsModel | null>(null);
    const [isDetailsLoading, setIsDetailsLoading] = useState<boolean>(false);
 
    const [isSubmitting, setIsSubmitting] = useState<boolean>(false);
 
    const selectedResume = resumes.find(r => r.id === selectedResumeId) ?? null;
 
    // ---- Fetch job details -----
    const fetchJobDetails = useCallback(async () => {
        if (!id) 
            return;
        setIsDetailsLoading(true);
        try {
            const details = await PublicJobQueries.getDetailsAboutPublicJob(id);
            setJobDetails(details);
        } 
        catch (err) {
            console.error("Error fetching job details:", err);
        }
        finally {
            setIsDetailsLoading(false);
        }
    }, [id]);
 
    // -- Fetch resumes metadata ---
    const loadResumesMetaData = useCallback(async () => {
        try {
            const data = await CandidatesQueries.getResumes();
            setResumes(data);
            if (data.length > 0) setSelectedResumeId(data[0].id);
        } catch (err) {
            console.error("Error loading resumes metadata:", err);
        }
    }, []);

 
    // --- Init --
    useEffect(() => {
        loadResumesMetaData();
        fetchJobDetails();
        return () => {
            blobCache.current.forEach(url => URL.revokeObjectURL(url));
            blobCache.current.clear();
        };
    }, [loadResumesMetaData, fetchJobDetails]);

 
    // -- Load resume blob (with cache) --
    useEffect(() => {
        if (!selectedResumeId) return;
        let alive = true;
 
        const load = async () => {
            if (blobCache.current.has(selectedResumeId)) {
                setCurrentResumeUrl(blobCache.current.get(selectedResumeId)!);
                return;
            }
            setIsResumeLoading(true);
            try {
                const blob = await CandidatesQueries.getResumeContent(selectedResumeId);
                const url  = URL.createObjectURL(blob);
                if (alive) {
                    blobCache.current.set(selectedResumeId, url);
                    setCurrentResumeUrl(url);
                }
            } catch (err) {
                console.error("Failed to load resume content:", err);
            } finally {
                if (alive) setIsResumeLoading(false);
            }
        };
 
        load();
        return () => { alive = false; };
    }, [selectedResumeId]);
 


    // --- Submit application -- 
    const handleSubmit = useCallback(async () => {
        if (!id || !selectedResumeId) 
            return;
        setIsSubmitting(true);
        setLoading({ state: true, subtitle: t("applications.submitting.processing") });
        
        try {
            await CandidateServices.apply({
                jobId:    id,
                resumeId: selectedResumeId,
            });
            setPopup({ status: "success", message: t("applications.submitting.success") });
        }
        catch (err) {
            console.error("Application error:", err);
            setPopup({ status: "error", message: t("applications.submitting.error") });
        }
        finally {
            setIsSubmitting(false);
            setLoading({ state: false, subtitle: undefined });
            navigateTo(naviagate, RouteScheme.jobs)
        }
    }, [id, selectedResumeId, candidate.id, setLoading, setPopup, t]);
 

    // -- Salary label helper ---
    const salaryLabel = buildSalaryLabel(jobDetails?.salary ?? null);
 
    return (
        <div className={styles.container}>
 
            {/* Page header */}
            <header className={styles.pageHeader}>
                <h1 className={styles.pageTitle}>{t("applications.title")}</h1>
                {jobDetails && (
                    <p className={styles.pageSubtitle}>
                        <span className={styles.jobTitle}>{jobDetails.title}</span>
                        {jobDetails.company && (
                            <span className={styles.company}> — {jobDetails.company}</span>
                        )}
                    </p>
                )}
            </header>
 
            {/* Two-column layout */}
            <div className={styles.layout}>
 
                {/* ── Left: CV selector + preview ── */}
                <section className={styles.cvSection} aria-label="CV">
                    <div className={styles.cvSelectorHeader}>
                        <label htmlFor="resume-select" className={styles.label}>
                            <CVFileSVG width={18} height={18} />
                            <span>{t("applications.resumeLabel")}</span>
                        </label>
 
                        <select
                            id="resume-select"
                            className={styles.selectInput}
                            value={selectedResumeId}
                            onChange={e => setSelectedResumeId(e.target.value)}
                            disabled={resumes.length === 0}
                        >
                            {resumes.map(resume => (
                                <option key={resume.id} value={resume.id}>
                                    {resume.originalName || resume.name}
                                    {" "}({(resume.size / 1024).toFixed(1)} KB
                                    {" "}— {new Date(resume.createdAt).toLocaleDateString()})
                                </option>
                            ))}
                        </select>
                    </div>
 
                    {selectedResume && (
                        <div className={styles.resumeInfoBadge}>
                            <span className={styles.infoName}>
                                {selectedResume.originalName || selectedResume.name}
                            </span>
                            <span className={styles.infoMeta}>
                                {(selectedResume.size / 1024).toFixed(1)} KB
                                {" · "}
                                {t("applications.importedOn")}{" "}
                                {new Date(selectedResume.createdAt).toLocaleDateString()}
                            </span>
                        </div>
                    )}
 
                    <div className={styles.previewContainer}>
                        {isResumeLoading ? (
                            <div className={styles.stateBox}>
                                <span className={styles.spinner} aria-hidden />
                                <span>{t("applications.loadingResume")}</span>
                            </div>
                        ) : currentResumeUrl ? (
                            <iframe
                                src={currentResumeUrl}
                                className={styles.iframePreview}
                                title={t("applications.previewTitle")}
                            />
                        ) : (
                            <div className={styles.stateBox}>
                                <span>{t("applications.noResume")}</span>
                            </div>
                        )}
                    </div>
                </section>
 
                {/* ── Right: job details ── */}
                <section className={styles.jobSection} aria-label="Job details">
                    {isDetailsLoading ? (
                        <JobDetailsSkeleton />
                    ) : jobDetails ? (
                        <div className={styles.jobDetailsCard}>
 
                            {/* Cover images */}
                            {jobDetails.images?.length > 0 && (
                                <div className={styles.imageGallery}>
                                    {jobDetails.images.map((url, i) => (
                                        <img
                                            key={i}
                                            src={url}
                                            alt=""
                                            aria-hidden
                                            className={styles.jobImage}
                                        />
                                    ))}
                                </div>
                            )}
 
                            <h2 className={styles.jobHeading}>{jobDetails.title}</h2>
 
                            {/* Meta badges */}
                            <div className={styles.jobBadges}>
                                {jobDetails.location && (
                                    <span className={`${styles.badge} ${styles.badgePrimary}`}>
                                        📍 {[jobDetails.location.city, jobDetails.location.country].filter(Boolean).join(", ") || t("global.text.notSpecify")}
                                    </span>
                                )}
                                {jobDetails.contractType?.label && (
                                    <span className={`${styles.badge} ${styles.badgeSecondary}`}>
                                        💼 {jobDetails.contractType.label}
                                    </span>
                                )}
                                {jobDetails.jobWorkMode && (
                                    <span className={`${styles.badge} ${styles.badgeNeutral}`}>
                                        🏠 {jobDetails.jobWorkMode}
                                    </span>
                                )}
                                {salaryLabel && (
                                    <span className={`${styles.badge} ${styles.badgeSuccess}`}>
                                        💰 {salaryLabel}
                                    </span>
                                )}
                            </div>
 
                            {jobDetails.expertise && (
                                <div className={styles.metaRow}>
                                    <span className={styles.metaRowLabel}>
                                        {t("jobs.createJob.additionnalOpstions.inputs.expertiseLevel.label")}
                                    </span>
                                    <span className={styles.metaRowValue}>{jobDetails.expertise}</span>
                                </div>
                            )}
 
                            {/* Description */}
                            <div className={styles.jobDescription}>
                                <TipTapRenderer content={jobDetails.content as object} />
                            </div>
 
                            {/* Skills */}
                            {jobDetails.skills?.length > 0 && (
                                <div className={styles.sectionBlock}>
                                    <h3 className={styles.sectionTitle}>
                                        {t("jobs.createJob.skillSection.title")}
                                    </h3>
                                    <div className={styles.skillsList}>
                                        {jobDetails.skills.map(skill => (
                                            <JobSkill key={skill.id} content={skill.name} />
                                        ))}
                                    </div>
                                </div>
                            )}
 
                            {/* Languages */}
                            {jobDetails.languages?.length > 0 && (
                                <div className={styles.sectionBlock}>
                                    <h3 className={styles.sectionTitle}>
                                        {t("jobs.createJob.additionnalOpstions.inputs.requireLanguage.label")}
                                    </h3>
                                    <ul className={styles.languagesList}>
                                        {jobDetails.languages.map(lang => (
                                            <li key={lang.id} className={styles.languageItem}>
                                                <strong>{lang.name}</strong>
                                                <span className={styles.langLevel}>{lang.level}</span>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}
 
                            {/* Submit */}
                            <button
                                className={styles.submitButton}
                                disabled={isSubmitting || !selectedResumeId}
                                onClick={handleSubmit}
                            >
                                {isSubmitting
                                    ? t("applications.submitting.label")
                                    : t("applications.confirmCta")}
                            </button>
                        </div>
                    ) : null}
                </section>
            </div>
        </div>
    );
};
 
export default JobApplicationPage;
 
// ─────────────────────────────────────────────────────────────
// Skeleton
// ─────────────────────────────────────────────────────────────
 
const JobDetailsSkeleton: React.FC = () => (
    <div className={styles.jobDetailsCard}>
        {[80, 50, 100, 70, 90].map((w, i) => (
            <div
                key={i}
                className={styles.skeletonLine}
                style={{ width: `${w}%`, height: i === 0 ? 20 : 13 }}
            />
        ))}
    </div>
);

 
// -------------------
// Helper
// -------------
 
function buildSalaryLabel(
    salary?: { 
        min?: number | null;
        max?: number | null;
        currency?: string | null;
    } | null
): string | null {
    if (!salary) return null;
    
    const cur = salary.currency ?? "€";
    const min = salary.min ?? 0;
    const max = salary.max ?? 0;

    if (min && max)
        return `${min.toLocaleString()} – ${max.toLocaleString()} ${cur}`;
    if (min) 
        return `min. ${min.toLocaleString()} ${cur}`;
    if (max) 
        return `max. ${max.toLocaleString()} ${cur}`;
    return null;
}