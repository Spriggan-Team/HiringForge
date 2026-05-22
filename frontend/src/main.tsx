//-- React lib
import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'

//Main component
import App from './App.tsx'

//-- Utilities
import './utils/i18n/index.ts'

//-- Styles
import './index.css'


createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>,
)
