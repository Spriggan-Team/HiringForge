import React, { useEffect, useRef, useState } from 'react';

//-- Services & types
import { useAppContext } from '../../../../../hooks/context';
import type {
    UserProfileData,
    EditedProfileData,
    ImageResoruce,
} from '../../../../../features/users/user.profile';
import UserServices from '../../../../../api/services/user/commend';

//-- Custom components
import MediaUploader from '../../../../components/uploader/media.uploader';
import { CardPlaceholder } from '../../../../../layout/components/cards/placeholder/card.placeholder';
import Galery from '../../../../../layout/components/cards/galery/galery';
import ActionCard from '../../../../../layout/components/cards/action.card';
import TextPlaceholder from '../../../../../layout/components/cards/placeholder/text.placeholder';

//-- SVG Components
import CameraSVG from "/src/assets/svg/images/camera-add-svgrepo-com.svg?react";
import GalerySVG from '/src/assets/svg/images/directory-image-1626-svgrepo-com.svg?react';
import SiretSVG from '/src/assets/svg/person/diaspora-svgrepo-com.svg?react';
import DepartmentSVG from '/src/assets/svg/menu/departments-svgrepo-com.svg?react';
import LocationSVG from "/src/assets/svg/location/location-svgrepo-com.svg?react";
import EmailSVG from '/src/assets/svg/email/email-1-svgrepo-com.svg?react';

//-- Styles
import styles from './ProfileViewPage.module.css';


interface ProfileViewPageProps {
    data: UserProfileData;
    isEditing: boolean;
    setIsEditing: React.Dispatch<React.SetStateAction<boolean>>;
    /** Appelé après une sauvegarde réussie (ex: pour re-fetch les données côté parent) */
    onSaved?: () => void;
}

type CurrentDetailsView = "galery" | "location" | "department";

type LocalDepartment = { id?: number; name: string };
type LocalLocation = { id?: number; country: string; street: string; postalCode: string; city: string };
type LocationFormState = { country: string; street: string; postalCode: string; city: string };

const emptyLocationForm: LocationFormState = { country: '', street: '', postalCode: '', city: '' };


/**
 *---------------------
 * Componenent
 * ------------------
 */

