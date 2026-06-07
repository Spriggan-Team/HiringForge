

//-- Api Success response (Data)

export type ApiResponse<T> = { data: T; message?: string; status?: "success" }

export type NoticeResponse = ApiResponse<{ message: string }>

    //-- Auth Register
export type AccountRegisterResponse = ApiResponse<{
    id: string;
    failedUploading: string[];
}>

    //-- Auth Login
export type AccountLoginResponse = ApiResponse<{
    token: string,
    role: string
}>


//--- Api Bad Reponse (Data)
export type FileUploadErrorPayload  = ApiResponse<{ originalName?: string | undefined }>