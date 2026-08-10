import type { ApiResponse } from "../response.types";


//-- Api Responses
export type PaginatedData<T> = {
  items: T[];
  total: number;
  limit: number;
  skip: number;
};


export type PublicJobOfferListResponse = ApiResponse<PaginatedData<PublicJobOfferLightModel>>;
export type PublicJobOfferDetailResponse = ApiResponse<PublicJobOfferDetailsModel>;

// --- Services Types ---

export type BasicPublicJobOfferModel = {
  id: string;
  title: string;
  content: unknown;
  jobWorkMode: string | null;
  salary: PublicJobOfferSalary;
  contractType: PublicJobOfferContractType | null;
  location: PublicJobOfferLocation | null;
};

export type PublicJobOfferLightModel = BasicPublicJobOfferModel & {
  mainImage: string | null;
};

export type PublicJobOfferDetailsModel = BasicPublicJobOfferModel & {
  expertise: string | null;
  skills: PublicJobOfferSkill[];
  languages: PublicJobOfferLanguage[];
  images: string[];
};

export type PublicJobOfferSalary = {
  min: number | null;
  max: number | null;
  currency: string | null;
};

export type PublicJobOfferContractType = {
  id: number;
  label: string;
};

export type PublicJobOfferLocation = {
  street: string | null;
  postalCode: string | null;
  city: string | null;
  country: string | null;
};

export type PublicJobOfferSkill = {
  id: string;
  name: string;
  isRequired: boolean;
};

export type PublicJobOfferLanguage = {
  id: number;
  name: string;
  level: string;
};