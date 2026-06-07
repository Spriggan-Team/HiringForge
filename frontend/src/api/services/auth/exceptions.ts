import { ExceptionWithPayload } from "../../exceptions";

//--- Global
export class RessourceCreationFailed extends ExceptionWithPayload{};

//--OTP
export class ExpiredOTP extends ExceptionWithPayload {}

//-- Account
export class AccountAlreadyRegistered extends ExceptionWithPayload{};
export class AccountNotFound extends ExceptionWithPayload{};

//--Auth
export class InvalidCredentials extends ExceptionWithPayload{};


//--File
export class FileSizeExceeded extends ExceptionWithPayload{};

export class FileTimeExceeded extends ExceptionWithPayload{};
