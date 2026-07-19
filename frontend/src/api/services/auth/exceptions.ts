import { ExceptionWithPayload } from "../../exceptions";

//--- Global
export class RessourceCreationFailed extends ExceptionWithPayload{};

//--OTP
export class InvalidOTP extends ExceptionWithPayload {}

//-- Account & Company
export class AccountAlreadyRegistered extends ExceptionWithPayload{};
export class CompanyAlreadyRegistered extends ExceptionWithPayload{};
export class AccountNotFound extends ExceptionWithPayload{};

//--Auth
export class InvalidCredentials extends ExceptionWithPayload{};
export class AccessExpired extends ExceptionWithPayload{};

//--File
export class FileSizeExceeded extends ExceptionWithPayload{};
export class FileTimeExceeded extends ExceptionWithPayload{};
