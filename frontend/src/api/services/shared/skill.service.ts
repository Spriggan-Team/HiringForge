import { intercept } from "../../../utils/utils";
import { authGet, handleGenericApiResponseAfter } from "../../handler";

import type { ApiResponse } from "../response.types";
import type { SearchSkillApiResponse } from "./reponses.types";


const search = async (text: string, locale: string = "en")=>{
    try{
        const response = await authGet<SearchSkillApiResponse>(`/skills?text=${encodeURIComponent(text)}&locale=${encodeURIComponent(locale)}`);
        return response.data;
    }
    catch(error){
        throw error;
    }
}


const getExpertiseCollection = async ()=>{
    try{
        const response = await authGet<ApiResponse<string[]>>(`/skills/expertises`);
        return response.data;
    }
    catch(error){
        throw error;
    }
}


const SkillServices = intercept(
    { search, getExpertiseCollection },
    undefined,
    handleGenericApiResponseAfter
);

export default SkillServices;