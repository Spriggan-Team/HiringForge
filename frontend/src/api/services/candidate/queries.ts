import { data } from "react-router-dom";
import { intercept } from "../../../utils/utils";
import { authGet, handleGenericApiResponseAfter } from "../../handler";
import type { CurrentCandidateContextResponse } from "./responses";




//------------------------
//---- Candidates
//---------------------------

const getCurrentCandidateContext = async()=>{
    try{
        const response = await  authGet<CurrentCandidateContextResponse>(`/candidates`);
        return response.data
    }
    catch(error){
        throw error;
    }
}


const CandidatesQueries = intercept(
    {  getCurrentCandidateContext },
    undefined,
    handleGenericApiResponseAfter
)



export default CandidatesQueries;