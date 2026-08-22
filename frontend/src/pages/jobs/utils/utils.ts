import type { TFunction } from "i18next";
import type { JobStatus } from "../../../features/jobs/JobOffer";


export  const renderStatus = (t: TFunction<"translation">, status: JobStatus)=>{
    switch(status){
        case "active":
            return t("global.jobs.offerStatus.active");
        case "pending":
            return t("global.jobs.offerStatus.pending");
        case "published":
            return t("global.jobs.publicationState.published");
        case "draft":
            return t("global.jobs.publicationState.draft");
        case "closed":
            return t("global.jobs.publicationState.closed");
    }
  }