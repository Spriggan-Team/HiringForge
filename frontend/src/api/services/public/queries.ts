import { get } from "../../http";
import type { PublicJobOfferDetailResponse , PublicJobOfferListResponse  } from "./responses";



export type FetchJobsParams = {
  search?: string;
  address?: string;
  limit?: number;
  skip?: number;
  locale?: string;
};


const getPublicJobs = async (params: FetchJobsParams = {}) => {
  const queryParams = new URLSearchParams();

  if (params.search) queryParams.append("search", params.search);
  if (params.address) queryParams.append("address", params.address);
  if (params.limit) queryParams.append("limit", params.limit.toString());
  if (params.skip !== undefined) queryParams.append("skip", params.skip.toString());
  if (params.locale) queryParams.append("locale", params.locale);

  const response = await get<PublicJobOfferListResponse>(`/public/job_offers?${queryParams.toString()}`);
  return response.data; // Return { items, total, limit, skip }
};



const getDetailsAboutPublicJob = async (jobId: string) => {
  const response = await get<PublicJobOfferDetailResponse>(`/public/job_offers/${jobId}`);
  return response.data;
};



const PublicJobQueries = {
  getPublicJobs,
  getDetailsAboutPublicJob,
};



export default PublicJobQueries;