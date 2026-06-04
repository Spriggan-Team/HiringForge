

//-- Api Success response (Data)
export interface NoticeResponse { message: string }

export interface AccountRegisterResponse {
    id: string;
    failedUploading: string[];
}


//--- Api Bad Reponse (Data)
export interface FileUploadErrorPayload { originalName?: string | undefined }