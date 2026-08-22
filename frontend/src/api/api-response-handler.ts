import RouteScheme from "../route.scheme";
import { ApiResponseCode, HttpBadResponse } from "./exceptions";
import { httpContext } from "./http-context";
import type { ApiResponse, ErrorApiResponse } from "./services/response.types";

export const handleGenericApiResponseAfter =  (
    method: string,
    result: ApiResponse | ErrorApiResponse | Error | HttpBadResponse
) => {
    if (result instanceof HttpBadResponse) {
        if (result.apiCode === ApiResponseCode.AUTH_ACCESS_EXPIRED) {
            return httpContext.navigate(RouteScheme.login);
        }
    }

    if (result instanceof Error) {
        console.error(`API error on ${method}`, result);
    }
};