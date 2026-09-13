
import type {  UserContextApiResponse, UserProfileDataResponse } from "./response";
import type { RecruiterDashboardKpis } from "../../../features/dashboard/KpiData";

import { intercept } from "../../../utils/utils";

import type { ApiResponse, ErrorApiResponse } from "../response.types";
import { handleGenericApiResponseAfter } from "../../api-response-handler";
import { authGet, } from "../../http";



const getKPI = async ()=>{
    try{
        const kpiData = await authGet<ApiResponse<RecruiterDashboardKpis>>(`/users/kpi`);
        return kpiData.data;
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

/**
 * Retreive user profile related info
 */
const getProfileData = async ({
    companyId
}: {
    companyId: string
})=>{
    const params = new URLSearchParams();
    params.append('companyId', companyId);
    
    const url = `/users/profile/view?${params.toString()}`;
    const response = await authGet<UserProfileDataResponse>(url);
    
    return response.data;
}


//-- Queries
const Queries =  { 
    getKPI,
    getCurrentUserContext,
    getProfileData
};

const UserQueriesServices = intercept<typeof Queries, ApiResponse | ErrorApiResponse>(
    Queries,
    undefined,
    (method, result) => handleGenericApiResponseAfter(method, result)
)


export default UserQueriesServices;