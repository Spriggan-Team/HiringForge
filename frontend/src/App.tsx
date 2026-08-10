import {  Routes, Route, Outlet, useNavigate } from 'react-router-dom'
import { useEffect } from 'react'

//--Services
import RouteScheme from './route.scheme'
import { httpContext } from './api/handler'

//-- Custom Compoenents
import UserSideMenu from './layout/components/menu/sidebar/user.side.menu'
import UserNavBar from './pages/components/navbar/user.navbar'

//-- Guard
import AuthGuardPage from './pages/auth.entry'
import EntryPage from './pages/entry.page'


//-- Pages
import Login from './pages/Login/page'
import UserRegister from './pages/Register/user/page'

import RegisterationEntry from './pages/Register/register/page'
import CandidateRegister from './pages/Register/candidate/candidate.register'
import DirectorRegister from './pages/Register/director/director.register'
import UserHome from './pages/home/user/page'
import UserJobsPage from './pages/jobs/page'
import CreateJobPage from './pages/jobs/create/create.job.page'
import PrivateJobViewPage from './pages/jobs/view/page'
import SchedulingWorkspace from './pages/schedule/scheduling.workspace'
import CandidatesPage from './pages/candidates/candidates.page'
import UserOffersPage from './pages/offers/user/user.offers.page'
import UserStatsPage from './pages/stats/user/stats.user.page'
import PublicJobPage from './pages/jobs/public/public.job.page'
import PublicNavBar from './pages/components/navbar/public.navbar'




function App() {
  const navigate = useNavigate();
  
  useEffect(()=>{
    httpContext.setNavigate(navigate)
  },[navigate])
  
  return (
      <Routes>
          <Route path={RouteScheme.main} element={<EntryPage />} />
          
          {/** ALL - USER  PUBLIC */}
          <Route element={<PublicAppLayout />}>
              <Route path={RouteScheme.jobs} element={<PublicJobPage />} />
          </Route>

          {/** Registering  */}
          <Route path={RouteScheme.login} element={<Login />} />
          <Route path={RouteScheme.register} element={<RegisterationEntry />} />
          <Route path={RouteScheme.userRegister} element={<UserRegister />} />
          <Route path={RouteScheme.candidateRegister} element={<CandidateRegister />} />
          <Route path={RouteScheme.directorRegister}  element={<DirectorRegister />} />

          {/** PROTECTED ROUTES (AUTHENTIFICATION REQUIRED) */}
          <Route 
            element={<AuthAccessGranted />}
          >
            {/** EXCLUSIVE RECRUITEUR ACCESS */}
            <Route element={<UserAppLayout />}>
                { /** Dashboard */  }
                <Route path={RouteScheme.userHome} element={<UserHome />} />
                { /** JOBS VIEWS */  }
                <Route path={RouteScheme.userJobs} element={<UserJobsPage />} />
                { /** SINGLE JOB VIEW */  }
                <Route path={RouteScheme.userJobView} element={<PrivateJobViewPage />}/>
                { /** CREATE JOB  */  }
                <Route path={RouteScheme.createJob} element={<CreateJobPage /> }/>
                {/** SCHEDULE PAGE */}
                <Route path={RouteScheme.userSchedule} element={<SchedulingWorkspace />} />
                {/** CANDIDATES */}
                <Route path={RouteScheme.userCandidate} element={<CandidatesPage />} />
                {/** OFFER */}
                <Route path={RouteScheme.userOffer} element={<UserOffersPage />} />
                {/** STATS */}
                <Route path={RouteScheme.userStats} element={<UserStatsPage />}/>
            </Route>

            {/** EXCLUSIVE CANDIDATES */}
            <Route>

            </Route>

            {/** PUBLIC ACCESS (AUTH) */}
          </Route>
      </Routes>
  )
}

export default App



const UserAppLayout = () => {
  return (
    <div className='app-container'>
      <UserSideMenu />
      <div className='app-view'>
        <UserNavBar className='nav-bar' />
        <Outlet  />
      </div>
    </div>
  );
}

//-- Common navabar (more design for candidates though)
const PublicAppLayout = ()=>{
  return (
    <div  style={{ display: "flex", flexDirection: "column" }}>
      <PublicNavBar />
      <div>
        <Outlet />
      </div>
    </div>
  );
}



const AuthAccessGranted = ()=>{
  return (
      <AuthGuardPage>
        <Outlet />
      </AuthGuardPage>
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

  let finalRoute = route;

  /** Safe Replace   */
  if (params) {
    for (const [key, value] of Object.entries(params)) {
      finalRoute = finalRoute.replaceAll(
        `:${key}`,
        encodeURIComponent(String(value))
      );
    }
  }

  navigate(finalRoute);

  /** Persist menu state */
  if (persistMenu && menuId) {
    localStorage.setItem("menu", menuId);
  }
};