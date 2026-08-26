import type {  EmploymentOfferStatus } from "../../../features/employment/offer";
import type { ApiResponse } from "../response.types";

/** Response */
export type EmploymentOfferQueryResponse = ApiResponse<EmploymentOfferQueryData[]>;
export type EmploymentSavedResponse = ApiResponse<EmploymentSaved>;

/** Supports */
export type EmploymentOfferQueryData = {
  id: string;
  status: EmploymentOfferStatus;
  sentAt: string;
  expiredAt: string;
  salary: number;
  candidate: {
    id: string;
    firstName: string;
    lastName: string;
    email: string;
    image: string //url
  };
  application: {
    id: string;
  };
  jobOffer:{
    id: string;
    title: string;
  }
};


export interface EmploymentSaved {
  id: string,
  createdAt: string
}