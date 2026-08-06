//-- React 
import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'

import { BrowserRouter } from 'react-router-dom'

//--Services
import AppContextProvider from './context/app.context'

//-- Custom Compoenents
import { AppSpinner } from './layout/components/indicators/spinner/spinner'
import AppPopup from './layout/components/popup/app.popup'


//Main component
import AppRoutes from './App.tsx'

//-- Utilities
import './utils/i18n/index.ts'

//-- Styles
import './index.css'
import AppModal from './layout/components/modal/app.modal.tsx'


createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <div>
            <AppContextProvider>
          <AppPopup>
            <AppSpinner>
                <BrowserRouter>
                  <AppRoutes />
              </BrowserRouter>

            </AppSpinner>
          <AppModal />
          </AppPopup>
      </AppContextProvider>
    </div>
  </StrictMode>,
)
