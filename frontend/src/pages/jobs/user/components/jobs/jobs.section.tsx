
import { useTranslation } from "react-i18next";

//--Services & types
import type { JobStatus, JobSummary } from "../../../../../features/jobs/JobOffer";

//--Custom components
import InfoPill from "../../../../../layout/components/badges/pill/info.pill";
import Separator from "../../../../../layout/components/separator/separator";

//-- SVG Compoenents
import LocationSVGComponent from "/src/assets/svg/location/location-svgrepo-com.svg"

//-- CSS module
import styles from "./JobSection.module.css"




interface JobsSectionProps{
    data: JobSummary[];
    className?: string;
    onClick?: (id: string) => void;
}


const JobsSection: React.FC<JobsSectionProps> = ({
    data,
    className,
    onClick
}) => {
  const { t } = useTranslation();

  return (
    <div className={`${styles.container} ${className}`}>
        <div className={styles.header}>
            <div className={styles.leading} />
            
            {/**Hint */}
            <span className={styles.hint}>{t("global.candidate.candidateLabel_one")}</span>
            <span className={styles.hint}>{t("global.interview.interviewLabel_one")}</span>
            <span className={styles.hint}>{t("global.offer.offerLabel_one")}</span>
            <span className={styles.hint}>{t("global.hired.hiredLabel_one")}</span>
        </div>
        <div className={styles.itemsWrapper}>
            <div className={`${styles.items} scrollbar`}>
                {data.map((job) => (
                <JobItem 
                    key={job.id} {...job}
                    onClick={onClick}
                />
                ))}
            </div>
            <div className={`${styles.fadeBottom} fadeBottom`}/>
        </div>
    </div>
  );
};
 
export default JobsSection;


/** Job Item */


type JobItemProps = JobSummary & {
  onClick?: (id: string) => void;
  className?: string;
}


const configJobStatusColor = {
  active: {
    txtColor: "#047857",
    bgColor: "#D1FAE5",
  },

  pending: {
    txtColor: "#2563EB",
    bgColor: "#DBEAFE",
  },

  published: {
    txtColor: "#7C3AED",
    bgColor: "#EDE9FE",
  },

  draft: {
    txtColor: "#475569",
    bgColor: "#F1F5F9",
  },

  closed: {
    txtColor: "#DC2626",
    bgColor: "#FEE2E2",
  },
};

const JobItem: React.FC<JobItemProps> = ({
  id,
  title,
  address,
  
  publicationStatus,
  activityStatus,

  cardinal,

  onClick,
  className,
}) => {
  const { t } = useTranslation();
  const status = activityStatus ?? publicationStatus;

  const renderStatus = (status: JobStatus)=>{
    switch(status){
        case "active":
            return t("global.jobs.offerStatus.active");
        case "pending":
            return t("global.jobs.offerStatus.pending");
        case "published":
            return t("global.jobs.publicationState.publish");
        case "draft":
            return t("global.jobs.publicationState.draft");
        case "closed":
            return t("global.jobs.publicationState.closed");
    }
  }

  return (
    <div 
        className={`${styles.item} ${className} card`}
        style={{
            cursor: onClick ? "pointer" : "default"
        }}
        onClick={()=>{
            if(onClick)
                 onClick(id);
        }}
    >
      <div className={styles.informations}>
        <span className={styles.title}>{title}</span>
        <div className={styles.locationRow}>
          <LocationSVGComponent height={15} width={15}/>
          <span>{address}</span>
        </div>
        <InfoPill
            indicator
            text={renderStatus(status)}
            txtColor={configJobStatusColor[status]?.txtColor}
            backgroundColor={configJobStatusColor[status]?.bgColor}
        />
      </div>

      <div className={styles.cardinals}>
        <Cardinal
          type="candidates"
          count={cardinal.candidates}
          label={t('global.candidate.candidateLabel', {
            count: cardinal.candidates,
          })}
        />

        <Cardinal
          type="interviews"
          count={cardinal.interviews}
          label={t('global.interview.interviewLabel', {
            count: cardinal.interviews,
          })}
        />

        <Cardinal
          type="offer"
          count={cardinal.offers}
          label={t('global.offer.offerLabel', {
            count: cardinal.offers,
          })}
        />

        <Cardinal
          type="hired"
          count={cardinal.hired}
          label={t('global.hired.hiredLabel', {
            count: cardinal.hired,
          })}
        />
      </div>
    </div>
  );
};


/** Cardinal */

interface CardinalProps {
  type?: 'interviews' | 'offer' | 'candidates' | 'hired';
  count: number;
  label: string;
  className?:string
}

const Cardinal: React.FC<CardinalProps> = ({ 
    type, count, label,
    className
}) => {
  return (
    <div className={`${styles.cardinal} ${className} `}>
      <div className={styles.countSection}>
        <span className={styles.count}>{count}</span>
        <Separator className={styles.separator} height="5px" width="25px"/>
      </div>
      <div className={styles.labelSection}>
        <div
          style={{
            ['--bgColor' as string]: type ? `var(--${type}-color)` : '',
          }}  
          className={styles.circle}
        />
        <span className={styles.label}>{label}</span>
      </div>
    </div>
  );
};
