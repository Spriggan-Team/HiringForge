import type { ApiResponse } from "../response.types";

import  { JobApplicationStatus } from "../../../features/application/application";
import  { InterviewType } from "../../../features/interviews/interviews";
import type { CandidateLightModel } from "../../../features/candidates/candidates";


export type SearchSkillApiResponse = ApiResponse<LightWeightSkill[]>;

interface LightWeightSkill{
    id: string;
    name: string;
}


//-- Pipeline

export const CandidatePipelineType = {
  APPLIED: JobApplicationStatus.APPLIED,
  HIRED: JobApplicationStatus.HIRED,
  RH_INTERVIEWS: InterviewType.RH_INTERVIEWS,
  TECHNICAL_INTERVIEWS: InterviewType.TECHNICAL_INTERVIEWS
} as const;

export type CandidatePipelineTypeValue = (typeof CandidatePipelineType)[keyof typeof CandidatePipelineType];


export const CANDIDATE_PIPELINE_TYPES = Object.values(
  CandidatePipelineType
) as CandidatePipelineTypeValue[];



export interface CandidatePipelineItem {
  more: number;
  stageType: CandidatePipelineTypeValue;
  candidates: (CandidateLightModel & {
    delayInSec: number;
  })[];
}


export type Timeframe = 'week' | 'month' | 'year';
