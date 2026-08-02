
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
