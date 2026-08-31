import type { ApiResponse } from "../response.types";



export type RecruitmentChartDataResponse  = ApiResponse<RecruitmentChartData>

interface RecruitmentChartData {
    applications: number[];
    interviews: number[];
    hires: number[];
                
    applicationCount: number;
    interviewsCount: number;
    hiredCandidateCount : number;
}