
import { intercept } from "../../../utils/utils";
import { authGet, get, handleGenericApiResponseAfter } from "../../handler";
import type { ApiResponse } from "../response.types";
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



const LanguageQueries = intercept(
    { getLanguages , getLanguagesLevel },
    undefined,
    handleGenericApiResponseAfter
);


export default LanguageQueries;