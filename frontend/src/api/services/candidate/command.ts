import { intercept } from "../../../utils/utils";
import { authPost,  } from "../../http";



const apply = async ({
    jobId,
    fileId
}:{
    jobId: string,
    fileId: string
})=>{
    try{
        await authPost(`/applications/${jobId}/apply`, { fileId });
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