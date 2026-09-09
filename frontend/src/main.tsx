//-- React 
import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'

import { BrowserRouter } from 'react-router-dom'

//--Services
import AppContextProvider from './context/app.context'

//-- Custom Compoennts
import { AppSpinner } from './layout/components/indicators/spinner/spinner'
import AppModal from './layout/components/modal/app.modal.tsx'
import AppPopup from './layout/components/popup/app.popup'


//Main component
import AppRoutes from './App.tsx'

//-- Utilities
import './utils/i18n/index.ts'

//-- Styles
import './index.css'
import './variable.css'



createRoot(document.getElementById('root')!).render(
<StrictMode>
    <div style={{ width: "100%", height: "100%"}}>
        <AppContextProvider>
            <AppPopup />
            <AppSpinner />
            <AppModal />
            <BrowserRouter>
                <AppRoutes />
            </BrowserRouter>
        </AppContextProvider>
    </div>
</StrictMode>
)
