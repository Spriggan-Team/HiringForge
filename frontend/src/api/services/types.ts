

//-- basic response
export interface NoticeResponse { message: string }

export interface AccountRegisterResponse {
    id: string;
    failedUploading: string[];
}