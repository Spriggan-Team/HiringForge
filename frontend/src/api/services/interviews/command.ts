import { intercept } from "../../../utils/utils";
import { handleGenericApiResponseAfter } from "../../handler";
import type { CreateInterviewRequest } from "./request";


const createInterview = async (data: CreateInterviewRequest)=>{

}

const cancelInterview = async (interviewId: string)=>{

}



const InterviewsServices = intercept(
    {cancelInterview, createInterview},
    undefined,
    handleGenericApiResponseAfter,
)


export default InterviewsServices;