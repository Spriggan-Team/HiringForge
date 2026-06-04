import { ExceptionWithPayload } from "../../exceptions";


export class ExpiredOTP extends ExceptionWithPayload {}

export class AccountAlreadyRegistered extends ExceptionWithPayload{};

export class FileSizeExceeded extends ExceptionWithPayload{};

export class FileTimeExceeded extends ExceptionWithPayload{};