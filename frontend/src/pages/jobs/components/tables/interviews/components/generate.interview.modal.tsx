import { useTranslation } from "react-i18next";
import React, { useEffect, useRef, useState } from "react";

import { INTERVIEW_TYPES, InterviewType, type CreateInterviewFormData, type InterviewTypeValue } from "../../../../../../features/interviews/interviews";

import type { CandidateSearchItem } from "../../../../../../features/shared/global";
import { CandidateApplicationSelector } from "../../../../../components/selector/candidate.application.selector";
import { useAppContext } from "../../../../../../hooks/context";
import type { CompleteJobView } from "../../../../../../features/jobs/JobOffer";
import { getMinDateTime } from "../../../../../../utils/format";


import styles from "./GenerateInterviewModal.module.css"


export interface GenerateInterviewModalProps {
  onClose: () => void;
  /**@throws {Error}  */
  onSubmit: (payload: CreateInterviewFormData) => Promise<void>;
  updateJob?: React.Dispatch<React.SetStateAction<CompleteJobView | null>>
}

export const GenerateInterviewModal: React.FC<GenerateInterviewModalProps> = ({
  onClose,
  onSubmit,
  updateJob
}) => {
  const { t } = useTranslation();
  const { setPopup, setKpiData, setLoading } = useAppContext();

  const [formData, setFormData] = useState<CreateInterviewFormData>({
    candidateId: "",
    applicationId: "",
    minutes: 60,
    title: "",
    description: "",
    scheduledAt: "",
    url: "",
    type: undefined,
  });

  const [selectedCandidate, setSelectedCandidate] = useState<CandidateSearchItem | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  //-- Date time
  const [minDateTime, setMinDateTime] = useState(getMinDateTime); // minimum date time available
  useEffect(() => {
      const updateMinDateTime = () => {
          setMinDateTime(getMinDateTime());
      };

      updateMinDateTime();

      const interval = setInterval(updateMinDateTime, 60_000);

      return () => clearInterval(interval);
  }, []);


  //-------------------
  //-- Handle selected candidates
  //--------------------
  const handleSelectCandidate = (candidate: CandidateSearchItem | null) => {
    setSelectedCandidate(candidate);
    setFormData(prev => ({
        ...prev,
        candidateId: candidate?.candidateId ?? "",
        applicationId: candidate?.applicationId ?? "",
    }));
  };


  //--------------------
  //-- handle submit
  //---------------
  const handleSubmit = async (e: React.SubmitEvent) => {
    e.preventDefault();

    //-----------------------
    //-- Check Requirements
    //-----------------------
    if (!selectedCandidate) {
        setPopup({
            status: "error",
            message: t(
                'global.validation.candidateFieldMandatory'
            ),
        });
        return;
    }

    if (!formData.scheduledAt) {
        setPopup({
            status: "error",
            message: "Veuillez définir une date.",
        });
        return;
    }

    if (formData.minutes <= 0) {
        setPopup({
            status: "error",
            message: "La durée doit être supérieure à 0.",
        });
        return;
    }

    //-----------------------
    //-- Start Submitting
    //-----------------------
    
    try {
      setLoading({state: true, subtitle: t('interviews.message.interviewCreation') });
      setIsSubmitting(true);
      if(!selectedCandidate){
        setPopup({status: "error", message: t('global.validation.candidateFieldMandatory')})
        return;
      }

      const payload = {
          ...formData,
          scheduledAt: new Date(
              formData.scheduledAt
          ).toISOString(),
      };

      await onSubmit(payload);

      //-- Update Global kpi
      setKpiData((prev)=>{
        if(!prev) return prev;
        return ({...prev, scheduledInterviews: (prev.scheduledInterviews ?? 0) + 1  });
      });


      updateJob?.((prev)=>{
        if(!prev) return null;
        return ({...prev, cardinal:{ ...prev.cardinal, interviews: (prev.cardinal.interviews ?? 0) + 1 }})
      });

      //-- Complete submit -> close 
      onClose();

      setLoading({state: false, subtitle: t('interviews.apiResponses.success.save')})
    }
    catch (err) {
      console.error("Erreur lors de la génération de l'entretien", err);
      setLoading({state: false})
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

        {/** Candidate */}
        <div>
          Candidat / Candidature *
          <CandidateApplicationSelector
            value={selectedCandidate}
            onChange={handleSelectCandidate}
          />
        </div>

        {/* Date */}
        <label>
          Date et heure *
          <input
            type="datetime-local"
            required
            min={minDateTime}
            value={formData.scheduledAt}
            onChange={(e) =>
              setFormData((prev) => ({ ...prev, scheduledAt: e.target.value }))
            }
          />
        </label>

        {/** Duration */}
        <label>
          Durée de l'entretien (minutes) *
          <input
              type="number"
              min={1}
              placeholder="60"
              value={formData.minutes || ""}
              onChange={(e) =>
                  setFormData(prev => ({
                      ...prev,
                      minutes: Number(e.target.value),
                  }))
              }
              required
          />
        </label>


        <label>
            Type d'entretien
            <select
                value={formData.type ?? ""}
                onChange={(e) =>
                    setFormData(prev => ({
                        ...prev,
                        type: e.target.value
                            ? e.target.value as InterviewTypeValue
                            : undefined,
                    }))
                }
            >
                <option value="">
                    Sélectionner un type
                </option>
                {
                  INTERVIEW_TYPES.map((type, key)=>{
                    const text = renderInterviewType(type);
                    if(!text) return null;
                    return (
                      <option key={key} value={type}>{text}</option>
                    )
                  })
                }
                <option value=''>
                    Inconnu
                </option>
            </select>
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
            placeholder="1 200 characters"
            value={formData.description}
            onChange={(e) =>{
              const value = e.target.value ;
              if(value.length <= 1200){
                setFormData((prev) => ({ ...prev, description: value}))
              }
            }}
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
            disabled={
                isSubmitting ||
                !formData.candidateId ||
                !formData.applicationId ||
                !formData.scheduledAt ||
                formData.minutes <= 0
            }
          >
            {isSubmitting ? "Génération..." : "Générer"}
          </button>
        </div>
      </form>
    </div>
  );
};




const renderInterviewType = (text: InterviewTypeValue)=>{
  switch(text){
    case InterviewType.RH_INTERVIEWS:
       return "Entretien RH";
    case InterviewType.TECHNICAL_INTERVIEWS:
        return "Entretien technique";
    default:
        return null
  }
}
