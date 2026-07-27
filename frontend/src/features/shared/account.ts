import type { Location } from "./global";

export type CurrentActor =
    | CurrentUser
    | CurrentCandidate
    | CurrentAgent;


export interface CurrentUser {
    type: "user";
    id: string;
    firstName: string;
    lastName: string;
    email: string;
    avatarUrl: string | null;
    company: CurrentCompany;
}


export interface CurrentCompany{
    id: string;
    name: string;
    logoUrl: string | null;
    location: Location[];
}


export interface CurrentCandidate {
    type: "candidate";
    id: string;
    firstName: string;
    lastName: string;
    email: string;
    avatarUrl: string | null;
    resumeUrl: string | null;
}


export interface CurrentAgent {
    type: "agent";
    id: string;
    firstName: string;
    lastName: string;
    email: string;
    avatarUrl: string | null;
    agencyName: string;
}



export type PersonActionType = "postulate" | "create-interview" | "confirm-interview" | "publish-offer";
