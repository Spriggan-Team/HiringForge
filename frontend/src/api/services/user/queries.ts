
import type {  UserContextApiResponse } from "./response";
import type { RecruiterDashboardKpis } from "../../../features/dashboard/KpiData";

import { intercept } from "../../../utils/utils";
import { authGet, generateAuthorizationBearerHeader, get } from "../../http";
import type { ApiResponse, ErrorApiResponse } from "../response.types";
import { handleGenericApiResponseAfter } from "../../api-response-handler";



const getKPI = async ()=>{
    try{
        const kpiData = await get<RecruiterDashboardKpis>(`/users/kpi`, generateAuthorizationBearerHeader());
        return kpiData;
    }
    catch(error){
        console.error("Something went wrong", error)
        throw error;
    }
}


//-- Retreives user context data
const getCurrentUserContext = async ()=>{
    try{
        const response = await authGet<UserContextApiResponse>("/users");
        return response.data;
    }
    catch(error){
        throw error;
    }
}





const UserQueriesServices = intercept<
    {
        getKPI: typeof getKPI,
        getCurrentUserContext: typeof getCurrentUserContext
    },
   ApiResponse | ErrorApiResponse
>(
    { getKPI, getCurrentUserContext },
    undefined,
    (method, result) => handleGenericApiResponseAfter(method, result)
)


export default UserQueriesServices;