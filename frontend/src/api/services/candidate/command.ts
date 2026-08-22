import { intercept } from "../../../utils/utils";
import { handleGenericApiResponseAfter } from "../../api-response-handler";
import type { ApiResponseError } from "../../exceptions";

import { authGet, authPost,  } from "../../http";
import type { ApiResponse } from "../response.types";





/**
 * Apply to a job 
 * @param param0 
 * @returns 
 */
const apply = async ({
    jobId,
    fileId
}:{
    jobId: string,
    fileId: string
})=>{
    try{
        const response = await authPost<ApiResponse<string>>(`/applications/${jobId}/apply`, { fileId });
        return response.data;
    }
    catch(error){
        throw error;
    }
}

//----------------------------
// Services building
//-----------------------------

const Services = { 
    apply,
}


//--------------------------------------
// Services
//-------------------------------------

const CandidateServices = intercept<
    typeof Services,
    ApiResponse | ApiResponseError
>(
    Services,
    undefined,
    (method, response) => handleGenericApiResponseAfter(method, response)
);

export default CandidateServices;