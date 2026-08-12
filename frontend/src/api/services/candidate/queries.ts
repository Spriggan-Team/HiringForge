import type { NavigateFunction } from "react-router-dom";
import { intercept } from "../../../utils/utils";
import { authGet, handleGenericApiResponseAfter,  } from "../../handler";
import type { CurrentCandidateContextResponse, GetResumeCollection } from "./responses";


//------------------------
//---- Candidates
//---------------------------

const getCandidateProfileImage = async(candidateId: string)=>{
    try{
        const blob = authGet<Blob>(`/candidates/assets/profile`);
        return blob;
    }
    catch(error){
        throw error;
    }
}

const getResumes = async ()=> {
    try{
        const response  = await authGet<GetResumeCollection>(`/candidates/assets/me/resumes`);
        return response.data;
    }
    catch(error){
        throw error;
    }
}


const getCurrentCandidateContext = async()=>{
    try{
        const response = await  authGet<CurrentCandidateContextResponse>(`/candidates`);
        return response.data
    }
    catch(error){
        throw error;
    }
}

const getResumeContent = async (resumeId: string)=>{
    try{
        const blob = await authGet<Blob>(`/candidates/assets/resumes/${resumeId}/content`);
        return blob;
    }
    catch(error){
        throw error;
    }
}

export class HttpContext {
  #navigate?: NavigateFunction;

  token?: string;
  locale?: string;
  organizationId?: string;
  
  setNavigate(fn: NavigateFunction) {
      this.#navigate = fn;
  }

  navigate(path: string) {
      this.#navigate?.(path);
  }
}



export const httpContext = new HttpContext();




const CandidatesQueries = intercept(
    {  
        getCurrentCandidateContext,
        getResumes,
        getResumeContent,
        getCandidateProfileImage
    },
    undefined,
    // handleGenericApiResponseAfter
)

export default CandidatesQueries;