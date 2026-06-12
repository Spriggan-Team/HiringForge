

//-- Api Success response (Data)

export type ApiResponse<T = undefined | null> = { data: T; message?: string; status?: "success" }

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


//---- 
export type KpiDataResponse = ApiResponse<{
    totalOffers: number,
    viewCount: number,
    applicationCount: number,
    activeOffers: number,
    pendingReviewOffers: number,
    closedOffers: number,
    applicationRate: number
}>