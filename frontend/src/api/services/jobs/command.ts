

//--import job
import { id } from "date-fns/locale";
import type { JobView } from "../../../features/jobs/JobOffer"
import { intercept } from "../../../utils/utils";
import { HttpBadResponse } from "../../exceptions";
import { authPost, post } from "../../http";
import type { ApiResponse, ErrorApiResponse } from "../response.types";
import { FailedJobAssetsUpload } from "./exceptions";
import { handleGenericApiResponseAfter } from "../../api-response-handler";


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
        
        const response = await authPost<ApiResponse<{offerId: string}>>('/job_offer', data);
        return response.data;
    }
    catch(error){
        throw error;
    }
}


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

    await authPost(`/job_offer/${jobId}/assets/uploads`, body);
  }
  catch (error) {
    if (error instanceof HttpBadResponse) {
      throw new FailedJobAssetsUpload();
    }
    throw error;
  }
};


//--- Service
const Services = { 
  createJob,
  uploadJobAssets
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