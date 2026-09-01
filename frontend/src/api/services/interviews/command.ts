import { intercept } from "../../../utils/utils";

import { handleGenericApiResponseAfter } from "../../api-response-handler";
import { ApiResponseCode, HttpBadResponse, type ApiResponseError } from "../../exceptions";

import { authDel, authPatch, authPost } from "../../http";
import { UnableResourceDeletion } from "../exceptions";
import type { ApiResponse } from "../response.types";
import { ConcurrentInterviewsException } from "./exceptions";
import type { CreateInterviewRequest } from "./request";


const createInterview = async (data: CreateInterviewRequest)=>{
    try{
        await authPost(`/interviews/users/create`, data);
    }
    catch(error){
        if(error instanceof HttpBadResponse){
            if(error.apiCode === ApiResponseCode.CONCURRENT_INTERVIEWS_FOUNDED){
                throw new ConcurrentInterviewsException();
            }
        }
        throw error;
    }
}

const deleteInterview = async (interviewId: string)=>{
    try{
        await authDel(`/interviews/users/${interviewId}/delete`);
    }
    catch(error){
        if(error instanceof HttpBadResponse){
            if(error.apiCode === ApiResponseCode.UNABLE_RESOURCE_DELETION){
                throw new UnableResourceDeletion();
            }
        }
        throw error;
    }
}


const cancelInterview = async (interviewId: string)=>{
    try{
        await authPatch(`/interviews/users/${interviewId}/cancel`);
    }
    catch(error){
        throw error;
    }
}


//-----------
const Services = {
    deleteInterview, 
    createInterview,
    cancelInterview,
}



const InterviewsServices = intercept<
    typeof Services,
    ApiResponse | ApiResponseError
>(
    Services,
    undefined,
    (method, response) => handleGenericApiResponseAfter(method, response),
)


export default InterviewsServices;