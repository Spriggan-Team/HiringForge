
import { intercept } from "../../../utils/utils";
import { handleGenericApiResponseAfter } from "../../api-response-handler";
import { authGet, get } from "../../http";
import type { ApiResponse, ErrorApiResponse } from "../response.types";
import type { LanguageApiResponse } from "./responses.types";


const getLanguages = async ()=>{
    try{
        const response = await get<LanguageApiResponse>("/languages");
        return response.data;
    }
    catch(error){
        throw error;
    }
}


const getLanguagesLevel = async()=>{
    try{
        const response = await authGet<ApiResponse<string[]>>("/languages/level");
        return response.data;
    }
    catch(error){
        throw error;
    }
}


const Queries =     { 
        getLanguages ,
        getLanguagesLevel
}

const LanguageQueries = intercept<
    typeof Queries,
    ApiResponse | ErrorApiResponse
>(
    Queries,
    undefined,
    (method, result) => handleGenericApiResponseAfter(method, result as ApiResponse)
);


export default LanguageQueries;