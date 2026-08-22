
export interface RecruiterDashboardKpis {
    totalOffers: number,
    viewCount: number,
    applicationCount: number, //candidatures
    activeOffers: number,
    pendingReviewOffers: number,
    closedOffers: number,
    applicationRate: number;
    scheduledInterviews: number;
}

export interface KpiCardData {
    label: string;
    value: number;
}