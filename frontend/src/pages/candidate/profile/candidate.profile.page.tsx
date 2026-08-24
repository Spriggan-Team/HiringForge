import { useTranslation } from "react-i18next";
import React, { useCallback, useEffect, useState, type ChangeEvent } from "react";

import AddSVGComponent from "/src/assets/svg/add/add-svgrepo-com.svg?react";

import { useAppContext, useCurrentCandidate } from "../../../hooks/context";
import CandidatesQueries from "../../../api/services/candidate/queries";


import type { Skill } from "../../../features/shared/global";

import CandidateServices from "../../../api/services/candidate/command";
import type { CandidateProfile } from "../../../features/candidates/candidates";


import styles from "./CandidateProfilPage.module.css";
import { ResourceNotFound, ResumeDeletionNotAllowedException } from "../../../api/services/exceptions";



const CandidateProfilPage: React.FC = () => {
  const { t } = useTranslation();
  const user = useCurrentCandidate();
  const { avatarUrl, setAvatarUrl, setPopup, setLoading } = useAppContext();

  const [isEditing, setIsEditing] = useState<boolean>(false);
  const [candidate, setCandidate] = useState<CandidateProfile | null>(null);

  const [newSkillName, setNewSkillName] = useState<string>("");
  const [resumes, setResumes] = useState<Array<{  fileId?: string; name: string; url: string }>>([]);


  //--initialize data
  useEffect(()=>{
    const fetchData = async ()=>{
      try{
        const skills = await CandidatesQueries.getCandidateSkills();
        const desc = await CandidatesQueries.getCandidateDescription();

        setCandidate(()=>({
          id: user.id,
          lastName: user.lastName,
          firstName: user.firstName,
          email: user.email,
          location: {
            street: user.location?.street ?? "",
            country: user.location?.country ?? "",
            city: user.location?.city ?? "",
            postalCode: user.location?.postalCode ?? ""
          },

          description: desc ?? "",
          skills: skills ?? [],
        }));

        //-- Resumes
        const resumesMetaData = await CandidatesQueries.getResumes();
        
        const resume = [];

        for (const data of resumesMetaData) {
          if(!data.fileId)
            continue;
          const content = await CandidatesQueries.getResumeContent(data.fileId);

          resume.push({
            id: data.id,
            fileId: data.fileId,
            name: data.originalName ?? data.name,
            url: URL.createObjectURL(content),
          });
        }

        setResumes(resume);
      }
      catch(error){
        console.warn("Something went wrong : ", error)
      }
    }

    fetchData();

  },[user])


  
  useEffect(() => {
    return () => {
      resumes.forEach((file) => URL.revokeObjectURL(file.url));
    };
  }, [ resumes]);



  // Handler champs racines
  const handleInputChange = (e: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setCandidate((prev) => {
      if(!prev) return prev;
      return ({ ...prev, [name]: value });
    });
  };


  // Handler candidate location
  const handleLocationChange = (e: ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setCandidate((prev) => {
      if(!prev) return prev;
      return ({
        ...prev,
        location: {
          ...prev.location,
          [name]: value,
        },
      })
    });
  };


  const handleAvatarChange = (e: ChangeEvent<HTMLInputElement>) => {
    if (e.target.files && e.target.files[0]) {
      const file = e.target.files[0];
      if (avatarUrl) 
        URL.revokeObjectURL(avatarUrl);
      setCandidate((prev)=>{
        if(!prev) return prev;
        return ({...prev, image: file})
      })
      setAvatarUrl(URL.createObjectURL(file));
    }
  };


  const handleResumeUpload = async (e: ChangeEvent<HTMLInputElement>) => {
    try{
      if (e.target.files && e.target.files[0]) {
        const file = e.target.files[0];

        const result = await CandidateServices.uploadResume(file);

        const newFile = {
          fileId: result.fileId,
          name: file.name,
          url: URL.createObjectURL(file),
        };
        
        setResumes((prev) => [...prev, newFile]);
    }
    }
    catch(error){
      console.log("Something went wrong while uploading resume", error)
    }
  };



  const handleRemoveResume = async (fileId?: string) => {
    try{
      if(!fileId)
          return;
      await CandidateServices.removeResume(fileId);

      setResumes((prev) => {
        const target = prev.find((item) => item.fileId === fileId);
        if (target) 
          URL.revokeObjectURL(target.url);
        return prev.filter((item) => item.fileId !== fileId);
      });
    }
    catch(error){
      if(error instanceof ResumeDeletionNotAllowedException){
        setPopup({status: "error", message: t("candidate.apiResonse.error.resumes.likelyInUseByApplication")})
      }
      else{
        setPopup({
          message: t('candidate.apiResonse.error.resumes.failedToDeleteResume'),
          status: 'error'
        });
      }
      console.warn("Something went wrong", error)
    }
  };


  const handleAddSkill = () => {
    if (!newSkillName.trim()) return;
    const newSkill: Skill = {
      id: `sk-${Date.now()}`,
      name: newSkillName.trim(),
    };
    setCandidate((prev) => {
      if(!prev) return prev;
      return  ({ ...prev, skills: [...prev.skills, newSkill] });
    });
    setNewSkillName("");
  };


  const handleRemoveSkill = (skillId: string) => {
    setCandidate((prev) =>{
      if(!prev) return prev;
      return  ({
        ...prev,
        skills: prev.skills.filter((sk) => sk.id !== skillId),
      });
    });
  };


  const handleSave = async () => {
    if (!candidate) 
      return;
    setLoading({ state: true })
    try {
      await CandidateServices.updateCandidateProfileDetails(candidate);
      setIsEditing(false);
    }
    catch (error) {
      setPopup({status: 'error' ,message: t("global.apiResponse.error.failedChangeProfil")});
      console.error("Something went wrong :", error);
    }
    finally{
      setLoading({ state: false })
    }
  };
      

  //-- Protection against null value
  if(!candidate) 
    return null;

  return (
    <div className={styles.container}>
      <div className={styles.headerAction}>
        <h2>Profil Candidat</h2>
        <button 
          className={isEditing ? styles.saveBtn : styles.editBtn} 
          onClick={() =>{
            if(isEditing){
              handleSave();
            }
            setIsEditing(!isEditing)
          }}
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
              <div key={resume.fileId} className={styles.resumeItem}>
                <a href={resume.url} target="_blank" rel="noopener noreferrer" className={styles.resumeLink}>
                  📄 {resume.name}
                </a>
                <button 
                  type="button" 
                  onClick={() => handleRemoveResume(resume.fileId)} 
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