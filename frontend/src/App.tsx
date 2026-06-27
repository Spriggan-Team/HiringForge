import { BrowserRouter, Routes, Route, Outlet } from 'react-router-dom'

//-- Compoenents
import RouteScheme from './route.scheme'
import AppContextProvider from './context/app.context'

import { AppSpinner } from './layout/components/indicators/spinner/spinner'
import AppPopup from './layout/components/popup/app.popup'
import SideMenu from './layout/components/menu/sidebar/side.menu'
import NavBar from './pages/home/components/navbar/navbar'

import Login from './pages/Login/page'
import UserRegister from './pages/Register/user/page'
import EntryPage from './pages/entry'
import RegisterationEntry from './pages/Register/register/page'
import CandidateRegister from './pages/Register/candidate/candidate.register'
import DirectorRegister from './pages/Register/director/director.register'
import UserHome from './pages/home/user/page'



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
                    <Route path={RouteScheme.register} element={<RegisterationEntry />} />
                    <Route path={RouteScheme.userRegister} element={<UserRegister />} />
                    <Route path={RouteScheme.candidateRegister} element={<CandidateRegister />} />
                    <Route path={RouteScheme.directorRegister}  element={<DirectorRegister />} />

                    { /** Dashboard */  }
                    <Route element={<UserAppLayout />}>
                      <Route path={RouteScheme.userHome} element={<UserHome />} />
                    </Route>

                  </Routes>
              </BrowserRouter>
            </AppSpinner>
          </AppPopup>
    </AppContextProvider>
  )
}

export default App


const UserAppLayout = () => {
  return (
    <div className='app-container'>
      <SideMenu />
      <div className='app-view'>
        <NavBar className='nav-bar' />
        <Outlet  />
      </div>
    </div>
  );
}
 