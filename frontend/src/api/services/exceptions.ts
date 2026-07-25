import { ExceptionWithPayload } from "../exceptions";

//--- Global
export class RessourceCreationFailed extends ExceptionWithPayload{};

//--OTP
export class InvalidOTP extends ExceptionWithPayload {}

//--File
export class FileSizeExceeded extends ExceptionWithPayload{};
export class FileTimeExceeded extends ExceptionWithPayload{};