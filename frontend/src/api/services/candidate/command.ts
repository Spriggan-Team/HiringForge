import type { CandidateProfile } from "../../../features/candidates/candidates";
import { intercept } from "../../../utils/utils";
import { handleGenericApiResponseAfter } from "../../api-response-handler";
import { ApiResponseCode, HttpBadResponse, type ApiResponseError } from "../../exceptions";

import { authDel, authGet, authPost,  } from "../../http";
import { ResourceNotFound, ResumeDeletionNotAllowedException } from "../exceptions";
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
        const response = await authPost<ApiResponse<string>>(`/applications/candidates/${jobId}/apply`, { fileId });
        return response.data;
    }
    catch(error){
        throw error;
    }
}


/**
 * Upload resume
 */
const uploadResume = async (resume: File)=>{
  try{
    const formData = new FormData();
    formData.append("resume", resume)
    const response = await authPost<ApiResponse<{ fileId: string }>>(`/candidates/assets/resumes`, formData);
    return response.data;
  }
  catch(error){
    throw error;
  }
}


/**
 * Remove resume
 */
const removeResume = async(fileId: string)=>{
  try{
    await authDel(`/candidates/assets/resumes/${fileId}`); 
  }
  catch(error){
    if(error instanceof HttpBadResponse){
      if(error.httpCode === 400){
        throw new ResourceNotFound({message: "Failed to remove resume"});
      }
      else if(error.apiCode == ApiResponseCode.UNALLOW_RESUME_DELETION){
        throw new ResumeDeletionNotAllowedException({message: "Cannot delete resume"});
      }
    }
    throw error;
  }
}


/**
 * Update candidate profile details
 */
const updateCandidateProfileDetails = async (
  data: CandidateProfile
): Promise<void> => {
  const formData = new FormData();

  formData.append('firstName', data.firstName);
  formData.append('lastName', data.lastName);
  formData.append('email', data.email);
  formData.append('description', data.description ?? '');

  formData.append(
    'location',
    JSON.stringify(data.location)
  );

  formData.append(
    'skills',
    JSON.stringify(data.skills)
  );

  if (data.image instanceof File) {
    formData.append('image', data.image);
  }

  try {
    await authPost(
      '/candidates/profile/details',
      formData
    );
  } 
  catch (error) {
    throw error;
  }
};


//----------------------------
// Services building
//-----------------------------

const Services = { 
    apply,
    uploadResume,
    removeResume,
    updateCandidateProfileDetails
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