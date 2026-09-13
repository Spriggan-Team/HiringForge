
import type { ApiResponse } from "../response.types";
import type {  Location as CompanyAddress } from "../../../features/shared/global";
import type { UserProfileData } from "../../../features/users/user.profile";


export type UserContextApiResponse = ApiResponse<UserContext>;
export type UserProfileDataResponse = ApiResponse<UserProfileData>




/**
 * ============================================================
 * User Context
 * ============================================================
 *
 * Lightweight information available globally for the user.
 */

export interface UserContext {
  user: CurrentUser;
  company: CurrentCompany;
}

export interface CurrentUser {
  id: string;
  firstName: string;
  lastName: string;
  email: string;
  avatarUrl: string | null;
}

export interface CurrentCompany {
  id: string;
  name: string;
  logoUrl: string | null;
  location: CompanyAddress[];
}