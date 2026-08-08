import { data } from "react-router-dom";
import { intercept } from "../../../utils/utils";
import { authGet, handleGenericApiResponseAfter } from "../../handler";
import type { CandidateListResponse } from "./responses";

//--------------
//--- Recruiter
//--------------------

const getJobCandidates = async(
    {
        jobId,
        search,
        limit,
    }:{
        jobId: string,
        search: string,
        limit: number,
    }
)=>{
    try{
        const response = await authGet<CandidateListResponse>('');
        return response.data;
    }
    catch(error){
        throw error;
    }
}



const CandidatesQueries = intercept(
    { getJobCandidates },
    undefined,
    handleGenericApiResponseAfter
)



export default CandidatesQueries;