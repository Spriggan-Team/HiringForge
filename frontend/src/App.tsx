import { BrowserRouter, Routes, Route } from 'react-router-dom'

//-- Compoenents
import Login from './pages/Login/page'
import Register from './pages/Register/page'
import EntryPage from './pages/entry'
import RouteScheme from './route.scheme'
import Home from './pages/home/page'
import AppPopup from './layout/components/popup/app.popup'
import AppContextProvider from './context/app.context'
import { AppSpinner } from './layout/components/indicators/spinner/spinner'


function App() {

  return (
    <AppContextProvider>
      <AppPopup>
        <AppSpinner>
            <BrowserRouter>
              <Routes>
                <Route path={RouteScheme.main} element={<EntryPage />} />
                <Route path={RouteScheme.login} element={<Login />} />
                <Route path={RouteScheme.register} element={<Register />} />
                <Route path={RouteScheme.home} element={<Home />} />
              </Routes>
          </BrowserRouter>
        </AppSpinner>
      </AppPopup>
    </AppContextProvider>
  )
}

export default App
