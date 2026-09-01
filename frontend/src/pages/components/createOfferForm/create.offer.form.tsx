// CreateOfferForm.tsx
import React, { useCallback, useEffect, useRef, useState } from "react";
import { useAppContext } from "../../../hooks/context";

import type { CreateOfferPayload } from "../../../features/employment/offer";

import { SearchAutocomplete } from "../../../layout/components/form/input/autocomplete/search.autocomplete";

/** Style */
import styles from "./CreateOfferForm.module.css";
import { candidateSearchService } from "../../../services/CandidateSearchService";
import type { AutoCompleteSearchResultItem, CandidateSearchItem } from "../../../features/shared/global";
import { CandidateApplicationSelector } from "../selector/candidate.application.selector";


interface CreateOfferFormProps {
    onSubmit: (
        payload: CreateOfferPayload & { 
            jobTitle: string,
            candiate:{
                email: string,
                lastName: string,
                firstName: string
            }
            avatarUrl?: string | null  
        }
    ) => Promise<void>;
}



/**
 * Employment offer
 * @param param0 
 * @returns 
 */
export const CreateOfferForm: React.FC<CreateOfferFormProps> = ({  onSubmit }) => {
    const { setModal, setPopup } = useAppContext();

    const [
        selectedCandidateApplicationEntity, 
        setSelectedCandidateApplicationEntity
    ] = useState<CandidateSearchItem | null>(null); //-- selected Candidate serach result

    const [scheduledEndDate, setScheduledEndDate] = useState<string>("");

    const [message, setMessage] = useState("");
    const [expiredAt, setExpiredAt] = useState("");
    
    const [salary, setSalary] = useState<number | "">("");
    const [isSubmitting, setIsSubmitting] = useState(false);


    //-- handle submit
    const handleSubmit = async (e: React.SubmitEvent) => {
        e.preventDefault();

        if (!selectedCandidateApplicationEntity) {
            setPopup({ 
                status: "warning", 
                message: "Vous devez sélectionner un candidat avant de créer une offre d'embauche." 
            });
            return;
        }

        if (!selectedCandidateApplicationEntity  || !expiredAt || !scheduledEndDate) {
            console.warn("Soumission bloquée : des champs obligatoires sont manquants.");
            return;
        }

        setIsSubmitting(true);

        try {

            const payload = {
                message,
                salary: Number(salary),
                candiate:{
                    email: selectedCandidateApplicationEntity.email as string,
                    lastName: selectedCandidateApplicationEntity.lastName as string,
                    firstName: selectedCandidateApplicationEntity.firstName as string
                },
                applicationId: selectedCandidateApplicationEntity.applicationId as string,
                candidateId: selectedCandidateApplicationEntity.candidateId as string,
                expiredAt: new Date(expiredAt).toISOString(),
                scheduledEndDate: new Date(scheduledEndDate).toISOString(),
                jobTitle: selectedCandidateApplicationEntity.jobTitle as string,
                avatarUrl: selectedCandidateApplicationEntity?.image,
            };

            console.log("GEN Employment: ", payload)
            await onSubmit(payload);
            setModal(null);
        }
        catch (error) {
            console.error("Failed to create offer:", error);
        }
        finally {
            setIsSubmitting(false);
        }
    };


    return (
        <form onSubmit={handleSubmit} className={styles.form}>
            <div className={styles.formGroup}>
                <label className={styles.label}>Candidat / Candidature *</label>
                <CandidateApplicationSelector
                    value={selectedCandidateApplicationEntity}
                    onChange={setSelectedCandidateApplicationEntity}
                />
                {/* {selectedCandidateApplicationEntity && (
                    <div style={{ marginTop: '16px', padding: '12px', background: '#f1f5f9', borderRadius: '6px' }}>
                        <small style={{ color: '#64748b' }}>ID prêt à être envoyé au backend :</small>
                        <code style={{ display: 'block', fontWeight: 'bold', color: '#0f172a' }}>
                            {selectedCandidateApplicationEntity.id}
                        </code>
                    </div>
                )} */}
            </div>


            <div className={styles.formRow}>
                <div className={styles.formGroup}>
                    <label className={styles.label}>Salaire Annuel (€) *</label>
                    <input
                        type="number"
                        className={styles.input}
                        placeholder="45000"
                        value={salary}
                        onChange={(e) => setSalary(e.target.value ? Number(e.target.value) : "")}
                    />
                </div>

                <div className={styles.formGroup}>
                    <label className={styles.label}>Date d'expiration *</label>
                    <input
                        type="date"
                        className={styles.input}
                        value={expiredAt}
                        onChange={(e) => setExpiredAt(e.target.value)}
                        required
                    />
                </div>
            </div>

            <div className={styles.formGroup}>
                <label className={styles.label}>Date de fin *</label>
                <input
                    type="date"
                    className={styles.input}
                    value={scheduledEndDate}
                    onChange={(e) => setScheduledEndDate(e.target.value)}
                    required
                />
            </div>

            <div className={styles.formGroup}>
                <label className={styles.label}>Message / Conditions</label>
                <textarea
                    rows={4}
                    className={styles.textarea}
                    value={message}
                    onChange={(e) => setMessage(e.target.value)}
                />
            </div>

            <div className={styles.actions}>
                <button
                    type="button"
                    onClick={() => setModal(null)}
                    className={styles.btnSecondary}
                >
                    Annuler
                </button>
                <button
                    type="submit"
                    disabled={isSubmitting}
                    className={styles.btnPrimary}
                >
                    {isSubmitting ? "Envoi..." : "Générer l'offre"}
                </button>
            </div>
        </form>
    );
};