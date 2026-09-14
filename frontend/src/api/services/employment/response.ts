import type {  CandidateEmploymentOffer, EmploymentOfferStats, RecruiterEmploymentOffer,  } from "../../../features/employment/offer";
import type { ApiResponse } from "../response.types";

/**
 * ---------------
 *  Response 
 * --------------
 * */

export type CandidateEmploymentOffersStats = ApiResponse<EmploymentOfferStats>; //-- Stats data
export type CandidateEmploymentOfferResponse  = ApiResponse<{
  data: CandidateEmploymentOffer[];
  total: number;
}>; //-- employment offer collection for candidate

/**For recruiter */
export type EmploymentOfferQueryResponse = ApiResponse<RecruiterEmploymentOffer[]>; 
export type EmploymentSavedResponse = ApiResponse<EmploymentSaved>;

/**
 * -------------
 *  Supports 
 * -----------
 * */


export interface EmploymentSaved {
  id: string,
  createdAt: string
}
