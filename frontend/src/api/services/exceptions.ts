import { ExceptionWithPayload } from "../exceptions";

//--- Global
export class ResourceCreationFailed extends ExceptionWithPayload{};
export class ResourceNotFound extends ExceptionWithPayload{};
export class UnableResourceDeletion extends ExceptionWithPayload{};

//--OTP
export class InvalidOTP extends ExceptionWithPayload {}

//--File
export class FileSizeExceeded extends ExceptionWithPayload{};
export class FileTimeExceeded extends ExceptionWithPayload{};

//--  Resume
export class ResumeDeletionNotAllowedException extends ExceptionWithPayload{};

//-- Employment Offer Exeption
export class ActiveEmploymentOfferExistsException  extends ExceptionWithPayload{};
