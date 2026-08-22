import type { JobLanguage, JobView } from "../../../features/jobs/JobOffer";
import type { JobOfferViewDataResponse } from "./response";



export const mapJobOfferViewToJobView = (dto: JobOfferViewDataResponse): JobView => {
  return {
    // --- PublicJobView Fields ---
    id: dto.id,
    title: dto.title,
    content: dto.content ?? {},
    
    skills: (dto.skills ?? []).map(skill => ({
      id: skill.id,
      name: skill.name,
    })),
    
    categories: [], // Catgories

    salary: dto.salary ? {
      devise: dto.salary.devise ?? 'EUR',
      min: dto.salary.min ?? 0,
      max: dto.salary.max ?? 0,
      fix: 0,
    } : undefined,

    contract: dto.contract ? {
      id: dto.contract.id ?? '',
      label: dto.contract.label ?? '',
    } : undefined,

    // Transform Language
    requireLanguages: (dto.languages ?? []).map((lang, index): JobLanguage => ({
      id: index + 1,
      code: lang.label.substring(0, 2).toLowerCase(),
      nativeLabel: lang.label,
      proficiencyLevel: lang.level,
    })),

    location: dto.location ? {
      id: dto.location.id,
      city: dto.location.city ?? '',
      street: dto.location.street ?? '',
      country: dto.location.country ?? '',
    } : undefined,

    mainImage: dto.mainImage ?? undefined,
    jobWorkMode: dto.jobWorkMode,

    createdAt: dto.createdAt ? new Date(dto.createdAt) : undefined,
    updatedAt: dto.updatedAt ? new Date(dto.updatedAt) : undefined,

    // --- RecruiterJobView Fields ---
    activityStatus: dto.activityStatus ?? 'active', // Valeur par défaut / calculée si absente de la projection
    publicationStatus: dto.publicationStatus ?? 'published',
    visibilityStatus: dto.visibilityStatus ?? 'public',

    views: dto.viewsCount ?? 0,
    applications: 0,
    
    department: dto.department ? {
      id: typeof dto.department.id === 'number' ? dto.department.id : undefined,
      label: dto.department.label ?? '',
    } : null,

    cardinal: {
      candidates: 0,
      interviews: 0,
      offers: 0,
      hired: 0,
    },
  };
};