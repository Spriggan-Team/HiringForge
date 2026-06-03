

//-- Popup
export type AppPopUpSettings = { status: "error" |  "success" | "warning", message: string } | null

export interface AppLoadingState { state: boolean, subtitle?: string }