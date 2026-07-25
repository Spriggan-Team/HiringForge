import { authPost, patch, post } from "../../handler";

//import types
import { AccountAlreadyRegistered, AccountNotFound, InvalidCredentials, CompanyAlreadyRegistered } from "./exceptions";
import { ApiResponseCode, HttpBadResponse } from "../../exceptions";
import { type ApiResponse, type AccountLoginResponse, type AccountRegisterResponse, type NoticeResponse } from "../response.types";
import { InvalidOTP, RessourceCreationFailed } from "../exceptions";



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
                throw new InvalidOTP();
            else if(error.apiCode === ApiResponseCode.ACCOUNT_ALREADY_EXISTS)
                throw new AccountAlreadyRegistered();
            else if(error.apiCode === ApiResponseCode.RESSOURCE_CREATION_FAILED)
                throw new RessourceCreationFailed();
            else if(error.apiCode === ApiResponseCode.COMPANY_ALREADY_REGISTERED)
                throw new CompanyAlreadyRegistered();
        }
        throw error;
    }
}



//-- reset password
const resetPassword = async ({
    email,
    password,
    verificationCode
}:{
    email: string;
    password: string;
    verificationCode: string;
})=>{
    try{
        await patch("/account/resetpassword", {
            verificationCode, password, email
        })
    }
    catch(error){
        if(error instanceof  HttpBadResponse){
            if(error.apiCode === ApiResponseCode.ACCOUNT_NOT_FOUND)
                throw new AccountNotFound();
            if(error.apiCode == ApiResponseCode.EXPIRED_OTP)
                throw new InvalidOTP();
        }
        throw error; 
    }
}


//-- login
const login = async (email: string, password: string)=>{
    try {
        const response = await post<AccountLoginResponse>("/user/login", { email, password });
        return response;
    }
    catch (error) {
        if(error instanceof  HttpBadResponse){
            if(error.apiCode === ApiResponseCode.ACCOUNT_NOT_FOUND)
                throw new AccountNotFound();
            if(error.apiCode == ApiResponseCode.INVALID_CREDENTIALS)
                throw new InvalidCredentials();
        }
        throw error;
    }
}

//-- Deconnexion

const logout = async ()=>{
    try{
        const response = await authPost<ApiResponse>("/logout");
        return response.data;
    }
    catch(error){
        throw error;
    }
}


const AuthServices = {
    resetPassword,
    askVerificationCode,
    
    login, 
    logout,
    performUserRegister, 
}


export default  AuthServices;