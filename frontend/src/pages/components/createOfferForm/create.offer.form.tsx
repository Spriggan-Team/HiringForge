// CreateOfferForm.tsx
import React, { useState } from "react";
import { useAppContext } from "../../../hooks/context";

import type { CreateOfferPayload } from "../../../features/offer/offer";

/** Style */
import styles from "./CreateOfferForm.module.css";



interface CreateOfferFormProps {
    applications: Array<{ id: string; candidateId: string; candidateName: string; jobTitle: string }>;
    onSubmit: (payload: CreateOfferPayload) => Promise<void>;
}


export const CreateOfferForm: React.FC<CreateOfferFormProps> = ({ applications, onSubmit }) => {
    const { setModal } = useAppContext();
    const [selectedAppId, setSelectedAppId] = useState("");
    const [title, setTitle] = useState("");
    const [salary, setSalary] = useState<number | "">("");
    const [expiredAt, setExpiredAt] = useState("");
    const [message, setMessage] = useState("");
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        const selectedApp = applications.find((app) => app.id === selectedAppId);
        if (!selectedApp || !salary || !expiredAt) return;

        setIsSubmitting(true);
        try {
            await onSubmit({
                applicationId: selectedApp.id,
                candidateId: selectedApp.candidateId,
                title,
                message,
                salary: Number(salary),
                expiredAt: new Date(expiredAt).toISOString(),
            });
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
                <select
                    className={styles.select}
                    value={selectedAppId}
                    onChange={(e) => setSelectedAppId(e.target.value)}
                    required
                >
                    <option value="">Sélectionner un candidat...</option>
                    {applications.map((app) => (
                        <option key={app.id} value={app.id}>
                            {app.candidateName} — {app.jobTitle}
                        </option>
                    ))}
                </select>
            </div>

            <div className={styles.formGroup}>
                <label className={styles.label}>Titre de l'offre</label>
                <input
                    type="text"
                    className={styles.input}
                    placeholder="Ex: Offre - Développeur Fullstack Sr"
                    value={title}
                    onChange={(e) => setTitle(e.target.value)}
                />
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
                        required
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