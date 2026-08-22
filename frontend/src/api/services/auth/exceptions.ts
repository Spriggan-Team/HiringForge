import { ExceptionWithPayload } from "../../exceptions";

//-- Account & Company
export class AccountAlreadyRegistered extends ExceptionWithPayload{};
export class CompanyAlreadyRegistered extends ExceptionWithPayload{};
export class AccountNotFound extends ExceptionWithPayload{};

//--Auth
export class InvalidCredentials extends ExceptionWithPayload{};
export class AccessExpired extends ExceptionWithPayload{};


