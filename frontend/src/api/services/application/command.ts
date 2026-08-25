import { intercept } from "../../../utils/utils";
import type { ApiResponseError } from "../../exceptions";
import { authPatch } from "../../http";
import type { ApiResponse } from "../response.types";



const updateApplicationsStatus = async (selectedIds: string[], status: string)=>{
    await authPatch(`/users/applications/status/change/bulk`, {ids: selectedIds, newStatus: status})
}


const updateStatus = async (applicationId: string, newStatus: string)=>{
    await authPatch(`/users/applications/${applicationId}/status/change`, { newStatus })
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