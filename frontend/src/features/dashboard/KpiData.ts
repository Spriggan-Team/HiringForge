
export interface RecruiterDashboardKpis {
  readonly totalOffers: number;
  readonly viewCount: number;
  readonly applicationCount: number;
  readonly rejectedApplicationCount: number;
  readonly hiredApplicationCount: number;
  readonly activeOffers: number;
  readonly pendingReviewOffers: number;
  readonly publishedOffers: number;
  readonly draftOffers: number;
  readonly closedOffers: number;
  readonly publicOffers: number;
  readonly privateOffers: number;
  readonly applicationRate: number;
  readonly scheduledInterviews: number;
  readonly totalGeneratedEmploymentOffers: number;
  readonly totalAcceptedEmploymentOffers: number;
  readonly applicationIncreaseThisWeek: number;
  readonly hiredIncreaseThisWeek: number;
  readonly generatedOfferEmploymentIncreaseThisWeek: number;
  readonly acceptedOfferEmploymentIncreaseThisWeek: number;
  readonly interviewsIncreaseThisWeek: number;
}

export interface KpiCardData {
    label: string;
    value: number;
}


