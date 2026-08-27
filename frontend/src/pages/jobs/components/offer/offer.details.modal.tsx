import React from 'react';
import styles from './OfferDetailModal.module.css';
import type { FlatOffer } from '../../../../features/employment/offer';

interface OfferDetailModalProps {
  offer: FlatOffer;
  onClose: () => void;
}

export const OfferDetailModal: React.FC<OfferDetailModalProps> = ({
  offer,
  onClose,
}) => {
  const formatSalary = (salary: number) =>
    new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency: 'EUR',
      maximumFractionDigits: 0,
    }).format(salary);

  return (
    <div className={styles.container}>
      {/** Avatar */}
      <div className={styles.header}>
        {offer.avatarUrl ? (
          <img
            src={offer.avatarUrl}
            alt={offer.candidate}
            className={styles.avatar}
          />
        ) : (
          <div className={styles.avatarFallback}>
            {offer.candidate.substring(0, 2).toUpperCase()}
          </div>
        )}

        <div>
          <h3 className={styles.candidateName}>{offer.candidate}</h3>
          <p className={styles.email}>{offer.email}</p>
        </div>
      </div>

      <hr className={styles.separator} />

      {/** Poste */}
      <div className={styles.details}>
        <div className={styles.detailItem}>
          <strong>Poste :</strong>
          <p className={styles.detailValue}>{offer.jobTitle}</p>
        </div>

        {/** Rebumération */}
        <div className={styles.detailItem}>
          <strong>Rémunération :</strong>
          <p className={styles.detailValue}>
            {offer.salary ? formatSalary(offer.salary) : "Aucune"} 
          </p>
        </div>

        {/** Created - At */}
        <div className={styles.detailItem}>
          <strong>Date d'envoi :</strong>
          <p className={styles.detailValue}>
            {offer.createdAt || '—'}
          </p>
        </div>

        {/** Expired - At */}
        <div className={styles.detailItem}>
          <strong>Expiration :</strong>
          <p className={styles.detailValue}>
            {offer.expiresAt || '—'}
          </p>
        </div>

        {/** Status */}
        <div className={styles.detailItem}>
          <strong>Statut :</strong>
          <p className={styles.detailValue}>
            {offer.status}
          </p>
        </div>
        
        {offer.message && (
          <div className={styles.messageBox}>
            <strong>Message :</strong>
            <p className={styles.messageContent}>{offer.message}</p>
          </div>
        )}
      </div>

      <div className={styles.actions}>
        <button
          type="button"
          onClick={onClose}
          className={styles.closeButton}
        >
          Fermer
        </button>
      </div>
    </div>
  );
};