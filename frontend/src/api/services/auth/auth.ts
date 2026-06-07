import { post } from "../../handler";

//import types
import { AccountAlreadyRegistered, AccountNotFound, ExpiredOTP, InvalidCredentials, RessourceCreationFailed } from "./exceptions";
import { ApiResponseCode, HttpBadResponse } from "../../exceptions";
import type { AccountLoginResponse, AccountRegisterResponse, NoticeResponse } from "../response.types";


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
const performUserRegister = async (formData: FormData)=>{
    try{
        if (!formData.entries().next().done) {
            const response  = await post<AccountRegisterResponse>("/user/register", formData);
            return response;
        }
        else {
            console.log("No fields provided");
            return;
        }
    }
    catch(error){
        if(error instanceof HttpBadResponse){
            if(error.apiCode === ApiResponseCode.EXPIRED_OTP || error.apiCode === ApiResponseCode.INVALID_OTP)
                throw new ExpiredOTP();
            if(error.apiCode === ApiResponseCode.ACCOUNT_ALREADY_EXISTS)
                throw new AccountAlreadyRegistered();
            if(error.apiCode === ApiResponseCode.RESSOURCE_CREATION_FAILED)
                throw new RessourceCreationFailed();
        }
        throw error;
    }
}


const login = async (email: string, password: string)=>{
    try {
        const response = await post<AccountLoginResponse>("/user/login", { email, password });
        return response;
    }
    catch (error) {
        if(error instanceof  HttpBadResponse){
            if(error.apiCode === ApiResponseCode.ACCOUNT_NOT_FOUND)
                throw new AccountNotFound();
            if(error.apiCode == ApiResponseCode.PASSWORD_MISMATCH)
                throw new InvalidCredentials();
        }
        throw error;
    }
}

const AuthServices = {
    askVerificationCode,
    performUserRegister, login
}


export default  AuthServices;