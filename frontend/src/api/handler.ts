import Utils from "../utils/html";
import { ApiResponseCode, HttpBadResponse, type ApiResponseCodeType } from "./exceptions";


const port = import.meta.env.VITE_API_PORT;
const host = import.meta.env.VITE_API_HOST;

const baseURL  = `http://${host}:${port}/api`;
const DEV = import.meta.env.DEV;


type RequestData = Record<string, any> | FormData | null;


const request = async <T, O = unknown>(
  endpoint: string,
  method: string,
  data?: RequestData,
  headers: HeadersInit = {}
): Promise<T> => {
  const isFormData = data instanceof FormData;
  const clearEndpoint = endpoint.replace(/^\//, "");

  const response = await fetch(`${baseURL}/${clearEndpoint}`, {
    method,
    headers: {
      ...(isFormData ? {} : { "Content-Type": "application/json" }),
      ...headers,
    },
    body:
      method === "GET" || method === "DELETE"
        ? undefined
        : isFormData
        ? data
        : data
        ? JSON.stringify(data)
        : undefined,
  });

  const contentType = response.headers.get("content-type");

  //-- Global Error handling
  if (!response.ok) {
    const errorBody = await response.text();

    let payload: O | null = null;
    let message: string = errorBody;
    let apiCode: ApiResponseCodeType | undefined;
    let httpCode = response.status;

    const isHtml = contentType?.includes("text/html");

    //-- HTML fallback (server error dev/prod)
    if (isHtml) {
      message = "An HTML error page was returned";
      if (DEV) {
        Utils.openHtml(errorBody);
      }
    }

    //-- JSON API error
    else {
      try {
        const errorJson = JSON.parse(errorBody);
        message = errorJson.message ?? message;

        if (errorJson.code && HttpBadResponse.isValidApiCode(errorJson.code)) {
          apiCode = errorJson.code;
        }

        if (errorJson.data) {
          payload = errorJson.data as O;
        }
        console.error("API Response (JSON) : ", errorJson);
      }
      catch (err) {
        // Keep the raw message if it is neither standard JSON nor HTML
      }
    }

    throw new HttpBadResponse<O | null>({
      httpCode,
      message,
      apiCode,
      payload,
    });
  }

  //-- Response: No Content (ex: 204 No Content)
  if (response.status === 204 || response.headers.get("content-length") === "0") {
    return null as T;
  }

  //-- Fallback: JSON
  if (contentType?.includes("application/json")) {
    return response.json();
  }

  //-- Fallback: Text
  return response.text() as unknown as T;
};


/** API Caller */


export const get = <T>(
  endpoint: string,
  headers?: HeadersInit
) => request<T>(endpoint, "GET", undefined, headers);



export const post = <T>(
  endpoint: string,
  data?: RequestData,
  headers?: HeadersInit
) => request<T>(endpoint, "POST", data, headers);



export const put = <T>(
  endpoint: string,
  data?: RequestData,
  headers?: HeadersInit
) => request<T>(endpoint, "PUT", data, headers);



export const patch = <T>(
  endpoint: string,
  data?: RequestData,
  headers?: HeadersInit
) => request<T>(endpoint, "PATCH", data, headers);



export const del = <T>(
  endpoint: string,
  headers?: HeadersInit
) => request<T>(endpoint, "DELETE", undefined, headers);



export const generateAuthorizationBearerHeader = (header?: HeadersInit): HeadersInit => {
  const token = localStorage.getItem("token");
  return {
    ...(header ?? {}),
    Authorization: `Bearer ${token}`,
  };
};


//-- Authorization


export const authGet = async <T>(
  endpoint: string,
  headers?: HeadersInit
)=>{
  return get<T>(endpoint, generateAuthorizationBearerHeader(headers));
}



export const authPost = async <T>(
  endpoint: string,
  data?: RequestData,
  headers?: HeadersInit
)=>{
  return post<T>(endpoint, data, generateAuthorizationBearerHeader(headers));
}



export const  authPut = async <T>(
  endpoint: string,
  data?: RequestData,
  headers?: HeadersInit
)=>{
  return put<T>(endpoint, data, generateAuthorizationBearerHeader(headers));
}



export const authPatch = async<T>(
  endpoint: string,
  data?: RequestData,
  headers?: HeadersInit
)=>{
  return patch<T>(endpoint, data, headers);
}



export const authDel = async<T>(
  endpoint: string,
  headers?: HeadersInit
)=>{
  return del<T>(endpoint, generateAuthorizationBearerHeader(headers));
}


//--------------------
//--- Http Context
//---------------------

import RouteScheme from "../route.scheme";
import type { NavigateFunction } from "react-router-dom";
import { isErrorApiResponse, type ApiResponse, type ErrorApiResponse } from "./services/response.types";


export class HttpContext {
  #navigate?: NavigateFunction;

  token?: string;
  locale?: string;
  organizationId?: string;
  
  setNavigate(fn: NavigateFunction) {
      this.#navigate = fn;
  }

  navigate(path: string) {
      this.#navigate?.(path);
  }
}


export const httpContext = new HttpContext();



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