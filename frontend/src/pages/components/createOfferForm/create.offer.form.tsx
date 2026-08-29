// CreateOfferForm.tsx
import React, { useCallback, useEffect, useRef, useState } from "react";
import { useAppContext } from "../../../hooks/context";

import type { CreateOfferPayload } from "../../../features/employment/offer";

import { SearchAutocomplete, type Item as AutoCompleteSearchResultItem } from "../../../layout/components/form/input/autocomplete/search.autocomplete";
import ApplicationQueries from "../../../api/services/application/queries";

import type { CandidateApplication } from "../../../api/services/application/response";

/** Style */
import styles from "./CreateOfferForm.module.css";


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


type SelectedCandidateApplicationEntity = {
  email: string;
  firstName: string;
  applicationId: string;
  candidateId: string;
  jobTitle: string;
};


/**
 * Employment offer
 * @param param0 
 * @returns 
 */
export const CreateOfferForm: React.FC<CreateOfferFormProps> = ({  onSubmit }) => {
    const { setModal } = useAppContext();

    const [selectedCandidateApplicationEntity, setSelectedCandidateApplicationEntity] = useState<
                                                                                            (AutoCompleteSearchResultItem & SelectedCandidateApplicationEntity) | null
                                                                                        >(null); //-- selected Candidate serach result

    const [message, setMessage] = useState("");
    const [expiredAt, setExpiredAt] = useState("");
    
    const [salary, setSalary] = useState<number | "">("");
    const [isSubmitting, setIsSubmitting] = useState(false);

    //-- Caches
    const imageCache = useRef<Record<string, string>>({}); // Clear image cache
    const searchCache = useRef<Record<string, AutoCompleteSearchResultItem[]>>({}); //api research cache


    //-- handle submit
    const handleSubmit = async (e: React.SubmitEvent) => {
        e.preventDefault();

        if (!selectedCandidateApplicationEntity  || !expiredAt) {
                console.warn("Soumission bloquée : des champs obligatoires sont manquants.");
                return;
        }

        setIsSubmitting(true);
        const cacheKey = `${selectedCandidateApplicationEntity.candidateId}_${selectedCandidateApplicationEntity.applicationId}`;

        try {
            let avatarUrl = null;
            if(imageCache.current[cacheKey]){
                avatarUrl = imageCache.current[cacheKey];
                delete imageCache.current[cacheKey];
            }

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
                jobTitle: selectedCandidateApplicationEntity.jobTitle as string,
                avatarUrl: avatarUrl,
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



    //-- handle search
    const handleOnSearch = useCallback(async (query: string) => {
        try {
            const cleanQuery = query.trim().toLocaleLowerCase();
            const data: CandidateApplication[] = await ApplicationQueries.searchCandidateByApplication(query);
            
            if(searchCache.current[cleanQuery]){
                return searchCache.current[cleanQuery];
            }

            // Promise.all est indispensable ici
            const items = await Promise.all(
                data.map(async (value) => {
                    const cacheKey = `${value.candidateId}_${value.applicationId}`;
                    let image: string | null = null;

                    if (imageCache.current[cacheKey]) {
                        image = imageCache.current[cacheKey];
                    } 
                    else {
                        try {
                            const blob = await ApplicationQueries.getCandidateProfilImage({
                                candidateId: value.candidateId,
                                applicationId: value.applicationId
                            });

                            if (blob && blob.size > 0) {
                                const url = URL.createObjectURL(blob);
                                image = url;
                                imageCache.current[cacheKey] = url;
                            }
                        } catch (error) {
                            console.warn(`Erreur de récupération image pour le candidat ${value.candidateId}:`, error);
                        }
                    }

                    return {
                        ...value,
                        id: value.candidateId,
                        image: image ?? undefined, 
                        jobTitle: value.jobTitle,
                        label: `${value.firstName} ${value.lastName}`,
                        sublabel: value.jobTitle,
                        applicationId: value.applicationId,
                    };
                })
            );

            searchCache.current[cleanQuery] = items;
            return items;
        }
        catch (error) {
            console.warn("Erreur lors de la recherche de candidat :", error);
            return []; 
        }
    }, []);



    //-- Cleaning Up Image URLs During Deconstruction
    useEffect(() => {
        return () => {
            Object.values(imageCache.current).forEach((url) => {
                URL.revokeObjectURL(url);
            });
        };
    }, []);



    return (
        <form onSubmit={handleSubmit} className={styles.form}>
            <div className={styles.formGroup}>
                <label className={styles.label}>Candidat / Candidature *</label>
                <SearchAutocomplete 
                    onSearch={handleOnSearch}
                    onSelect={(item) => {
                        setSelectedCandidateApplicationEntity(item as (AutoCompleteSearchResultItem & SelectedCandidateApplicationEntity) | null)
                    }}
                    placeholder="Tapez le nom d'une candidature"
                    debounceMs={300}
                    maxResults={5}
                    className={styles.autocompleteInput}
                />
                {selectedCandidateApplicationEntity && (
                    <div style={{ marginTop: '16px', padding: '12px', background: '#f1f5f9', borderRadius: '6px' }}>
                        <small style={{ color: '#64748b' }}>ID prêt à être envoyé au backend :</small>
                        <code style={{ display: 'block', fontWeight: 'bold', color: '#0f172a' }}>
                            {selectedCandidateApplicationEntity.id}
                        </code>
                    </div>
                )}
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