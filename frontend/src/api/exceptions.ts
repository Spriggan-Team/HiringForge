import type { ErrorApiResponse } from "./services/response.types";

//-- Application Error Code
export const ApiResponseCode = {
    //-- global
    RESSOURCE_NOT_FOUND: "ressource_not_found",
    RESSOURCE_CREATION_FAILED: "ressources_creation_failed",

    //-- otp
    EXPIRED_OTP: "expired_otp",
    INVALID_OTP: "invalid_otp",

    //-- account & company
    ACCOUNT_ALREADY_EXISTS: "account_already_exists",
    ACCOUNT_NOT_FOUND: "account_not_found",
    INVALID_CREDENTIALS: "invalid_credential",
    COMPANY_ALREADY_REGISTERED: "company_already_registered",

    //-- auth/login
    AUTH_ACCESS_EXPIRED: "access_expired",


    //-- file
    FILE_MISMATCH_TYPE: "file_mismatch_type",
    FILE_SIZE_EXCEEDED: "file_size_exceeded",
    FILE_TIME_EXCEEDED: "file_time_exceeded",

    //-- resume
    UNALLOW_RESUME_DELETION: "unallow_deletion_resume",

} as const;



export type ApiResponseCodeType =
    typeof ApiResponseCode[keyof typeof ApiResponseCode];




//--- Api Générique error code
export interface HttpBadResponseProps<T = unknown> {
    httpCode?: number;
    message?: string;
    apiCode?: ApiResponseCodeType;
    options?: ErrorOptions;
    payload?: T | null;
}


export class HttpBadResponse<T = unknown> extends Error {
    readonly httpCode?: number;
    readonly apiCode?: ApiResponseCodeType;
    readonly payload?: T | null;

    constructor(props: HttpBadResponseProps<T>) {
        super(props.message, props.options);

        this.name = "HttpBadResponse";
        this.httpCode = props.httpCode;
        this.apiCode = props.apiCode;
        this.payload = props.payload;
    }

    public static isValidApiCode(value?: string): value is ApiResponseCodeType {
        return !!value &&
            Object.values(ApiResponseCode).includes(
                value as ApiResponseCodeType
            );
    }
}



//--- Application Exception with context
export interface ExceptionWithPayloadInterface<T>
{
    message?: string;
    payload?: T | null;
    code?: number;
    options?: ErrorOptions;
}


export class ExceptionWithPayload<T = unknown> extends Error
{
    public readonly code: number;
    private readonly payload: T | null;

    constructor({
        message = "",
        payload = null,
        code = 0,
        options,
    }: ExceptionWithPayloadInterface<T> = {})
    {
        super(message, options);

        this.name = this.constructor.name;
        this.code = code;
        this.payload = payload;
    }

    public getPayload(): T | null
    {
        return this.payload;
    }
}


export type ApiResponseError = ErrorApiResponse | Error | HttpBadResponse;