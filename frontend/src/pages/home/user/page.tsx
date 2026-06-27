

import { useCallback, useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useTranslation } from "react-i18next";

import RouteScheme from "../../../route.scheme";

//-- Custom Component
import KpiCard, { KpiPercentage } from "./component/kpi/kpi.card";
import{  
    type CardData, 
    KanbanBoard, 
    KanbanCard,
    KanbanColumn,
    KanbanDragGhost
} from "../../../layout/components/kanban/kanban";
import Calendar from "../../../layout/components/cards/calendar/calendar";

//-- SVG Components
import JobOfferSVG from "/src/assets/svg/menu/work-svgrepo-com-v2.svg"

//-- CSS Styles
import styles from "./styles.module.css"
import CandidateCard from "./component/cards/candidate.card";


type KanbanColumnIds = "interview" | "recruitment" | "technical-interview" | "hired"


const initialCards: { 
    id: string;
    columnId: KanbanColumnIds; 
    remainingTime: number;
    [key: string]: any; 
}[] = [
  {
    "id": "1",
    "columnId": "interview",
    "firstname": "Emma",
    "lastname": "Martin",
    "remainingTime": 135,
  },
  {
    "id": "2",
    "columnId": "interview",
    "firstname": "Lucas",
    "lastname": "Bernard",
    "remainingTime": 42
  },
  {
    "id": "3",
    "columnId": "interview",
    "firstname": "Sarah",
    "lastname": "Petit",
    "remainingTime": 95
  },
  {
    "id": "4",
    "columnId": "recruitment",
    "firstname": "Nathan",
    "lastname": "Robert",
    "remainingTime": 18
  },
  {
    "id": "5",
    "columnId": "recruitment",
    "firstname": "Lina",
    "lastname": "Moreau",
    "remainingTime": 210
  },
  {
    "id": "6",
    "columnId": "recruitment",
    "firstname": "Hugo",
    "lastname": "Garcia",
    "remainingTime": 87
  },
  {
    "id": "7",
    "columnId": "recruitment",
    "firstname": "Chloé",
    "lastname": "Roux",
    "remainingTime": 59
  },
  {
    "id": "8",
    "columnId": "technical-interview",
    "firstname": "Tom",
    "lastname": "Fournier",
    "remainingTime": 143
  },
  {
    "id": "9",
    "columnId": "technical-interview",
    "firstname": "Inès",
    "lastname": "Faure",
    "remainingTime": 24
  },
  {
    "id": "10",
    "columnId": "technical-interview",
    "firstname": "Noah",
    "lastname": "Mercier",
    "remainingTime": 61
  },
  {
    "id": "11",
    "columnId": "technical-interview",
    "firstname": "Julie",
    "lastname": "Blanc",
    "remainingTime": 172
  },
  {
    "id": "12",
    "columnId": "technical-interview",
    "firstname": "Louis",
    "lastname": "Chevalier",
    "remainingTime": 38
  },
  {
    "id": "13",
    "columnId": "hired",
    "firstname": "Camille",
    "lastname": "Garnier",
    "remainingTime": 0
  },
  {
    "id": "14",
    "columnId": "hired",
    "firstname": "Mathis",
    "lastname": "Dupuis",
    "remainingTime": 0
  },
  {
    "id": "15",
    "columnId": "hired",
    "firstname": "Léa",
    "lastname": "Marchand",
    "remainingTime": 0
  },
  {
    "id": "16",
    "columnId": "interview",
    "firstname": "Jules",
    "lastname": "Gauthier",
    "remainingTime": 76
  },
  {
    "id": "17",
    "columnId": "recruitment",
    "firstname": "Zoé",
    "lastname": "Lefebvre",
    "remainingTime": 14
  },
  {
    "id": "18",
    "columnId": "technical-interview",
    "firstname": "Ethan",
    "lastname": "Masson",
    "remainingTime": 128
  },
  {
    "id": "19",
    "columnId": "interview",
    "firstname": "Clara",
    "lastname": "Andre",
    "remainingTime": 53
  },
  {
    "id": "20",
    "columnId": "recruitment",
    "firstname": "Gabriel",
    "lastname": "Perrin",
    "remainingTime": 101
  }
];



