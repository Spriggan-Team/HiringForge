
import { useContext } from "react";
import { AppContext } from "../context/app.context";
import type { CurrentActor, CurrentAgent, CurrentCandidate, CurrentUser } from "../features/shared/account";
import { CandidateContext } from "../context/candidate.context";


export const useAppContext = () => {
    const context = useContext(AppContext);
    
    // Sécurité indispensable si le hook est appelé hors du Provider
    if (!context) {
        throw new Error("useAppContent must be used within an AppProvider");
    }
    
    return context;
};


export const useCandidateContext = () => {
    const context = useContext(CandidateContext);
    
    // Sécurité indispensable si le hook est appelé hors du Provider
    if (!context) {
        throw new Error("useCandidateContext must be used within an CandidateProvider");
    }
    
    return context;
};



//--------------------------------
//-- USER types
//--------------------------------

export function useCurrentActor(): CurrentActor {
  const { currentActor } = useAppContext();
  if (!currentActor) {
    throw new Error("Unauthenticated Actor");
  }
  return currentActor;
}


/** Internal */
export function useCurrentUser(): CurrentUser {
  const actor = useCurrentActor();
  if (actor.type !== 'user') {
    throw new Error(`Refused access: Type 'user' expected, but got '${actor.type}'`);
  }
  return actor;
}


/** Hook  Candidat */
export function useCurrentCandidate(): CurrentCandidate {
  const actor = useCurrentActor();
  if (actor.type !== 'candidate') {
    throw new Error(`Refused access: Type 'candidate' expected, but obtained '${actor.type}'`);
  }
  return actor;
}


/** Hook  Agent */
export function useCurrentAgent(): CurrentAgent {
  const actor = useCurrentActor();
  if (actor.type !== 'agent') {
    throw new Error(`Refused access : Type 'agent' expected, but obtained '${actor.type}'`);
  }
  return actor;
}
