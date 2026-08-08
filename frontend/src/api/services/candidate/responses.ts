import type { CandidateLightModel } from "../../../features/candidates/candidates";
import type { ApiResponse } from "../response.types";


export type CandidateListResponse = ApiResponse<CandidateLightModel[]>;