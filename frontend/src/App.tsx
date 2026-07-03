import { BrowserRouter, Routes, Route, Outlet } from 'react-router-dom'

//--Services
import RouteScheme from './route.scheme'
import { useAppContext } from './hooks/context'
import AppContextProvider from './context/app.context'

//-- Custom Compoenents
import { AppSpinner } from './layout/components/indicators/spinner/spinner'
import AppPopup from './layout/components/popup/app.popup'
import SideMenu from './layout/components/menu/sidebar/side.menu'
import NavBar from './pages/home/components/navbar/navbar'

//-- Pages
import Login from './pages/Login/page'
import UserRegister from './pages/Register/user/page'
import EntryPage from './pages/entry'
import RegisterationEntry from './pages/Register/register/page'
import CandidateRegister from './pages/Register/candidate/candidate.register'
import DirectorRegister from './pages/Register/director/director.register'
import UserHome from './pages/home/user/page'
import UserJobsPage from './pages/jobs/user/page'
import UserPageSinglePage from './pages/jobs/user/single/page'
import CreateJobPage from './pages/jobs/create/create.job.page'



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
                      <Route path={RouteScheme.userJobs} element={<UserJobsPage />} />
                      <Route path={RouteScheme.userSingleJob} element={<UserPageSinglePage />}/>
                      <Route path={RouteScheme.createJob} element={<CreateJobPage /> }/>
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



type NavigateFn = (path: string, params?: any) => void;

interface NavigateOptions {
  params?: Record<string, any>;
  menuId?: string;
  persistMenu?: boolean;
}

export const navigateTo = (
  navigate: NavigateFn,
  route: string,
  options: NavigateOptions = {}
) => {
  const {
    params,
    menuId,
    persistMenu = true,
  } = options;

  navigate(route, params);

  if (persistMenu && menuId) {
    localStorage.setItem("menu", menuId);
  }
};