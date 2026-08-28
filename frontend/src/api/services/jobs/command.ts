

//--import job
import { id } from "date-fns/locale";

import type { JobView } from "../../../features/jobs/JobOffer"
import { intercept } from "../../../utils/utils";
import { HttpBadResponse } from "../../exceptions";
import { authPatch, authPost, authPut, post } from "../../http";

import type { ApiResponse, ErrorApiResponse } from "../response.types";
import { FailedJobAssetsUpload } from "./exceptions";
import { handleGenericApiResponseAfter } from "../../api-response-handler";


/**
 * Create new job
 * @param currentJob 
 * @returns 
 */
const createJob = async(
    currentJob: JobView
)=>{
    try{
        const data = {
            ...currentJob,
            id: null,
            contractTypeId: currentJob.contract?.id,
            departmentId: currentJob.department?.id,
            workMode: currentJob.jobWorkMode ,
            location: {
                id: currentJob.location?.id,
                city: currentJob.location?.city,
                street: currentJob.location?.street,
                country: currentJob.location?.country
            },
            salary:{
                min: currentJob.salary?.min,
                max: currentJob.salary?.max,
                currency: currentJob.salary?.devise ?? "EURO"
            },
            skills: currentJob.skills.map(skill => skill.id),
            languages: currentJob.requireLanguages.map((l)=> {
                    return ({ 
                        languageId: l.id,
                        level: l.proficiencyLevel
                    }
                );
            }),
            publicationStatus: currentJob.publicationStatus
        }

        console.log({createJobBodyRequest: data});
        
        const response = await authPost<ApiResponse<{offerId: string}>>('/job_offers', data);
        return response.data;
    }
    catch(error){
        throw error;
    }
}


/**
 * Update job offer
 */
const updateJob = async (job: JobView)=>{
  try{
    await authPut(`/job_offers/${job.id}/update`, job);
  }
  catch(error){
    throw error;
  }
}



/**
 * Upload job assets (image, ...)
 * @param jobId 
 * @param images 
 */
const uploadJobAssets = async (
  jobId: string, 
  images: { file: File; isMain: boolean }[]
) => {
  try {
    const body = new FormData();

    let mainFound = false;

    images.forEach((img, index) => {
      body.append('images', img.file);

      if (img.isMain && !mainFound) {
        body.append('mainIndex', String(index)); 
        mainFound = true;
      }
    });

    await authPost(`/job_offers/${jobId}/assets/uploads`, body);
  }
  catch (error) {
    if (error instanceof HttpBadResponse) {
      throw new FailedJobAssetsUpload();
    }
    throw error;
  }
};



/**
 * UPDATE JOB ASSETS
 */
const updateJobAssets = async (
  jobId: string, 
  newImages: { file: File; isMain: boolean }[],
  removeImages: string[] = []
): Promise<void> => {
  const hasChanges = newImages.length > 0 || removeImages.length > 0;

  if (!hasChanges) {
    return;
  }

  const formData = new FormData();

  // new file & main image detection
  let mainIndex: number | null = null;

  newImages.forEach((img, index) => {
    formData.append("newImages[]", img.file);

    if (img.isMain && mainIndex === null) {
      mainIndex = index;
    }
  });

  if (mainIndex !== null) {
    formData.append("mainIndex", String(mainIndex));
  }

  // Ids to delete
  removeImages.forEach((id) => {
    formData.append("removeImages[]", id);
  });

  // Api Call
  await authPatch<{ successIds: string[] }>(
    `/job_offers/${jobId}/assets/uploads/update`, 
    formData
  );
};



/**
 * Set job as draft
 * @param jobId 
 */
const setJobAsDraft = async (jobId: string) => {
  try{
    await authPatch(`/job_offers/offers/${jobId}/draft`);
  }
  catch(error){
    throw error;
  }
}



//---------------------
//--- Service
//-----------------------

const Services = { 
  createJob,
  updateJob,

  uploadJobAssets,
  updateJobAssets,

  setJobAsDraft
}



const JobServices = intercept<
  typeof Services,
  ApiResponse | ErrorApiResponse
>(
    Services,
    undefined,
    (method, result) => handleGenericApiResponseAfter(method, result)
);




export default JobServices;