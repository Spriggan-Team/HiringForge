

//--import job
import type { JobView } from "../../../features/jobs/JobOffer"
import { post } from "../../handler";


const createJob = async(
    currentJob: JobView
)=>{
    try{
        const data = {
            ...currentJob
        }
        console.log({data});
        
        const response = await post('/job_offer', data);
        return response;
    }
    catch(error){
        throw error;
    }
}

const JobServices = { createJob }
export default JobServices;