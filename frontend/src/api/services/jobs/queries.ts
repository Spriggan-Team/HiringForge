import { intercept } from "../../../utils/utils";
import { authGet, handleGenericApiResponseAfter } from "../../handler";



const getJobsSummary = async (userId: string, limit: number = 7, skip: number = 0)=>{
    try{
        const response = await authGet('/users/job_offer');
    }
    catch(error){
        throw error;
    }
}



const JobQueries = intercept(
    {getJobsSummary},
    undefined,
    handleGenericApiResponseAfter
);



export default JobQueries;