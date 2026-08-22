import { intercept } from "../../../utils/utils";
import { handleGenericApiResponseAfter } from "../../api-response-handler";
import { authGet } from "../../http";

import type { ApiResponse, ErrorApiResponse } from "../response.types";
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



const Queries = { 
    search, 
    getExpertiseCollection
};
const SkillServices = intercept<
    typeof Queries,
    ApiResponse |ErrorApiResponse
>(
    Queries,
    undefined,
    (method, response) => handleGenericApiResponseAfter(method, response)
);

export default SkillServices;