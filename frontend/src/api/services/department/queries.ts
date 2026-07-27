import { intercept } from "../../../utils/utils";
import type { DepartmentListApiResponse } from "./responses.type";
import { authGet, handleGenericApiResponseAfter } from "../../handler";



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



const DepartmentQueries = intercept(
    { getDepartmentList },
    undefined,
    handleGenericApiResponseAfter
)



export default DepartmentQueries; 