import { intercept } from "../../../utils/utils";
import { authGet, handleGenericApiResponseAfter } from "../../handler";



const getRecentActivityAbout = async(offerId: string) =>{
    try{
        const response = authGet("");
    }
    catch(error){
        throw error;
    }
}



const NotificationService = intercept(
    { getRecentActivityAbout },
    undefined,
    handleGenericApiResponseAfter
)

export default NotificationService;