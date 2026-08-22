import type { OfferStatus } from "../../../features/offer/offer";
import type { ApiResponse } from "../response.types";


export type UserOfferQueryResponse = ApiResponse<UserOfferQueryData[]>;

export type UserOfferQueryData = {
  id: string;
  title: string | null;
  status: OfferStatus;
  sentAt: string;
  expiredAt: string;
  salary: number;
  candidate: {
    id: string;
    firstName: string;
    lastName: string;
    email: string;
    image: string //url
  };
  application: {
    id: string;
  };
  jobOffer:{
    id: string;
    title: string;
  }
};