const UserHome = () => {
    const navigate = useNavigate();
    const { t } = useTranslation();
    
    const [activeOffer, setActiveOffers] = useState();
    const [cards, setCards] = useState<CardData[]>(()=>{
        const c = initialCards.map(item => ({ id: item.id, columnId: item.columnId }));
        return c;
    });


    useEffect(()=>{
        const token = localStorage.getItem("token") ?? undefined;
        if(!token)
            navigate(RouteScheme.main);  
    },[]);

    const handleCandidateChangeStatus = useCallback(async (candidateId: string, status: KanbanColumnIds)=>{

    },[]);
    
    return ( 
        <div className={styles.container}>
            <main className={styles.main}>
                {/** KPI SECTIONS */}
                <div className={styles.kpiSection}>
                    <KpiCard  displayCurve={true}>
                        <KpiPercentage percent="90" />
                    </KpiCard>
                    <KpiCard svg={JobOfferSVG}>

                    </KpiCard>
                </div>

                {/** SPLIT VIEW  */}
                <div>
                    <div className={styles.view}>
                        {/** KANBAN - BOARD  */}
                        <div className={styles.pipeline}>
                            <span className={styles.title}>{t("userHome.borad.kanban.title")}</span>
                            <KanbanBoard
                                cards={cards}
                                onCardMove={async (current, index, cardData) =>{
                                    setCards(cardData);
                                    console.log({cardData, current, index})
                                    await handleCandidateChangeStatus(current.id, current.columnId as KanbanColumnIds);                               
                                }}
                            >
                                <KanbanDragGhost>
                                    <div className="ghost">
                                        Déplacement...
                                    </div>
                                </KanbanDragGhost>

                                <KanbanColumn
                                    color="#86a4df"
                                    backgroundColor="#f1f5fe"
                                    id={ "recruitment" as KanbanColumnIds }
                                    title={t("userHome.borad.kanban.colomns.new.title")}
                                    count={cards.filter(c => (c.columnId as KanbanColumnIds) === "recruitment").length}
                                >
                                    {cards
                                        .filter(c => c.columnId === "recruitment")
                                        .map(card => (
                                            <KanbanCard
                                                key={card.id}
                                                id={card.id}
                                                columnId={card.columnId}
                                            >
                                                <div className="card">
                                                    Carte {card.id}
                                                </div>
                                            </KanbanCard>
                                        ))}
                                </KanbanColumn>

                                <KanbanColumn
                                    color="#e59561"
                                    backgroundColor="#fef7f1"
                                    id={ "interview" as KanbanColumnIds}
                                    title={t("userHome.borad.kanban.colomns.interviews.title")}
                                    count={cards.filter(c => (c.columnId as KanbanColumnIds) === "interview").length}
                                >
                                    {cards
                                        .filter(c => c.columnId === "interview")
                                        .map(card => (
                                            <KanbanCard
                                                key={card.id}
                                                id={card.id}
                                                columnId={card.columnId}
                                            >
                                                <div className="card">
                                                    Carte {card.id}
                                                </div>
                                            </KanbanCard>
                                        ))}
                                </KanbanColumn>

                                <KanbanColumn
                                    color="#9573d6"
                                    backgroundColor="#ece2fd"
                                    id={ "technical-interview" as KanbanColumnIds}
                                    title={t("userHome.borad.kanban.colomns.techInterview.title")}
                                    count={cards.filter(c => (c.columnId as KanbanColumnIds) === "technical-interview").length}
                                >
                                    {cards
                                        .filter(c => c.columnId === "technical-interview")
                                        .map(card => (
                                            <KanbanCard
                                                key={card.id}
                                                id={card.id}
                                                columnId={card.columnId}
                                            >
                                                <CandidateCard
                                                    lastName={"William"}
                                                    firstName="Tendor"
                                                    time="12 m"
                                                />
                                            </KanbanCard>
                                        ))}
                                </KanbanColumn>

                                <KanbanColumn
                                    color="#8ccfaf"
                                    backgroundColor="#f1faf7"
                                    id={ "hired" as KanbanColumnIds}
                                    title={t("userHome.borad.kanban.colomns.hired.title")}
                                    count={cards.filter(c => (c.columnId as KanbanColumnIds) === "hired").length}
                                >
                                    {cards
                                        .filter(c => c.columnId === "hired")
                                        .map(card => (
                                            <KanbanCard
                                                key={card.id}
                                                id={card.id}
                                                columnId={card.columnId}
                                            >
                                                <div className="card">
                                                    Carte {card.id}
                                                </div>
                                            </KanbanCard>
                                        ))}
                                </KanbanColumn>
                            </KanbanBoard>
                        </div>

                        {/** SIDE ITEMS */}
                        <div>
                            {/** PRIORITY TASK */}
                           <div></div>
                           
                           {/** DATES/ CALENDAR */}
                           <div>
                                <Calendar />
                           </div>
                           
                           {/** AGENDA */}
                           <div></div>
                        </div>

                    </div>
                </div>

            </main>
        </div>
    );
}
 
export default UserHome;



