
import {  HttpBadResponse } from "../../exceptions";
import { authGet,  } from "../../http";

import { intercept } from "../../../utils/utils";
import {  type ApiResponse, type ContractTypeResponse, type ErrorApiResponse } from "../response.types";
import { handleGenericApiResponseAfter } from "../../api-response-handler";



const getContractType = async ()=>{
    try{
        const response = await authGet<ContractTypeResponse>('/contract');
        console.log({response})
        return response.data;
    }
    catch(error){
        if(error instanceof HttpBadResponse){

        }
        throw error;
    }
}



const ContractQueries = intercept<
    {  getContractType: typeof getContractType },
    ApiResponse | ErrorApiResponse
>(
    { getContractType },
    undefined, 
    (method, result) => handleGenericApiResponseAfter(method, result as ApiResponse)
)


export default ContractQueries;  