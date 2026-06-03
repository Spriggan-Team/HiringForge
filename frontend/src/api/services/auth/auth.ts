import { post } from "../../handler";

//import types
import { AccountAlreadyRegistered, ExpiredOTP } from "./exceptions";
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
        if(error instanceof HttpBadResponse){
            if(error.apiCode == ApiResponseCode.ACCOUNT_ALREADY_EXISTS) //-- purpose: sign up
                throw new AccountAlreadyRegistered();
        }
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
                throw new ExpiredOTP();
            if(error.apiCode == ApiResponseCode.ACCOUNT_ALREADY_EXISTS)
                throw new AccountAlreadyRegistered();
        }
        throw error;
    }
}



const AuthServices = {
    askVerificationCode, register
}


export default  AuthServices;