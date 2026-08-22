import { intercept } from "../../../utils/utils";
import type { DepartmentListApiResponse } from "./responses.type";
import { authGet } from "../../http";
import type { ApiResponse, ErrorApiResponse } from "../response.types";
import { handleGenericApiResponseAfter } from "../../api-response-handler";



const getDepartmentList = async (companyId: string)=>{
    try{
        const response = await authGet<DepartmentListApiResponse>(
            `/departments?companyId=${encodeURIComponent(companyId)}`
        );
        return response.data;
    }
    catch(error){
        throw error;
    }
}


//---
const Queries =  { 
    getDepartmentList
}

const DepartmentQueries = intercept<
    typeof Queries,
    ApiResponse | ErrorApiResponse
>(
    Queries,
    undefined,
    (method, response) => handleGenericApiResponseAfter(method, response)
)



export default DepartmentQueries; 