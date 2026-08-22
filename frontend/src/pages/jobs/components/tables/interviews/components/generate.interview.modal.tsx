import React, { useEffect, useRef, useState } from "react";

import type { CreateInterviewFormData } from "../../../../../../features/interviews/interviews";
import type { CandidateLightModel } from "../../../../../../features/candidates/candidates";
import { useDebounce } from "../../../../../../hooks/timer";

import styles from "./GenerateInterviewModal.module.css"




export interface GenerateInterviewModalProps {
  jobId: string;
  onClose: () => void;
  onSubmit: (payload: CreateInterviewFormData) => Promise<void>;
  fetchCandidatesApi: (
    jobId: string,
    search: string,
    limit: number
  ) => Promise<CandidateLightModel[]>;
}

export const GenerateInterviewModal: React.FC<GenerateInterviewModalProps> = ({
  jobId,
  onClose,
  onSubmit,
  fetchCandidatesApi,
}) => {
  const [formData, setFormData] = useState<CreateInterviewFormData>({
    candidateId: "",
    title: "",
    description: "",
    scheduledAt: "",
    url: "",
  });

  const [candidateSearch, setCandidateSearch] = useState("");
  const [candidateOptions, setCandidateOptions] = useState<CandidateLightModel[]>([]);
  const [selectedCandidate, setSelectedCandidate] = useState<CandidateLightModel | null>(null);

  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [isLoadingCandidates, setIsLoadingCandidates] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);

  // -- Select Candidates cantainer ref
  const dropdownContainerRef = useRef<HTMLDivElement | null>(null);

  const debouncedCandidateSearch = useDebounce(candidateSearch, 300);

  // --- Handle close (candidates dropdown with outsider click)
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (
        dropdownContainerRef.current &&
        !dropdownContainerRef.current.contains(event.target as Node)
      ) {
        setIsDropdownOpen(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);


  // Dynamic loading of candidates
  useEffect(() => {
    if (selectedCandidate) return;

    let isMounted = true;
    const loadCandidates = async () => {
      setIsLoadingCandidates(true);
      try {
        const candidates = await fetchCandidatesApi(jobId, debouncedCandidateSearch, 7);
        if (isMounted) {
          setCandidateOptions(candidates);
        }
      }
      catch (error) {
        console.error("Erreur lors de la récupération des candidats:", error);
      }
      finally {
        if (isMounted) setIsLoadingCandidates(false);
      }
    };

    loadCandidates();

    return () => {
      isMounted = false;
    };
  }, [jobId, debouncedCandidateSearch, fetchCandidatesApi, selectedCandidate]);


  //-- Handle selected candidates
  const handleSelectCandidate = (candidate: CandidateLightModel) => {
    setSelectedCandidate(candidate);
    setFormData((prev) => ({ ...prev, candidateId: candidate.id }));
    setCandidateSearch("");
    setIsDropdownOpen(false);
  };


  //-- Handle removed candidates
  const handleRemoveCandidate = () => {
    setSelectedCandidate(null);
    setFormData((prev) => ({ ...prev, candidateId: "" }));
    setCandidateSearch("");
  };


  //-- handle submit
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!formData.candidateId || !formData.scheduledAt) {
      alert("Veuillez sélectionner un candidat et définir une date.");
      return;
    }

    try {
      setIsSubmitting(true);
      await onSubmit(formData);
      onClose();
    }
    catch (err) {
      console.error("Erreur lors de la génération de l'entretien", err);
    }
    finally {
      setIsSubmitting(false);
    }
  };


  const getInitials = (firstName: string, lastName: string) => {
    return `${firstName?.[0] ?? ""}${lastName?.[0] ?? ""}`.toUpperCase();
  };


  return (
    <div className={styles.modalCard}>
      <h3>Générer un nouvel entretien</h3>
      <form onSubmit={handleSubmit} className={styles.modalForm}>
        {/* Candidates Selection */}
        <div className={styles.fieldGroup}>
          <label>Candidat *</label>

          {selectedCandidate ? (
            <div className={styles.selectedCandidateCard}>
              <div className={styles.selectedCandidateInfo}>
                {selectedCandidate.avatarUrl ? (
                  <img
                    src={selectedCandidate.avatarUrl}
                    alt={`${selectedCandidate.firstName} ${selectedCandidate.lastName}`}
                    className={styles.candidateAvatar}
                  />
                ) : (
                  <div className={styles.candidateAvatarFallback}>
                    {getInitials(selectedCandidate.firstName, selectedCandidate.lastName)}
                  </div>
                )}

                <span className={styles.candidateName}>
                  {selectedCandidate.firstName} {selectedCandidate.lastName}
                </span>
              </div>

              <button
                type="button"
                onClick={handleRemoveCandidate}
                className={styles.btnRemoveCandidate}
                title="Changer de candidat"
              >
                ✕
              </button>
            </div>
          ) : (
            <div className={styles.customSelectContainer} ref={dropdownContainerRef}>
              <input
                type="text"
                required={!formData.candidateId}
                placeholder="Rechercher un candidat..."
                value={candidateSearch}
                onFocus={() => setIsDropdownOpen(true)}
                onChange={(e) => {
                  setCandidateSearch(e.target.value);
                  setIsDropdownOpen(true);
                }}
                className={styles.searchInput}
              />

              {isDropdownOpen && (
                <ul className={styles.dropdownList}>
                  {isLoadingCandidates ? (
                    <li className={styles.dropdownState}>Chargement...</li>
                  ) : candidateOptions.length === 0 ? (
                    <li
                      className={styles.dropdownState}
                      onClick={() => setIsDropdownOpen(false)}
                    >
                      Aucun candidat trouvé
                    </li>
                  ) : (
                    candidateOptions.map((candidate) => (
                      <li
                        key={candidate.id}
                        onClick={() => handleSelectCandidate(candidate)}
                        className={styles.dropdownOption}
                      >
                        {candidate.avatarUrl ? (
                          <img
                            src={candidate.avatarUrl}
                            alt={`${candidate.firstName} ${candidate.lastName}`}
                            className={styles.candidateAvatar}
                          />
                        ) : (
                          <div className={styles.candidateAvatarFallback}>
                            {getInitials(candidate.firstName, candidate.lastName)}
                          </div>
                        )}
                        <span className={styles.candidateName}>
                          {candidate.firstName} {candidate.lastName}
                        </span>
                      </li>
                    ))
                  )}
                </ul>
              )}
            </div>
          )}
        </div>

        {/* Title */}
        <label>
          Titre de l'entretien (optionnel)
          <input
            type="text"
            placeholder="ex: Entretien Technique - Tour 1"
            value={formData.title}
            onChange={(e) =>
              setFormData((prev) => ({ ...prev, title: e.target.value }))
            }
          />
        </label>

        {/* Date */}
        <label>
          Date et heure *
          <input
            type="datetime-local"
            required
            value={formData.scheduledAt}
            onChange={(e) =>
              setFormData((prev) => ({ ...prev, scheduledAt: e.target.value }))
            }
          />
        </label>

        {/* Video URL  */}
        <label>
          Lien / URL Visio (optionnel)
          <input
            type="url"
            placeholder="https://meet.google.com/..."
            value={formData.url}
            onChange={(e) =>
              setFormData((prev) => ({ ...prev, url: e.target.value }))
            }
          />
        </label>

        {/* Description */}
        <label>
          Description (optionnel)
          <textarea
            rows={3}
            value={formData.description}
            onChange={(e) =>
              setFormData((prev) => ({ ...prev, description: e.target.value }))
            }
          />
        </label>

        {/* Actions */}
        <div className={styles.modalFooter}>
          <button
            type="button"
            onClick={onClose}
            className={styles.btnSecondary}
            disabled={isSubmitting}
          >
            Annuler
          </button>
          <button
            type="submit"
            className={styles.btnPrimary}
            disabled={isSubmitting || !formData.candidateId}
          >
            {isSubmitting ? "Génération..." : "Générer"}
          </button>
        </div>
      </form>
    </div>
  );
};