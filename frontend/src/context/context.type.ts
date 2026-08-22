
//-------------------
//--- APP Context
//--------------------

//-- Popup
export type AppPopUpSettings = { status: "error" |  "success" | "warning", message: string; title?: string } | null

//-- Loading
export interface AppLoadingState { state: boolean, subtitle?: string }

//--NavBar
export interface UserAppNavBarProps {
    title: string | null;
    description?: string | null | React.ReactNode;
}


export interface AppModalProps{
    isOpen: boolean;
    title?: string;
    /** Jsx component to display */
    content: React.ReactNode; 
    /** run on closing*/
    onClose?: () => void;
}