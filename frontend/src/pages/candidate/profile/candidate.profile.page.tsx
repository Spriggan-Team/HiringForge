import React, { useEffect, useState, type ChangeEvent } from "react";
import AddSVGComponent from "/src/assets/svg/add/add-svgrepo-com.svg";
import styles from "./CandidateProfilPage.module.css";

export interface Skill {
  id: string;
  name: string;
}

export interface Location {
  street: string;
  city: string;
  country: string;
  postalCode: string;
}

export interface Candidate {
  id: string;
  firstName: string;
  lastName: string;
  email: string;
  description: string;
  location: Location;
  skills: Skill[];
}

const MOCK_CANDIDATE: Candidate = {
  id: "cand-123",
  firstName: "Alexandre",
  lastName: "Dupont",
  email: "alexandre.dupont@example.com",
  description: "Développeur Full Stack passionné par React, Node.js et l'architecture logicielle propre.",
  location: {
    street: "12 Rue de la Paix",
    city: "Paris",
    country: "France",
    postalCode: "75002",
  },
  skills: [
    { id: "sk-1", name: "React.js" },
    { id: "sk-2", name: "TypeScript" },
    { id: "sk-3", name: "Node.js" },
    { id: "sk-4", name: "Doctrine ORM" },
  ],
};

const CandidateProfilPage: React.FC = () => {
  const [candidate, setCandidate] = useState<Candidate>(MOCK_CANDIDATE);
  const [isEditing, setIsEditing] = useState<boolean>(false);
  const [newSkillName, setNewSkillName] = useState<string>("");

  const [avatarUrl, setAvatarUrl] = useState<string>("");
  const [resumes, setResumes] = useState<Array<{ id: string; name: string; url: string }>>([]);

  useEffect(() => {
    return () => {
      if (avatarUrl) URL.revokeObjectURL(avatarUrl);
      resumes.forEach((file) => URL.revokeObjectURL(file.url));
    };
  }, [avatarUrl, resumes]);

  // Handler champs racines
  const handleInputChange = (e: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setCandidate((prev) => ({ ...prev, [name]: value }));
  };

  // Handler dédié à la sous-clé location
  const handleLocationChange = (e: ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setCandidate((prev) => ({
      ...prev,
      location: {
        ...prev.location,
        [name]: value,
      },
    }));
  };

  const handleAvatarChange = (e: ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      const file = e.target.files[0];
      if (avatarUrl) URL.revokeObjectURL(avatarUrl);
      setAvatarUrl(URL.createObjectURL(file));
    }
  };

  const handleResumeUpload = (e: ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      const file = e.target.files[0];
      const newFile = {
        id: `cv-${Date.now()}`,
        name: file.name,
        url: URL.createObjectURL(file),
      };
      setResumes((prev) => [...prev, newFile]);
    }
  };

  const handleRemoveResume = (id: string) => {
    setResumes((prev) => {
      const target = prev.find((item) => item.id === id);
      if (target) URL.revokeObjectURL(target.url);
      return prev.filter((item) => item.id !== id);
    });
  };

  const handleAddSkill = () => {
    if (!newSkillName.trim()) return;
    const newSkill: Skill = {
      id: `sk-${Date.now()}`,
      name: newSkillName.trim(),
    };
    setCandidate((prev) => ({ ...prev, skills: [...prev.skills, newSkill] }));
    setNewSkillName("");
  };

  const handleRemoveSkill = (skillId: string) => {
    setCandidate((prev) => ({
      ...prev,
      skills: prev.skills.filter((sk) => sk.id !== skillId),
    }));
  };

  return (
    <div className={styles.container}>
      <div className={styles.headerAction}>
        <h2>Profil Candidat</h2>
        <button 
          className={isEditing ? styles.saveBtn : styles.editBtn} 
          onClick={() => setIsEditing(!isEditing)}
        >
          {isEditing ? "Enregistrer" : "Modifier le profil"}
        </button>
      </div>

      {/* Header section: Avatar + General Info */}
      <div className={styles.profileHeader}>
        <div className={styles.avatarWrapper}>
          <img
            src={avatarUrl || "https://via.placeholder.com/120?text=Photo"}
            alt={`${candidate.firstName} ${candidate.lastName}`}
            className={styles.avatarImg}
          />
          {isEditing && (
            <label className={styles.avatarUploadBtn}>
              Changer
              <input type="file" accept="image/*" onChange={handleAvatarChange} hidden />
            </label>
          )}
        </div>

        <div className={styles.identityDetails}>
          {isEditing ? (
            <div className={styles.inputGroupRow}>
              <input
                type="text"
                name="firstName"
                value={candidate.firstName}
                onChange={handleInputChange}
                placeholder="Prénom"
                className={styles.inputField}
              />
              <input
                type="text"
                name="lastName"
                value={candidate.lastName}
                onChange={handleInputChange}
                placeholder="Nom"
                className={styles.inputField}
              />
            </div>
          ) : (
            <h1 className={styles.fullName}>
              {candidate.firstName} {candidate.lastName}
            </h1>
          )}

          {isEditing ? (
            <input
              type="email"
              name="email"
              value={candidate.email}
              onChange={handleInputChange}
              placeholder="Email"
              className={styles.inputField}
            />
          ) : (
            <p className={styles.emailText}>{candidate.email}</p>
          )}
        </div>
      </div>

      {/* Location Section */}
      <div className={styles.sectionCard}>
        <h3 className={styles.sectionTitle}>Localisation</h3>
        {isEditing ? (
          <div className={styles.locationFormGrid}>
            <input
              type="text"
              name="street"
              value={candidate.location.street}
              onChange={handleLocationChange}
              placeholder="Adresse (Rue)"
              className={styles.inputField}
            />
            <div className={styles.inputGroupRow}>
              <input
                type="text"
                name="postalCode"
                value={candidate.location.postalCode}
                onChange={handleLocationChange}
                placeholder="Code Postal"
                className={styles.inputField}
              />
              <input
                type="text"
                name="city"
                value={candidate.location.city}
                onChange={handleLocationChange}
                placeholder="Ville"
                className={styles.inputField}
              />
            </div>
            <input
              type="text"
              name="country"
              value={candidate.location.country}
              onChange={handleLocationChange}
              placeholder="Pays"
              className={styles.inputField}
            />
          </div>
        ) : (
          <p className={styles.locationText}>
            📍 {candidate.location.street}, {candidate.location.postalCode} {candidate.location.city}, {candidate.location.country}
          </p>
        )}
      </div>

      {/* Description Section */}
      <div className={styles.sectionCard}>
        <h3 className={styles.sectionTitle}>À propos</h3>
        {isEditing ? (
          <textarea
            name="description"
            rows={4}
            value={candidate.description}
            onChange={handleInputChange}
            className={styles.textareaField}
          />
        ) : (
          <p className={styles.descriptionText}>{candidate.description}</p>
        )}
      </div>

      {/* Skills Section */}
      <div className={styles.sectionCard}>
        <h3 className={styles.sectionTitle}>Compétences</h3>
        <div className={styles.skillsContainer}>
          {candidate.skills.map((skill) => (
            <span key={skill.id} className={styles.skillBadge}>
              {skill.name}
              {isEditing && (
                <button 
                  type="button" 
                  onClick={() => handleRemoveSkill(skill.id)} 
                  className={styles.removeSkillBtn}
                >
                  ×
                </button>
              )}
            </span>
          ))}
        </div>

        {isEditing && (
          <div className={styles.addSkillBox}>
            <input
              type="text"
              value={newSkillName}
              onChange={(e) => setNewSkillName(e.target.value)}
              placeholder="Ajouter une compétence (ex: Docker)"
              className={styles.inputField}
            />
            <button type="button" onClick={handleAddSkill} className={styles.addSkillBtn}>
              Ajouter
            </button>
          </div>
        )}
      </div>

      {/* Resumes Section */}
      <div className={styles.sectionCard}>
        <div className={styles.sectionHeader}>
          <h3 className={styles.sectionTitle}>Mes CV</h3>
          <label className={styles.uploadIconBtn} title="Ajouter un CV">
            <AddSVGComponent className={styles.addIcon} />
            <input 
              type="file" 
              accept=".pdf,.doc,.docx" 
              onChange={handleResumeUpload} 
              hidden 
            />
          </label>
        </div>

        <div className={styles.resumeList}>
          {resumes.length === 0 ? (
            <p className={styles.emptyText}>Aucun CV ajouté pour le moment.</p>
          ) : (
            resumes.map((resume) => (
              <div key={resume.id} className={styles.resumeItem}>
                <a href={resume.url} target="_blank" rel="noopener noreferrer" className={styles.resumeLink}>
                  📄 {resume.name}
                </a>
                <button 
                  type="button" 
                  onClick={() => handleRemoveResume(resume.id)} 
                  className={styles.deleteResumeBtn}
                >
                  Supprimer
                </button>
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  );
};

export default CandidateProfilPage;