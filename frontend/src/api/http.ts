import Utils from "../utils/html";
import { ApiResponseCode, HttpBadResponse, type ApiResponseCodeType } from "./exceptions";


const port = import.meta.env.VITE_API_PORT;
const host = import.meta.env.VITE_API_HOST;
const protocol = import.meta.env.VITE_API_PROTOCOL;

const baseURL  = `${protocol}://${host}:${port}/api`;
const DEV = import.meta.env.DEV;


type RequestData = Record<string, any> | FormData | null;


/**
 * @throws {HttpBadResponse}
 */
const request = async <T, O = unknown>(
  endpoint: string,
  method: string,
  data?: RequestData,
  headers: HeadersInit = {},
  moreOptions?: RequestInit
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
    ...(moreOptions ?? {}),
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

  
  if (
      contentType?.includes("application/pdf") ||
      contentType?.startsWith("image/") ||
      contentType?.startsWith("application/octet-stream")
  ) {
      return response.blob() as Promise<T>;
  }

  //-- Fallback: Text
  return response.text() as unknown as T;
};


/** API Caller */


export const get = <T>(
  endpoint: string,
  headers?: HeadersInit,
  moreOptions?: RequestInit
) => request<T>(endpoint, "GET", undefined, headers, moreOptions);



export const post = <T>(
  endpoint: string,
  data?: RequestData,
  headers?: HeadersInit,
  moreOptions?: RequestInit
) => request<T>(endpoint, "POST", data, headers, moreOptions);



export const put = <T>(
  endpoint: string,
  data?: RequestData,
  headers?: HeadersInit,
  moreOptions?: RequestInit
) => request<T>(endpoint, "PUT", data, headers, moreOptions);



export const patch = <T>(
  endpoint: string,
  data?: RequestData,
  headers?: HeadersInit,
  moreOptions?: RequestInit
) => request<T>(endpoint, "PATCH", data, headers, moreOptions);



export const del = <T>(
  endpoint: string,
  headers?: HeadersInit,
  moreOptions?: RequestInit
) => request<T>(endpoint, "DELETE", undefined, headers, moreOptions);



export const generateAuthorizationBearerHeader = (header?: HeadersInit): HeadersInit => {
  const token = localStorage.getItem("token");
  return {
    ...(header ?? {}),
    Authorization: `Bearer ${token}`,
  };
};


//-------------------------------
//-- Authorization request
//--------------------------------

export const authGet = async <T>(
  endpoint: string,
  headers?: HeadersInit,
  moreOptions?: RequestInit,
)=>{
  return get<T>(endpoint, generateAuthorizationBearerHeader(headers), moreOptions);
}



export const authPost = async <T>(
  endpoint: string,
  data?: RequestData,
  headers?: HeadersInit,
  moreOptions?: RequestInit,
)=>{
  return post<T>(endpoint, data, generateAuthorizationBearerHeader(headers), moreOptions);
}



export const  authPut = async <T>(
  endpoint: string,
  data?: RequestData,
  headers?: HeadersInit,
  moreOptions?: RequestInit,
)=>{
  return put<T>(endpoint, data, generateAuthorizationBearerHeader(headers), moreOptions);
}



export const authPatch = async<T>(
  endpoint: string,
  data?: RequestData,
  headers?: HeadersInit,
  moreOptions?: RequestInit,
)=>{
  return patch<T>(endpoint, data, generateAuthorizationBearerHeader(headers), moreOptions);
}



export const authDel = async<T>(
  endpoint: string,
  headers?: HeadersInit,
  moreOptions?: RequestInit
)=>{
  return del<T>(endpoint, generateAuthorizationBearerHeader(headers), moreOptions);
}

