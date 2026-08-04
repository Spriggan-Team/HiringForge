import { intercept } from "../../../utils/utils";
import {  handleGenericApiResponseAfter } from "../../handler";




const NotificationService = intercept(
    {  },
    undefined,
    handleGenericApiResponseAfter
)

export default NotificationService;