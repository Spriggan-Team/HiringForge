

//--import job
import type { JobView } from "../../../features/jobs/JobOffer"
import { post } from "../../handler";


const createJob = async(
    currentJob: JobView
)=>{
    try{
        const response = await post('/job_offer', currentJob);
        return response;
    }
    catch(error){
        throw error;
    }
}

const JobServices = { createJob }
export default JobServices;