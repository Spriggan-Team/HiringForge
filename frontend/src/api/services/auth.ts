import { post } from "../handler";
import type { AccountRegisterResponse, NoticeResponse } from "./types";



//-- ask code
const askVerificationCode = async (email: string, purpose: "SIGNUP" | "PASSWORD_RESET" | "EMAIL_CHANGE")=>{
    if(!email){
        console.log("Please provide an email for beeing able to ask for code verification");
        return;
    }
    const response = await post<NoticeResponse>("verificationcode", { email, purpose});
    return response;
}


//-- register 
const register = async (formData: FormData)=>{
    if (!formData.entries().next().done) {
        const response  = await post<AccountRegisterResponse>("/users/register", formData);
        return response;
    }
    else {
        console.log("No fields provided");
        return;
    }

}


const AuthServices = {
    askVerificationCode, register
}


export default  AuthServices;