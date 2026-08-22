import { 
    useState,
    createContext,
    useMemo,
    useCallback,
    useEffect,
} from "react";
import CandidatesQueries from "../api/services/candidate/queries";


interface CandidateContextProps{
    appliedJobIds: Record<string, boolean>;
    setAppliedJobIds: React.Dispatch<React.SetStateAction<Record<string, boolean>>>;

}

export const CandidateContext = createContext<CandidateContextProps | null>(null);




interface AppContextProviderProps{
    children: React.ReactNode
}



const CandidateContextProvider: React.FC<AppContextProviderProps> = ({children}) => {
    //-- Global state
    const [appliedJobIds, setAppliedJobIds] = useState<Record<string, boolean>>({});


    //----------------------------------
    // Context value
    //----------------------------------

    const contextValue = useMemo(
        () => ({
            appliedJobIds,
            setAppliedJobIds
        }),
        [
            appliedJobIds
        ]
    );


    return (
        <CandidateContext.Provider value={contextValue}>
            {children}
        </CandidateContext.Provider>
    );
}
 
export default CandidateContextProvider;

