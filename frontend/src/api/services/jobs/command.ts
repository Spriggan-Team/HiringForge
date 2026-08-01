

//--import job
import type { JobView } from "../../../features/jobs/JobOffer"
import { intercept } from "../../../utils/utils";
import { authPost, handleGenericApiResponseAfter, post } from "../../handler";
import type { ApiResponse } from "../response.types";


const createJob = async(
    currentJob: JobView
)=>{
    try{
        const data = {
            ...currentJob,
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
            skills: currentJob.skills,
            languages: currentJob.requireLanguages.map((l)=> {
                    return ({ 
                        languageId: l.id,
                        level: l.proficiencyLevel
                    }
                );
            }),
        }

        console.log({createJobBodyRequest: data});
        
        const response = await authPost<ApiResponse<{offerId: string}>>('/job_offer', data);
        return response.data;
    }
    catch(error){
        throw error;
    }
}


const JobServices = intercept(
    { createJob },
    undefined,
    handleGenericApiResponseAfter
)


export default JobServices;