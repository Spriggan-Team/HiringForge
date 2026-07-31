import type { ApiResponse } from "../response.types";

export type SearchSkillApiResponse = ApiResponse<LightWeightSkill[]>;

interface LightWeightSkill{
    id: string;
    name: string;
}