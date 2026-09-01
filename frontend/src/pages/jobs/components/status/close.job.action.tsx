import { useState } from 'react';
import ToggleSwitch from '../../../../layout/components/switch/toggle.switch';
import { useAppContext } from '../../../../hooks/context';
import { ConfirmModal } from '../../../../layout/components/conform.box';

interface JobStatusToggleProps {
  jobTitle: string;
  isClosed: boolean;
  onToggleStatus: (shouldClose: boolean) => Promise<void>;
}

export const JobStatusToggle = ({ 
  jobTitle, 
  isClosed, 
  onToggleStatus 
}: JobStatusToggleProps) => {
  const [isLoading, setIsLoading] = useState(false);
  const { setModal } = useAppContext();

  const handleSwitchChange = (checked: boolean) => {
    // `checked = true` means we wand to close the offers
    const shouldClose = checked;

    setModal({
      isOpen: true,
      title: shouldClose ? "Fermer l'offre" : "Rouvrir l'offre",
      content: (
        <ConfirmModal
          title={shouldClose ? "Fermer l'offre d'emploi ?" : "Rouvrir l'offre d'emploi ?"}
          variant={shouldClose ? "danger" : "primary"}
          confirmText={shouldClose ? "Oui, fermer" : "Oui, rouvrir"}
          cancelText="Annuler"
          message={
            shouldClose ? (
              <>
                Êtes-vous sûr de vouloir fermer <strong>{jobTitle}</strong> ? 
                Les candidats ne pourront plus soumettre de candidature.
              </>
            ) : (
              <>
                Êtes-vous sûr de vouloir rouvrir <strong>{jobTitle}</strong> ? 
                L'offre sera de nouveau visible par les candidats.
              </>
            )
          }
          onCancel={() => setModal(null)}
          onConfirm={async () => {
            try {
              setIsLoading(true);
              await onToggleStatus(shouldClose);
            } finally {
              setIsLoading(false);
              setModal(null);
            }
          }}
        />
      ),
      onClose: () => setModal(null)
    });
  };

  return (
    <ToggleSwitch
      checked={isClosed}
      disabled={isLoading}
      text={isClosed ? "Offre fermée" : "Offre ouverte"}
      onChange={handleSwitchChange}
    />
  );
};