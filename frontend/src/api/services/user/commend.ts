
import type {  UserContextApiResponse, UserProfileDataResponse } from "./response";
import type { RecruiterDashboardKpis } from "../../../features/dashboard/KpiData";

import { intercept } from "../../../utils/utils";

import type { ApiResponse, ErrorApiResponse } from "../response.types";
import { handleGenericApiResponseAfter } from "../../api-response-handler";
import { authPatch, authPut, get } from "../../http";
import type { EditedProfileData } from "../../../features/users/user.profile";
import { objectToDeepFormData,  } from "../../../utils/convertor";



const changeProfilData = async (data: EditedProfileData)=>{
    try{
        await authPatch<ApiResponse<RecruiterDashboardKpis>>(`/users/profile/change`, objectToDeepFormData(data as Record<string, unknown>));
    }
    catch(error){
        console.error("Something went wrong", error)
        throw error;
    }
}




//-- Services
const Services =  { 
    changeProfilData
};

const UserServices = intercept<typeof Services, ApiResponse | ErrorApiResponse>(
    Services,
    undefined,
    (method, result) => handleGenericApiResponseAfter(method, result)
)


export default UserServices;