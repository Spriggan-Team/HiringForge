import type { RecruiterDashboardKpis } from "../../../features/dashboard/KpiData";
import { generateAuthorizationBearerHeader, get } from "../../handler";



const getKPI = async ()=>{
    try{
        const kpiData = await get<RecruiterDashboardKpis>(`/users/kpi`, generateAuthorizationBearerHeader());
        return kpiData
    }
    catch(error){
        throw error;
    }
}



const UserQueriesServices = {
    getKPI
}



export default UserQueriesServices;