import type { ApiResponse } from "../response.types";


export type DepartmentListApiResponse = ApiResponse<DepartmentListItems>;

export type DepartmentListItems = DepartmentItem[];

export interface DepartmentItem{
    id?: number;
    label: string;
    description: string;
    code?: string;
    externalRef?: string;
}