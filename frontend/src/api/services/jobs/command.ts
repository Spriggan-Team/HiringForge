

//--import job
import { id } from "date-fns/locale";
import type { JobView } from "../../../features/jobs/JobOffer"
import { intercept } from "../../../utils/utils";
import { HttpBadResponse } from "../../exceptions";
import { authPost, handleGenericApiResponseAfter, post } from "../../handler";
import type { ApiResponse } from "../response.types";
import { FailedJobAssetsUpload } from "./exceptions";


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
      // 1. Ajouter tous les fichiers sous le nom 'images' (ou 'images[]' selon ton parser)
      body.append('images', img.file);

      // 2. Seule la PREMIÈRE image marquée isMain définit l'index principal
      if (img.isMain && !mainFound) {
        body.append('mainIndex', String(index)); // Doit correspondre à la clé lue dans le Controller PHP
        mainFound = true;
      }
    });

    // Remplace par la route exacte (ex: `/job_offer/${jobId}/assets/uploads` ou `/${jobId}/assets/uploads`)
    await authPost(`/job_offer/${jobId}/assets/uploads`, body);
  } catch (error) {
    if (error instanceof HttpBadResponse) {
      throw new FailedJobAssetsUpload();
    }
    throw error;
  }
};



const JobServices = intercept(
    { createJob, uploadJobAssets },
    undefined,
    handleGenericApiResponseAfter
);




export default JobServices;