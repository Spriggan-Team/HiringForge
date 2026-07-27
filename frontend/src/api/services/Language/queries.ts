
import { intercept } from "../../../utils/utils";
import { get, handleGenericApiResponseAfter } from "../../handler";
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


const LanguageQueries = intercept(
    { getLanguages },
    undefined,
    handleGenericApiResponseAfter
);

export default LanguageQueries;