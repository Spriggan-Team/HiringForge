import { intercept } from "../../../utils/utils"

import { handleGenericApiResponseAfter } from "../../api-response-handler";
import { authGet } from "../../http";

//- respone
import type { RecruitmentChartDataResponse } from "./response";
import type { ApiResponse, ErrorApiResponse } from "../response.types";



const getRecruitmentStatistics = async ({
    pick,
    timeframe = "week",
}: {
    pick?: Date;
    timeframe?: 'week' | 'month' | 'year';
}) => {
    const params = new URLSearchParams();

    const pickParam = (pick ?? new Date()).toISOString();

    params.append('pick', pickParam);
    params.append('timeframe', timeframe);

    const response = await authGet<RecruitmentChartDataResponse>(
        `/users/job_offers/stats/dashboard?${params.toString()}`
    );

    return response.data;
};


const Queries  = {
    getRecruitmentStatistics
}


const StatsQueries = intercept<
    typeof Queries,
    ApiResponse | ErrorApiResponse
>(
    Queries,
    undefined,
    (method, response) => handleGenericApiResponseAfter(method, response)
);

export default StatsQueries;