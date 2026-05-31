

const port = import.meta.env.VITE_API_PORT;
const host = import.meta.env.VITE_API_HOST;

const baseURL  = `http://${host}:${port}/api`;


type RequestData = Record<string, any> | FormData | null;


const request = async <T>(
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

  if (!response.ok) {
    const error = await response.text();

    throw new Error(
      `HTTP ${response.status} - ${
        error || response.statusText
      }`
    );
  }

  const contentType = response.headers.get("content-type");

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