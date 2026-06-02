export const ApiResponseCode = {
    //-- otp
    EXPIRED_OTP: "expired_otp",
    INVALID_OTP: "invalid_otp",

    //-- account
    ACCOUNT_ALREADY_EXISTS: "account_already_exists",
    ACCOUNT_NOT_FOUND: "account_not_found",
} as const;


export type ApiResponseCodeType =
    typeof ApiResponseCode[keyof typeof ApiResponseCode];


export interface HttpBadResponseProps {
    httpCode?: number;
    message?: string;
    apiCode?: ApiResponseCodeType;
    options?: ErrorOptions;
}

export class HttpBadResponse extends Error {
    readonly httpCode?: number;
    readonly apiCode?: ApiResponseCodeType;

    constructor(props: HttpBadResponseProps) {
        super(props.message, props.options);

        this.name = "HttpBadResponse";
        this.httpCode = props.httpCode;
        this.apiCode = props.apiCode;
    }

    
    public static isValidApiCode  (value?: string): value is ApiResponseCodeType {
        if(!value)
            return false;
        return Object.values(ApiResponseCode).includes(
            value as ApiResponseCodeType
        );
    };
}