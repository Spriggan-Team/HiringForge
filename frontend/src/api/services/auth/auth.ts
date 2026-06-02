import { post } from "../../handler";

//import types
import { ExpiredOTP } from "./exceptions";
import { ApiResponseCode, HttpBadResponse } from "../../exceptions";
import type { AccountRegisterResponse, NoticeResponse } from "../types";



//-- ask code
const askVerificationCode = async (email: string, purpose: "SIGNUP" | "PASSWORD_RESET" | "EMAIL_CHANGE")=>{
    try{
        if(!email){
            console.log("Please provide an email for beeing able to ask for code verification");
            return;
        }
        const response = await post<NoticeResponse>("/account/verificationcode", { email, purpose });
        return response;
    }
    catch(error){
        throw error
    }
}


//-- register 
const register = async (formData: FormData)=>{
    try{
        if (!formData.entries().next().done) {
            const response  = await post<AccountRegisterResponse>("/users/register", formData);
            return response;
        }
        else {
            console.log("No fields provided");
            return;
        }
    }
    catch(error){
        if(error instanceof HttpBadResponse){
            if(error.apiCode == ApiResponseCode.EXPIRED_OTP)
                throw ExpiredOTP;
        }
        throw error;
    }
}


const AuthServices = {
    askVerificationCode, register
}


export default  AuthServices;