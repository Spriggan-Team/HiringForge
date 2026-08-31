import { intercept } from "../../../utils/utils";

import { authPatch } from "../../http";
import type { ApiResponse } from "../response.types";

import { ActiveEmploymentOfferExistsException } from "../exceptions";
import { ApiResponseCode, HttpBadResponse, type ApiResponseError } from "../../exceptions";



const updateApplicationsStatus = async (selectedIds: string[], status: string)=>{
    await authPatch(`/users/applications/status/change/bulk`, {ids: selectedIds, newStatus: status})
}


const updateStatus = async (applicationId: string, newStatus: string)=>{
    try{
        await authPatch(`/users/applications/${applicationId}/status/change`, { newStatus })
    }
    catch(error){
        if(error instanceof HttpBadResponse){
            if(error.apiCode === ApiResponseCode.ACTIVE_EMPLOYMENT_OFFER_EXISTS){
                throw new ActiveEmploymentOfferExistsException();
            }
        }
        throw error;
    }
}


const Services = {
    updateApplicationsStatus,
    updateStatus
}


const ApplicationServices = intercept<
    typeof Services,
    ApiResponse | ApiResponseError
>(
    Services,
    undefined
);


export default  ApplicationServices;