import { intercept } from "../../../utils/utils";
import { handleGenericApiResponseAfter } from "../../api-response-handler";
import type { ApiResponseError } from "../../exceptions";
import type { ApiResponse } from "../response.types";
import type { CreateInterviewRequest } from "./request";


const createInterview = async (data: CreateInterviewRequest)=>{

}

const cancelInterview = async (interviewId: string)=>{

}

//-----------
const Services = {
    cancelInterview, 
    createInterview
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