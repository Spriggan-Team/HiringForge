
import type { ApiResponse } from "../response.types";
import type {  Location as CompanyAddress } from "../../../features/shared/global";


export type UserContextApiResponse = ApiResponse<UserContext>;
export type UserProfileDataResponse = ApiResponse<UserProfileData>


/**
 * ============================================================
 * User Profile Data
 * ============================================================
 *
 * Detailed information used to display the user's profile
 * and company profile.
 */

export interface UserProfileData {
  user: UserView;
  company: CompanyView;
}

export interface UserView {
  firstName: string;
  lastName: string;
  description: string;
  email: string;
  image: string;
}

export interface CompanyView {
  name: string;
  siret: string;
  description: string;
  departmentCount: number;
  location: {
    city: string;
    postalCode: string;
    street: string;
    country: string;
  };
  logo?: string;
  videoPresentation?: string;
  images: {
    main?: string;
    others: string[];
  };
}


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