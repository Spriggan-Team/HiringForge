import Utils from "../utils/html";
import { HttpBadResponse, type ApiResponseCodeType } from "./exceptions";


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
      ...(isFormData
        ? {}
        : {
            "Content-Type": "application/json",
          }),
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

  if (!response.ok) {
      const errorBody = await response.text();

      let data: O | null = null;
      let message: string = errorBody;
      let apiCode: ApiResponseCodeType | undefined;
      let httpCode = response.status;

      //-- HTML fallback (error page serveur)
      if (contentType && contentType.includes("text/html") && DEV) {
          message = "An HTML error page was returned";
          Utils.openHtml(errorBody);
      }
      //-- JSON API error
      else {
          try {
            const errorJson = JSON.parse(errorBody);
            message = errorJson.message ?? message;

            if (errorJson.code) {
              if(HttpBadResponse.isValidApiCode(errorJson.code)){
                  apiCode = errorJson.code ;
              }
            }

            if(errorJson.data){
              data = errorJson.data as O
            }
            console.error("API Response (JSON) : ", errorJson);
          }
          catch (err) {
              //-- keep raw message
          }
      }

      throw new HttpBadResponse<O | null>({
          httpCode,
          message,
          apiCode,
          payload: data
      });
  }


  if (contentType?.includes("application/json")) {
    return response.json();
  }

  return response.text() as T;
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