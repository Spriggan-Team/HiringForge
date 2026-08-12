import { intercept } from "../../../utils/utils";
import { authPost, handleGenericApiResponseAfter } from "../../handler";



const apply = async ({
    jobId,
    resumeId
}:{
    jobId: string,
    resumeId: string
})=>{
    try{
        await authPost(`/applications/${jobId}/apply`, { resumeId });
    }
    catch(error){
        throw error;
    }
}


const CandidateServices = intercept(
    { apply },
    undefined,
);

export default CandidateServices;