import { useCallback, useEffect, useRef, useState } from "react";
import { useTranslation } from "react-i18next";

//-- Custom service
import ApplicationQueries from "../../../../../api/services/application/queries";
import { CandidatePipelineType, type CandidatePipelineItem, type CandidatePipelineTypeValue, } from "../../../../../api/services/shared/reponses.types";

//-- Custom Composant
import { 
    type CardData, 
    KanbanBoard, 
    KanbanCard,
    KanbanColumn,
} from "../../../../../layout/components/kanban/kanban";
import CandidateCard from "../cards/candidate.card";

//-- Styles 
import styles from "./styles.module.css"




type KanbanColumnIds = "interview" | "recruitment" | "technical-interview" | "hired"


type PipelineItems = CandidatePipelineItem | null | undefined; 

const MAX_PIPELINE_ITEM_COUNT = 5;

const RecruitmentPipeline = () => {
    const {t} = useTranslation();
    
    const paginationParams = useRef({
        [CandidatePipelineType.APPLIED]: {
            skip: 0,
            limit: MAX_PIPELINE_ITEM_COUNT
        },
        [CandidatePipelineType.RH_INTERVIEWS]:{
            skip:0,
            limit: MAX_PIPELINE_ITEM_COUNT,
        },
        [CandidatePipelineType.TECHNICAL_INTERVIEWS]:{
            skip:0,
            limit: MAX_PIPELINE_ITEM_COUNT
        },
        [CandidatePipelineType.HIRED]:{
            skip: 0,
            limit: MAX_PIPELINE_ITEM_COUNT
        }
    })

    const [newCandidatesPipeline, setNewCandidatesPipeline] = useState<PipelineItems>(null);
    const [rhInterviewsPipeline, setRhInterviewsPipeline] = useState<PipelineItems>(null);
    const [technicalInterviewsPipeline, setTechnicalInterviewsPipeline ] = useState<PipelineItems>(null);
    const [hiredCandidatesPipeline, setHiredCandidatesPipeline] = useState<PipelineItems>(null);

    const PipelineStages = {
        [CandidatePipelineType.APPLIED]: newCandidatesPipeline,
        [CandidatePipelineType.RH_INTERVIEWS]: rhInterviewsPipeline,
        [CandidatePipelineType.TECHNICAL_INTERVIEWS]: technicalInterviewsPipeline ,
        [CandidatePipelineType.HIRED]: hiredCandidatesPipeline
    };


    //-- Pipeline card
    const [kanbanCardData, setKanbanCardData] = useState<CardData[]>([]);
    
    const buildKanbanCardItems = (data?: PipelineItems)=>{
        if(!data){
            return [];
        }
        
        const c = [];
        for(const value of Object.values(data.candidates)){
            //-- candidate image data
            if(value.imageId){
                // const imageBlob = ApplicationQueries.getCandidateProfilImage({});
                // console.log({value})
            }
        
            //-- Kanban components - card data
            c.push({ id: value.id, columnId: data.stageType as string  })
        }
        return c;
    }

    const KanbanRef = useRef<HTMLDivElement | null>(null);


    useEffect(()=>{
        const handlePageDataInit = async ()=>{
            try{
                const pipeline = await ApplicationQueries.getCandidatesPipeline({}) ?? [];
                console.log({ pipeline } );

                let newCandidate: PipelineItems;
                let rhInterviews:PipelineItems;
                let techInterviews: PipelineItems;
                let hiredCandidate:PipelineItems;
            
                pipeline.forEach((item) => {
                    switch (item.stageType) {
                        case CandidatePipelineType.APPLIED:
                            newCandidate = item;
                            break;

                        case CandidatePipelineType.RH_INTERVIEWS:
                            rhInterviews = item;
                            break;

                        case CandidatePipelineType.TECHNICAL_INTERVIEWS:
                            techInterviews = item;
                            break;

                        case CandidatePipelineType.HIRED:
                            hiredCandidate = item;
                            break;
                    }
                });


                //Kaban card data
                const cards = [];
                
                cards.push(...buildKanbanCardItems(rhInterviews));
                cards.push(...buildKanbanCardItems(newCandidate));
                cards.push(...buildKanbanCardItems(hiredCandidate));
                cards.push(...buildKanbanCardItems(techInterviews));

                console.log({cards});
                setKanbanCardData(cards);

                //-- State
                setRhInterviewsPipeline(rhInterviews);
                setNewCandidatesPipeline(newCandidate);
                setHiredCandidatesPipeline(hiredCandidate);
                setTechnicalInterviewsPipeline(techInterviews);
                
            }
            catch(error){
                console.warn("Somthing went wrong while retreiving candidate pipeline")
            }
        }

        //-- init data
        handlePageDataInit();

        const kRef = KanbanRef.current;
        if(!kRef){
            return;
        }

        const pipelineLoader = kRef.querySelectorAll(
            `[data-column-observer="${CandidatePipelineType.APPLIED}"],
             [data-column-observer="${CandidatePipelineType.RH_INTERVIEWS}"],
             [data-column-observer="${CandidatePipelineType.TECHNICAL_INTERVIEWS}"],
             [data-column-observer="${CandidatePipelineType.HIRED}"]
            `
        );

        //-- Load one pipeline items
        const loadPipelineStageNexItem = async (stage: CandidatePipelineTypeValue)=>{
            try{
                let queries = paginationParams.current[stage];
                const data = await ApplicationQueries.getCandidatePipelineStage({
                    type: stage,
                    skip: queries.skip,
                    limit: queries.limit
                });

                queries = {
                    ...queries,
                    skip: queries.limit,
                }

                switch(stage){
                    case CandidatePipelineType.HIRED:
                        setHiredCandidatesPipeline(data);
                        break;
                    case CandidatePipelineType.RH_INTERVIEWS:
                        setRhInterviewsPipeline(data);
                        break;
                    case CandidatePipelineType.APPLIED:
                        setNewCandidatesPipeline(data);
                        break;
                    case CandidatePipelineType.TECHNICAL_INTERVIEWS:
                        setTechnicalInterviewsPipeline(data);
                        break;
                }
                
            }
            catch(error){
                console.log("Something went loading more pipeline items")
            }
        }

        const loaders = Array.from(pipelineLoader);

        const observer = new IntersectionObserver((entries)=>{
            entries.forEach((entry)=>{
                const target = entry.target;
                if(!(target instanceof HTMLElement)){
                    return;
                }
                
                console.log("Observer Reach !!");
                const stageType = target.dataset.columnObserver as CandidatePipelineTypeValue;
                const currentPipeline = PipelineStages[stageType];
                if(currentPipeline && currentPipeline.more > 0){
                    loadPipelineStageNexItem(stageType);
                }
            })
        });

        loaders.forEach((item)=>observer.observe(item));

        return ()=>{
            observer.disconnect();
        }
    },[]);


    //-- Pipeline update view
    const handleCandidateChangeStatus = useCallback(async (candidateId: string, status: KanbanColumnIds)=>{
        throw Error("Not implemented");
    },[]);

    const getKanbanCount = (currentCount: number | undefined, more: number | undefined)=>{
        more = more ?? 0;
        currentCount = currentCount ?? 0;
        if(more > currentCount){
            return `+${more - currentCount}`
        }
        else{
            return currentCount;
        }
    }

    return (
        <div className={styles.view}>
            <div className={`${styles.pipeline} scrollbar`}>
                <KanbanBoard
                    ref={KanbanRef}
                    isDraggable={false}
                    className={styles.kanban}
                    cards={kanbanCardData}
                    onCardMove={async (current, index, cardData) =>{
                        setKanbanCardData(cardData);
                        console.log({cardData, current, index})
                        await handleCandidateChangeStatus(current.id, current.columnId as KanbanColumnIds);                               
                    }}
                >
                    <KanbanColumn
                        color="#86a4df"
                        backgroundColor="#f1f5fe"
                        className={styles.kanbanColumn}
                        id={ "recruitment" as KanbanColumnIds }
                        title={t("userHome.borad.kanban.colomns.new.title")}
                        count={getKanbanCount(newCandidatesPipeline?.candidates.length, newCandidatesPipeline?.more)}
                    >
                        {
                            newCandidatesPipeline?.candidates.slice(0, 4).map((card) => (
                                <KanbanCard
                                    key={card.id}
                                    id={card.id}
                                    columnId={newCandidatesPipeline.stageType as string}
                                >
                                    <CandidateCard
                                        lastName={card.lastName}
                                        firstName={card.firstName}
                                        time={card.delayInSec}
                                    />
                                </KanbanCard>
                            ))
                        }
                        <div 
                            className={styles.candidatePlaceholder}
                            data-column-observer={(newCandidatesPipeline?.stageType as string) ?? ""}
                        >
                        </div>
                        {/* <div className={styles.moreIndicator}>
                            <div className={styles.moreIndicatorContent}>
                                {newCanidatesPipeline && (<span>+{newCanidatesPipeline.more} {t("global.messages.more")}</span>)}
                            </div>
                        </div> */}
                    </KanbanColumn>

                    <KanbanColumn
                        color="#e59561"
                        backgroundColor="#fef7f1"
                        id={ "interview" as KanbanColumnIds}
                        title={t("userHome.borad.kanban.colomns.interviews.title")}
                        count={getKanbanCount(rhInterviewsPipeline?.candidates.length, rhInterviewsPipeline?.more)}
                    >
                        {
                            rhInterviewsPipeline?.candidates.slice(0,4)
                                .map((card, index) => (
                                    <KanbanCard
                                        key={index}
                                        id={card.id}
                                        columnId={rhInterviewsPipeline.stageType as string}
                                    >
                                        <CandidateCard
                                            lastName={card.lastName}
                                            firstName={card.firstName}
                                            time={card.delayInSec}
                                        />
                                    </KanbanCard>
                                ))
                        }
                        <div 
                            className={styles.candidatePlaceholder}
                            data-column-observer={(rhInterviewsPipeline?.stageType as string) ?? ""}
                        >
                        </div>
                        {/* <div className={styles.moreIndicator}>
                            <div className={styles.moreIndicatorContent}>
                                {rhInterviewsPipeline && (<span>+{rhInterviewsPipeline.more} {t("global.messages.more")}</span>)}
                            </div>
                        </div> */}
                    </KanbanColumn>

                    <KanbanColumn
                        color="#9573d6"
                        backgroundColor="#ece2fd"
                        id={ "technical-interview" as KanbanColumnIds}
                        title={t("userHome.borad.kanban.colomns.techInterview.title")}
                        count={getKanbanCount(technicalInterviewsPipeline?.candidates.length, technicalInterviewsPipeline?.more)}
                    >
                        {
                            technicalInterviewsPipeline?.candidates.slice(0, 4)
                                .map((card, index) => (
                                    <KanbanCard
                                        id={card.id}
                                        key={index}
                                        columnId={technicalInterviewsPipeline.stageType as string }
                                    >
                                        <CandidateCard
                                            lastName={card.lastName}
                                            firstName={card.firstName}
                                            time={card.delayInSec}
                                        />
                                    </KanbanCard>
                                ))
                        }
                        <div 
                            className={styles.candidatePlaceholder}
                            data-column-observer={(technicalInterviewsPipeline?.stageType as string) ?? ""}
                        >
                        </div>
                        {/*
                        <div className={styles.moreIndicator}>
                            <div className={styles.moreIndicatorContent}>
                                { technicalInterviewsPipeline && (<span>+{technicalInterviewsPipeline.more} {t("global.messages.more")}</span>)}
                            </div>
                        </div> */}
                    </KanbanColumn>

                    <KanbanColumn
                        color="#8ccfaf"
                        backgroundColor="#f1faf7"
                        id={ "hired" as KanbanColumnIds}
                        title={t("userHome.borad.kanban.colomns.hired.title")}
                        count={getKanbanCount(hiredCandidatesPipeline?.candidates.length, hiredCandidatesPipeline?.more)}
                    >
                        {
                            hiredCandidatesPipeline?.candidates.slice(0,4)
                                .map((card, index) => (
                                    index < 4 ? 
                                        <KanbanCard
                                            key={index}
                                            id={card.id}
                                            columnId={hiredCandidatesPipeline.stageType as string}
                                        >
                                            <CandidateCard
                                                lastName={card.lastName}
                                                firstName={card.firstName}
                                                time={card.delayInSec}
                                            />
                                        </KanbanCard>
                                    : null
                                ))
                        }
                        <div 
                            className={styles.candidatePlaceholder}
                            data-column-observer={(hiredCandidatesPipeline?.stageType as string) ?? ""}
                        >
                        </div>
                        {/* <div className={styles.moreIndicator}>
                            <div className={styles.moreIndicatorContent}>
                                {hiredCandidatesPipeline && (<span>+{hiredCandidatesPipeline.more} {t("global.messages.more")}</span>)}
                            </div>
                        </div> */}
                    </KanbanColumn>
                </KanbanBoard>
            </div>
        </div>
    );
}

 
export default RecruitmentPipeline;