const ProfilePage: React.FC<ProfileViewPageProps> = ({
    data,
    isEditing,
    setIsEditing,
    onSaved,
}) => {
    //--App context
    const { setPopup, setLoading } = useAppContext();

    //-- Payload envoyé à l'API
    const [editedData, setEditedData] = useState<EditedProfileData>({});

    //-- UI/View state
    const [currentDetailsView, setCurrentDetailsView] = useState<CurrentDetailsView>('galery');

    //-- Champs texte "société"
    const [companyName, setCompanyName] = useState(data.company.name);
    const [companySiret, setCompanySiret] = useState(data.company.siret);
    const [companyDescription, setCompanyDescription] = useState(data.company.description);

    //-- Champs texte "utilisateur"
    const [userFirstName, setUserFirstName] = useState(data.user.firstName);
    const [userLastName, setUserLastName] = useState(data.user.lastName);
    const [userEmail, setUserEmail] = useState(data.user.email);
    const [userDescription, setUserDescription] = useState(data.user.description);

    //-- Vidéo
    const videoUrl = data.company.videoPresentation?.url;
    const [videoStatus, setVideoStatus] = useState<"loading" | "loaded" | "error">(videoUrl ? "loading" : "error");
    const [videoPreviewUrl, setVideoPreviewUrl] = useState<string | undefined>(videoUrl);
    const [videoRemoved, setVideoRemoved] = useState(false);

    //-- Logo
    const [logoPreviewUrl, setLogoPreviewUrl] = useState<string | undefined>(data.company.logo?.url);
    const [logoRemoved, setLogoRemoved] = useState(false);

    //-- Avatar
    const [avatarPreviewUrl, setAvatarPreviewUrl] = useState<string | undefined>(data.user.image?.url);
    const [avatarRemoved, setAvatarRemoved] = useState(false);

    //-- Image d'en-tête
    const [mainImage, setMainImage] = useState<{ file?: File; url?: string }>();

    //-- Galerie
    const [images, setImages] = useState<ImageResoruce[]>([
        ...(data.company.images.others ?? []),
        ...(data.company.images.main ? [data.company.images.main] : []),
    ]);

    //-- Départements
    const [addedDepartments, setAddedDepartments] = useState<LocalDepartment[]>([]);
    const [removedDepartmentIds, setRemovedDepartmentIds] = useState<number[]>([]);
    const [newDepartmentName, setNewDepartmentName] = useState('');

    //-- Localisations
    const [addedLocations, setAddedLocations] = useState<LocalLocation[]>([]);
    const [removedLocationIds, setRemovedLocationIds] = useState<number[]>([]);
    const [locationForm, setLocationForm] = useState<LocationFormState>(emptyLocationForm);
    const [locationFormError, setLocationFormError] = useState<string | null>(null);

    //-- Refs
    const mainImageInputRef = useRef<HTMLInputElement | null>(null);
    const logoInputRef = useRef<HTMLInputElement | null>(null);
    const avatarInputRef = useRef<HTMLInputElement | null>(null);
    const videoInputRef = useRef<HTMLInputElement | null>(null);

    //-- Réinitialise tous les states locaux à partir des données serveur (mount + annulation)
    const resetFromData = (source: UserProfileData) => {
        setEditedData({});

        setCompanyName(source.company.name);
        setCompanySiret(source.company.siret);
        setCompanyDescription(source.company.description);

        setUserFirstName(source.user.firstName);
        setUserLastName(source.user.lastName);
        setUserEmail(source.user.email);
        setUserDescription(source.user.description);

        setVideoPreviewUrl(source.company.videoPresentation?.url);
        setVideoRemoved(false);

        setLogoPreviewUrl(source.company.logo?.url);
        setLogoRemoved(false);

        setAvatarPreviewUrl(source.user.image?.url);
        setAvatarRemoved(false);

        setMainImage({ url: source.company.images.main?.url ?? source.company.images.others[0]?.url });

        setImages([
            ...(source.company.images.others ?? []),
            ...(source.company.images.main ? [source.company.images.main] : []),
        ]);

        setAddedDepartments([]);
        setRemovedDepartmentIds([]);
        setNewDepartmentName('');

        setAddedLocations([]);
        setRemovedLocationIds([]);
        setLocationForm(emptyLocationForm);
        setLocationFormError(null);
    };

    useEffect(() => {
        resetFromData(data);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [data]);

    useEffect(() => {
        setVideoStatus(videoUrl ? "loading" : "error");
    }, [videoUrl]);

    //-- Listes affichées = données serveur (moins les suppressions) + ajouts locaux
    const displayedDepartments: LocalDepartment[] = [
        ...(data.company.departments ?? []).filter((department) => !removedDepartmentIds.includes(department.id)),
        ...addedDepartments,
    ];

    const displayedLocations: LocalLocation[] = [
        ...(data.company.location ?? []).filter((location) => !removedLocationIds.includes(location.id)),
        ...addedLocations,
    ];

    //-- Synchronise editedData.company.departments avec les states granulaires
    useEffect(() => {
        setEditedData((prev) => ({
            ...prev,
            company: {
                ...(prev.company ?? {}),
                departments: (addedDepartments.length || removedDepartmentIds.length)
                    ? {
                        added: addedDepartments.length ? addedDepartments.map((d) => ({ name: d.name })) : undefined,
                        removed: removedDepartmentIds.length ? removedDepartmentIds.map((id) => ({ id })) : undefined,
                    }
                    : undefined,
            },
        }));
    }, [addedDepartments, removedDepartmentIds]);

    //-- Synchronise editedData.company.location avec les states granulaires
    useEffect(() => {
        setEditedData((prev) => ({
            ...prev,
            company: {
                ...(prev.company ?? {}),
                location: (addedLocations.length || removedLocationIds.length)
                    ? {
                        added: addedLocations.length
                            ? addedLocations.map(({ country, street, postalCode, city }) => ({ country, street, postalCode, city }))
                            : undefined,
                        removed: removedLocationIds.length ? removedLocationIds.map((id) => ({ id })) : undefined,
                    }
                    : undefined,
            },
        }));
    }, [addedLocations, removedLocationIds]);


    //---------------------------------------------
    //-- Handlers: Text Fields
    //---------------------------------------------
    
    const handleCompanyNameChange = (value: string) => {
        setCompanyName(value);
        setEditedData((prev) => ({ ...prev, company: { ...(prev.company ?? {}), name: value } }));
    };

    const handleCompanySiretChange = (value: string) => {
        setCompanySiret(value);
        setEditedData((prev) => ({ ...prev, company: { ...(prev.company ?? {}), siret: value } }));
    };

    const handleCompanyDescriptionChange = (value: string) => {
        setCompanyDescription(value);
        setEditedData((prev) => ({ ...prev, company: { ...(prev.company ?? {}), description: value } }));
    };

    const handleUserFirstNameChange = (value: string) => {
        setUserFirstName(value);
        setEditedData((prev) => ({ ...prev, user: { ...(prev.user ?? {}), firstName: value } }));
    };

    const handleUserLastNameChange = (value: string) => {
        setUserLastName(value);
        setEditedData((prev) => ({ ...prev, user: { ...(prev.user ?? {}), lastName: value } }));
    };

    const handleUserEmailChange = (value: string) => {
        setUserEmail(value);
        setEditedData((prev) => ({ ...prev, user: { ...(prev.user ?? {}), email: value } }));
    };

    const handleUserDescriptionChange = (value: string) => {
        setUserDescription(value);
        setEditedData((prev) => ({ ...prev, user: { ...(prev.user ?? {}), description: value } }));
    };


    //---------------------------------------------
    //-- Handlers: main image
    //---------------------------------------------
    const handleMainImageChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        if (file) {
            if (mainImage?.file && mainImage.url) {
                URL.revokeObjectURL(mainImage.url);
            }
            const url = URL.createObjectURL(file);
            setMainImage((prev) => ({ ...prev, file, url }));

            setEditedData((prev) => {
                const previousImages = prev.company?.images;
                const otherAdded = (previousImages?.added ?? []).filter((img) => img.isMain !== true);
                return {
                    ...prev,
                    company: {
                        ...(prev.company ?? {}),
                        images: {
                            ...(previousImages ?? {}),
                            added: [...otherAdded, { isMain: true, file }],
                        },
                    },
                };
            });
        }
        event.target.value = "";
    };


    //---------------------------------------------
    //-- Handlers: logo
    //---------------------------------------------
    const handleLogoChange = (file: File | null) => {
        if (!file) {
            setLogoRemoved(true);
            setLogoPreviewUrl(undefined);
            setEditedData((prev) => ({
                ...prev,
                company: { ...(prev.company ?? {}), logo: data.company.logo ? null : undefined },
            }));
            return;
        }

        setLogoRemoved(false);
        const url = URL.createObjectURL(file);
        setLogoPreviewUrl(url);
        setEditedData((prev) => ({
            ...prev,
            company: { ...(prev.company ?? {}), logo: { file } },
        }));
    };

    const handleRemoveLogo = () => handleLogoChange(null);


    //---------------------------------------------
    //-- Handlers: avatar
    //---------------------------------------------
    const handleAvatarChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        if (file) {
            setAvatarRemoved(false);
            const url = URL.createObjectURL(file);
            setAvatarPreviewUrl(url);
            setEditedData((prev) => ({
                ...prev,
                user: { ...(prev.user ?? {}), image: { file } },
            }));
        }
        event.target.value = "";
    };

    const handleRemoveAvatar = () => {
        setAvatarRemoved(true);
        setAvatarPreviewUrl(undefined);
        setEditedData((prev) => ({
            ...prev,
            user: { ...(prev.user ?? {}), image: data.user.image ? null : undefined },
        }));
    };


    //---------------------------------------------
    //-- Handlers: video presentation
    //---------------------------------------------
    const handleVideoChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        if (file) {
            setVideoRemoved(false);
            const url = URL.createObjectURL(file);
            setVideoPreviewUrl(url);
            setVideoStatus("loading");
            setEditedData((prev) => ({
                ...prev,
                company: { ...(prev.company ?? {}), videoPresentation: { file } },
            }));
        }
        event.target.value = "";
    };

    const handleRemoveVideo = () => {
        setVideoRemoved(true);
        setVideoPreviewUrl(undefined);
        setEditedData((prev) => ({
            ...prev,
            company: { ...(prev.company ?? {}), videoPresentation: data.company.videoPresentation ? null : undefined },
        }));
    };


    //---------------------------------------------
    //-- Handlers: galerie
    //---------------------------------------------
    const handleAddGalleryImage = (file: File) => {
        setEditedData((prev) => ({
            ...prev,
            company: {
                ...(prev.company ?? {}),
                images: {
                    ...(prev.company?.images ?? {}),
                    added: [...(prev.company?.images?.added ?? []), { file, isMain: false }],
                },
            },
        }));
    };

    const handleRemoveGalleryImage = (id: number) => {
        setEditedData((prev) => ({
            ...prev,
            company: {
                ...(prev.company ?? {}),
                images: {
                    ...(prev.company?.images ?? {}),
                    removed: [...(prev.company?.images?.removed ?? []), { id }],
                },
            },
        }));
    };


    //---------------------------------------------
    //-- Handlers: départements
    //---------------------------------------------
    const handleAddDepartment = () => {
        const name = newDepartmentName.trim();
        if (!name) return;
        setAddedDepartments((prev) => [...prev, { name }]);
        setNewDepartmentName('');
    };

    const handleRemoveDepartment = (department: LocalDepartment) => {
        if (department.id !== undefined) {
            setRemovedDepartmentIds((prev) => [...prev, department.id as number]);
        } else {
            setAddedDepartments((prev) => prev.filter((d) => d.name !== department.name));
        }
    };


    //---------------------------------------------
    //-- Handlers: localisations
    //---------------------------------------------
    const handleAddLocation = () => {
        const { country, street, postalCode, city } = locationForm;
        if (!country.trim() || !street.trim() || !postalCode.trim() || !city.trim()) {
            setLocationFormError("Merci de renseigner le pays, la rue, le code postal et la ville.");
            return;
        }
        setAddedLocations((prev) => [...prev, {
            country: country.trim(),
            street: street.trim(),
            postalCode: postalCode.trim(),
            city: city.trim(),
        }]);
        setLocationForm(emptyLocationForm);
        setLocationFormError(null);
    };

    const handleRemoveLocation = (location: LocalLocation) => {
        if (location.id !== undefined) {
            setRemovedLocationIds((prev) => [...prev, location.id as number]);
        } else {
            setAddedLocations((prev) => prev.filter((l) => l !== location));
        }
    };


    //---------------------------------------------
    //-- Saving / Cancelling
    //---------------------------------------------
    const handleSaveProfilData = async () => {
        try {
            setLoading({ state: true, subtitle: "Modification des informations du profil" });
            await UserServices.changeProfilData(editedData);
            setPopup({ status: 'success', message: "Profil modifié avec succès" });
            setIsEditing(false);
            onSaved?.();
        }
        catch (error) {
            console.warn("Something went wrong while changing profil data : ", error);
            setPopup({ status: 'error', message: "Une erreur est survenue lors de la modification des informations du profil" });
        } finally {
            setLoading({ state: false });
        }
    };

    const handleCancelEdit = () => {
        resetFromData(data);
        setIsEditing(false);
    };


    return (
        <div className={styles.container}>
            <div className={styles.mainImageContainer}>
                {/** HEADER IMAGE */}
                <img src={mainImage?.url} alt="Main image" />
                {isEditing && (
                    <button
                        type="button"
                        onClick={() => mainImageInputRef.current?.click()}
                        className={styles.button}
                    >
                        <CameraSVG className={styles.svg} height={15} width={15} />
                        <span>Modifier l'image</span>
                    </button>
                )}
                <input
                    type="file"
                    name="mainImage"
                    accept="image/*"
                    onChange={handleMainImageChange}
                    ref={mainImageInputRef}
                    className={styles.mainImageInput}
                />
            </div>

            {/** CONTENT */}
            <div className={styles.content}>
                {/** PRESENTATION */}
                <div className={styles.presentation}>
                    <div className={styles.left}>
                        <div className={styles.logoContainer}>
                            {logoPreviewUrl && !logoRemoved ? (
                                <div className={styles.imageWrapper}>
                                    <img
                                        src={logoPreviewUrl}
                                        alt="Logo"
                                        style={{ width: '80px', height: '80px', objectFit: 'cover', borderRadius: '8px' }}
                                    />
                                    {isEditing && (
                                        <div className={styles.imageEditOverlay}>
                                            <button type="button" onClick={() => logoInputRef.current?.click()} className={styles.overlayButton} aria-label="Changer le logo">
                                                <CameraSVG height={13} width={13} />
                                            </button>
                                            <button type="button" onClick={handleRemoveLogo} className={styles.overlayRemoveButton} aria-label="Supprimer le logo">
                                                ×
                                            </button>
                                        </div>
                                    )}
                                </div>
                            ) : isEditing ? (
                                <div className={styles.logoPlaceholder}>
                                    <MediaUploader
                                        leadingText=""
                                        file={null}
                                        className={styles.mediaUploader}
                                        onFileChange={handleLogoChange}
                                    />
                                </div>
                            ) : (
                                <CardPlaceholder text="Aucun logo" />
                            )}
                            <input
                                type="file"
                                accept="image/*"
                                ref={logoInputRef}
                                className={styles.hiddenInput}
                                onChange={(event) => {
                                    const file = event.target.files?.[0];
                                    if (file) handleLogoChange(file);
                                    event.target.value = "";
                                }}
                            />
                        </div>
                        <div className={styles.text}>
                            {isEditing ? (
                                <input
                                    className={`${styles.name} ${styles.input}`}
                                    value={companyName}
                                    onChange={(event) => handleCompanyNameChange(event.target.value)}
                                    placeholder="Nom de l'entreprise"
                                />
                            ) : (
                                <span className={styles.name}>{companyName}</span>
                            )}
                        </div>
                    </div>


                    {/** VIDEO */}
                    <div className={styles.videoContainer}>
                        {videoPreviewUrl && !videoRemoved && videoStatus !== "error" && videoStatus !== "loading" && (
                            <video
                                key={videoPreviewUrl}
                                src={videoPreviewUrl}
                                controls
                                onCanPlay={() => setVideoStatus("loaded")}
                                onError={() => setVideoStatus("error")}
                                className={`${styles.video} ${
                                    videoStatus !== "loaded" ? styles.videoHidden : ""
                                }`}
                            />
                        )}

                        {(videoStatus !== "loaded" || videoRemoved || !videoPreviewUrl) && (
                            <CardPlaceholder text="Vidéo de présentation" />
                        )}

                        {isEditing && (
                            <div className={styles.videoEditActions}>
                                <button type="button" className={styles.smallActionButton} onClick={() => videoInputRef.current?.click()}>
                                    {videoPreviewUrl && !videoRemoved ? "Changer la vidéo" : "Ajouter une vidéo"}
                                </button>
                                {videoPreviewUrl && !videoRemoved && (
                                    <button type="button" className={styles.smallActionButtonDanger} onClick={handleRemoveVideo}>
                                        Supprimer
                                    </button>
                                )}
                            </div>
                        )}
                        <input
                            type="file"
                            accept="video/*"
                            ref={videoInputRef}
                            className={styles.hiddenInput}
                            onChange={handleVideoChange}
                        />
                    </div>
                </div>

                {/** TAGS SECTION */}
                <div className={styles.tagsSection}>
                    {/** GALERY */}
                    <div
                        className={`${styles.tag} ${currentDetailsView === 'galery' ? styles.activeTag : ''}`}
                        onClick={() => setCurrentDetailsView('galery')}
                    >
                        <GalerySVG className={styles.svg} height={18} width={18} />
                        <div>
                            <span className={styles.title}>Galerie</span>
                            <span className={styles.value}>{images.length}</span>
                        </div>
                    </div>

                    {/** SIRET */}
                    <div className={styles.tag}>
                        <SiretSVG className={styles.svg} height={18} width={18} />
                        <div>
                            <span className={styles.title}>SIRET</span>
                            {isEditing ? (
                                <input
                                    className={`${styles.value} ${styles.inlineInput} ${styles.input}`}
                                    value={companySiret}
                                    onChange={(event) => handleCompanySiretChange(event.target.value)}
                                    placeholder="Numéro de SIRET"
                                />
                            ) : (
                                <span className={styles.value}>{companySiret || '—'}</span>
                            )}
                        </div>
                    </div>

                    {/** DEPARTMENT */}
                    <div
                        className={`${styles.tag} ${currentDetailsView === 'department' ? styles.activeTag : ''}`}
                        onClick={() => setCurrentDetailsView('department')}
                    >
                        <DepartmentSVG className={styles.svg} width={18} height={18} />
                        <div>
                            <span className={styles.title}>Départements</span>
                            <span className={styles.value}>{displayedDepartments.length}</span>
                        </div>
                    </div>

                    {/** LOCATION */}
                    <div
                        className={`${styles.tag} ${currentDetailsView === 'location' ? styles.activeTag : ''}`}
                        onClick={() => setCurrentDetailsView('location')}
                    >
                        <LocationSVG className={styles.svg} width={18} height={18} />
                        <div>
                            <span className={styles.title}>Localisations</span>
                            <span className={styles.value}>{displayedLocations.length}</span>
                        </div>
                    </div>
                </div>

                {/** Company Details */}
                <div className={styles.companyDetails}>
                    {currentDetailsView === "galery" && (
                        <div className={styles.images}>
                            <Galery
                                isEditable={isEditing}
                                className={styles.galery}
                                images={images}
                                setImages={setImages}
                                onAdd={handleAddGalleryImage}
                                onDelete={handleRemoveGalleryImage}
                            />
                        </div>
                    )}

                    {currentDetailsView === "department" && (
                        <div className={styles.departments}>
                            <span className={styles.title}>Départements</span>

                            {isEditing && (
                                <div className={styles.addForm}>
                                    <input
                                        className={styles.input}
                                        value={newDepartmentName}
                                        onChange={(event) => setNewDepartmentName(event.target.value)}
                                        placeholder="Nom du département"
                                        onKeyDown={(event) => {
                                            if (event.key === 'Enter') {
                                                event.preventDefault();
                                                handleAddDepartment();
                                            }
                                        }}
                                    />
                                    <button type="button" className={styles.addButton} onClick={handleAddDepartment}>
                                        Ajouter
                                    </button>
                                </div>
                            )}

                            {displayedDepartments.length === 0 ? (
                                <CardPlaceholder text="Aucun département" />
                            ) : (
                                <div className={styles.cardsGrid}>
                                    {displayedDepartments.map((department, index) => (
                                        <ActionCard
                                            isEditable={isEditing}
                                            className={styles.actionCard}
                                            key={department.id ?? `new-department-${index}`}
                                            text={department.name}
                                            onDelete={() => handleRemoveDepartment(department)}
                                        />
                                    ))}
                                </div>
                            )}
                        </div>
                    )}

                    {/** LOCATIONS */}
                    {currentDetailsView === 'location' && (
                        <div className={styles.locations}>
                            <span className={styles.title}>Localisations</span>

                            {isEditing && (
                                <div className={styles.addForm}>
                                    <div className={styles.addFormGrid}>
                                        <input
                                            className={styles.input}
                                            placeholder="Pays"
                                            value={locationForm.country}
                                            onChange={(event) => setLocationForm((prev) => ({ ...prev, country: event.target.value }))}
                                        />
                                        <input
                                            className={styles.input}
                                            placeholder="Rue"
                                            value={locationForm.street}
                                            onChange={(event) => setLocationForm((prev) => ({ ...prev, street: event.target.value }))}
                                        />
                                        <input
                                            className={styles.input}
                                            placeholder="Code postal"
                                            value={locationForm.postalCode}
                                            onChange={(event) => setLocationForm((prev) => ({ ...prev, postalCode: event.target.value }))}
                                        />
                                        <input
                                            className={styles.input}
                                            placeholder="Ville"
                                            value={locationForm.city}
                                            onChange={(event) => setLocationForm((prev) => ({ ...prev, city: event.target.value }))}
                                        />
                                    </div>
                                    {locationFormError && <span className={styles.formError}>{locationFormError}</span>}
                                    <button type="button" className={styles.addButton} onClick={handleAddLocation}>
                                        Ajouter une localisation
                                    </button>
                                </div>
                            )}

                            {displayedLocations.length === 0 ? (
                                <CardPlaceholder text="Aucune localisation" />
                            ) : (
                                <div className={styles.cardsGrid}>
                                    {displayedLocations.map((loc, index) => {
                                        const normalized = [
                                            loc.country,
                                            loc.city,
                                            loc.street,
                                            loc.postalCode,
                                        ].filter(Boolean).join(', ');

                                        return (
                                            <ActionCard
                                                key={loc.id ?? `new-location-${index}`}
                                                text={normalized}
                                                isEditable={isEditing}
                                                className={styles.actionCard}
                                                url={`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(normalized)}`}
                                                onDelete={() => handleRemoveLocation(loc)}
                                            />
                                        );
                                    })}
                                </div>
                            )}
                        </div>
                    )}
                </div>

                {/** Company Description */}
                <div className={styles.companyDescription}>
                    <span className={styles.title}>À propos de nous</span>
                    {isEditing ? (
                        <textarea
                            className={styles.textarea}
                            value={companyDescription}
                            onChange={(event) => handleCompanyDescriptionChange(event.target.value)}
                            placeholder="Décrivez votre entreprise"
                            rows={4}
                        />
                    ) : companyDescription ? (
                        <p className={styles.desc}>{companyDescription}</p>
                    ) : (
                        <CardPlaceholder text='Aucune description' />
                    )}
                </div>

                {/** Recruiter */}
                <div className={styles.userProfile}>
                    <div className={styles.imageContainer}>
                        <div className={styles.imageWrapper}>
                            <img
                                alt="Avatar"
                                src={
                                    avatarPreviewUrl && !avatarRemoved
                                        ? avatarPreviewUrl
                                        : "/src/assets/images/pngtree-glitch-effect-avatar-profile-vector-png-image_15605578.png"
                                }
                            />
                            {isEditing && (
                                <div className={styles.imageEditOverlay}>
                                    <button type="button" onClick={() => avatarInputRef.current?.click()} className={styles.overlayButton} aria-label="Changer l'avatar">
                                        <CameraSVG height={13} width={13} />
                                    </button>
                                    {avatarPreviewUrl && !avatarRemoved && (
                                        <button type="button" onClick={handleRemoveAvatar} className={styles.overlayRemoveButton} aria-label="Supprimer l'avatar">
                                            ×
                                        </button>
                                    )}
                                </div>
                            )}
                        </div>
                        <input
                            type="file"
                            accept="image/*"
                            ref={avatarInputRef}
                            className={styles.hiddenInput}
                            onChange={handleAvatarChange}
                        />
                    </div>

                    <div className={styles.details}>
                        {isEditing ? (
                            <div className={styles.nameFields}>
                                <input className={styles.input} value={userFirstName} onChange={(e) => handleUserFirstNameChange(e.target.value)} placeholder="Prénom" />
                                <input className={styles.input} value={userLastName} onChange={(e) => handleUserLastNameChange(e.target.value)} placeholder="Nom" />
                            </div>
                        ) : (
                            <div>
                                <span className={styles.name}>{`${userFirstName} ${userLastName}`}</span>
                                <span className={styles.flag}>Recruteur</span>
                            </div>
                        )}
                        <div>
                            <EmailSVG height={14} width={14} className={styles.svg} />
                            {isEditing ? (
                                <input
                                    className={`${styles.email} ${styles.inlineInput} ${styles.input}`}
                                    value={userEmail}
                                    type="email"
                                    onChange={(e) => handleUserEmailChange(e.target.value)}
                                    placeholder="Email"
                                />
                            ) : (
                                <span className={styles.email}>{userEmail}</span>
                            )}
                        </div>
                        {isEditing ? (
                            <textarea
                                className={styles.textarea}
                                value={userDescription}
                                onChange={(e) => handleUserDescriptionChange(e.target.value)}
                                placeholder="Décrivez-vous en quelques mots"
                                rows={3}
                            />
                        ) : userDescription ? (
                            <p className={styles.desc}>{userDescription}</p>
                        ) : (
                            <TextPlaceholder />
                        )}
                    </div>
                </div>

                {/** ACTIONS */}
                <div className={styles.editActions}>
                    {isEditing ? (
                        <>
                            <button type="button" className={styles.cancelButton} onClick={handleCancelEdit}>
                                Annuler
                            </button>
                            <button type="button" className={styles.saveButton} onClick={handleSaveProfilData}>
                                Enregistrer
                            </button>
                        </>
                    ) : (
                        <button type="button" className={styles.modifyProfil} onClick={() => setIsEditing(true)}>
                            Modifier le profil
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
};

export default ProfilePage;