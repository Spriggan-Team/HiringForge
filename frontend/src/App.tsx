import { BrowserRouter, Routes, Route } from 'react-router-dom'

//-- Compoenents
import Login from './pages/Login/page'
import UserRegister from './pages/Register/user/page'
import EntryPage from './pages/entry'
import RouteScheme from './route.scheme'
import Home from './pages/home/page'
import AppPopup from './layout/components/popup/app.popup'
import AppContextProvider from './context/app.context'
import { AppSpinner } from './layout/components/indicators/spinner/spinner'
import RegisterationEntry from './pages/Register/page'


function App() {

  return (
    <AppContextProvider>
      <AppPopup>
        <AppSpinner>
            <BrowserRouter>
              <Routes>
                <Route path={RouteScheme.main} element={<EntryPage />} />

                {/** Authentification  */}
                <Route path={RouteScheme.login} element={<Login />} />
                <Route path={RouteScheme.userRegister} element={<UserRegister />} />

                { /** Public */  }
                <Route path={RouteScheme.register} element={<RegisterationEntry />} />
                <Route path={RouteScheme.home} element={<Home />} />
                <Route path={RouteScheme.home} element={<Home />} />
              </Routes>
          </BrowserRouter>
        </AppSpinner>
      </AppPopup>
    </AppContextProvider>
  )
}

export default App
