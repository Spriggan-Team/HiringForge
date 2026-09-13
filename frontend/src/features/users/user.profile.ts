


/**
 * ============================================================
 * User Profile Data
 * ============================================================
 *
 * Detailed information used to display the user's profile
 * and company profile.
 */

export interface UserProfileData { //!Important (as t is used in ui)
  user: UserView;
  company: CompanyView;
}

export interface UserView {
  firstName: string;
  lastName: string;
  description: string;
  email: string;
  image?: ImageResoruce;
}

export interface CompanyView {
  name: string;
  siret: string;
  description: string;
  departments?: {
    id: number;
    name: string;
    parentId: number;
  }[];
  location: {
    id: number;
    city: string;
    postalCode: string;
    street: string;
    country: string;
  }[];
  logo?: ImageResoruce;
  videoPresentation?: ImageResoruce;
  images: {
    main?: ImageResoruce;
    others: ImageResoruce[];
  };
}

export interface ImageResoruce{ id?: number; url: string }


/**
 * Type for profil data edition
 */


export interface EditedProfileData {
    company?: {
        name?: string;
        siret?: string;
        description?: string;

        logo?: EditedImage | null;

        videoPresentation?: EditedImage | null;

        departments?: {
            added?: {
                name: string;
            }[];
            removed?: {
                id: number;
            }[];
        };

        location?: {
            added?: EditedLocation[];
            removed?: {
                id: number;
            }[];
        };

        images?: {
            added?: {
                isMain: boolean;
                file: File;
            }[];
            removed?: {
                id: number;
            }[];
        };
    };

    user?: {
        firstName?: string;
        lastName?: string;
        description?: string;
        email?: string;

        image?: EditedImage | null;
    };
}

interface EditedImage {
    id?: number;
    file: File;
}

interface EditedLocation {
    country: string;
    street: string;
    postalCode: string;
    city: string;
}