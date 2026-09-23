import { useEffect } from 'react'
import { useTranslation } from 'react-i18next'
import {  Routes, Route, Outlet, useNavigate } from 'react-router-dom'

//--Services
import RouteScheme from './route.scheme'
import { httpContext } from './api/http-context'
import { useAppContext } from './hooks/context'

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
import CandidateRegister from './pages/Register/candidate/candidate.register.page'
import DirectorRegister from './pages/Register/director/director.register'
import UserHome from './pages/home/user/page'
import UserJobsPage from './pages/jobs/page'
import PrivateJobViewPage from './pages/jobs/view/page'
import SchedulingWorkspace from './pages/schedule/user/scheduling.workspace'
import CandidatesPage from './pages/user/candidates/candidates.page'
import UserOffersPage from './pages/offers/user/user.offers.page'
import UserStatsPage from './pages/stats/user/stats.user.page'
import PublicJobPage from './pages/jobs/public/public.job.page'
import PublicNavBar from './pages/components/navbar/public.navbar'
import JobApplicationPage from './pages/candidate/apply/job.application.page'
import CandidateApplicationPage from './pages/candidate/applications/candidate.applications.page'
import CandidateOfferPage from './pages/candidate/offers/candidate.offer.page'
import CandidateProfilPage from './pages/candidate/profile/candidate.profile.page'
import CandidateContextProvider from './context/candidate.context'
import CreateJobPage from './pages/jobs/form/create.job.page'
import ModifyJobPage from './pages/jobs/form/modify.job.page'
import UserProfilPage from './pages/user/profil/profil.page'
import CandidateInterviewsPage from './pages/schedule/candidate/interviews/candidate.interviews.page'
import UserInterviewsPage from './pages/user/interviews/user.interview.page'





function App() {
  const {t} = useTranslation();
  const navigate = useNavigate();
  const { setPopup } = useAppContext();

  useEffect(()=>{
    httpContext.setNavigate(navigate);
    httpContext.setSessionExpiredHandler(()=>{
      localStorage.removeItem("token");
      localStorage.removeItem("role");
      localStorage.removeItem("menu");
      setPopup({ status: 'warning', message: t('global.messages.expiredAuthentificationSession') })
    })
  },[navigate]);
  
  return (
    <div>
      <Routes>
          {/** ENTRY PAGE */}
          <Route path={RouteScheme.main} element={<EntryPage />} />
          
          {/** ALL -  PUBLIC  PAGES*/}
          <Route element={<PublicAppLayout />}>
              <Route
                path={RouteScheme.jobs}
                element={
                  <CandidateContextProvider>
                    <PublicJobPage />
                  </CandidateContextProvider>
                }
              />
          </Route>


          {/** REGISTERING  */}
          <Route path={RouteScheme.login} element={<Login />} />
          <Route path={RouteScheme.register} element={<RegisterationEntry />} />

          <Route element={<PublicAppLayout backgroundColor='transparent' />}>
            {/** RECRUTEUR REGISTERING  */}
            <Route path={RouteScheme.userRegister} element={<UserRegister />} />
            {/** CANIDATE REGISTERING  */}
            <Route path={RouteScheme.candidateRegister} element={<CandidateRegister />} />
            {/** RH DIRECTOR REGISTERING  */}
            <Route path={RouteScheme.directorRegister}  element={<DirectorRegister />} />
          </Route>


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
                {/** Modify JOB */}
                <Route path={RouteScheme.modifyJob} element={<ModifyJobPage /> }/>
                {/** INTERVIEWS PAGES */}
                <Route path={RouteScheme.userIntervews} element={<UserInterviewsPage />} />
                {/** SCHEDULE/CALENDAR PAGE */}
                <Route path={RouteScheme.userSchedule} element={<SchedulingWorkspace />} />
                {/** CANDIDATES */}
                <Route path={RouteScheme.userCandidate} element={<CandidatesPage />} />
                {/** EMPLOYMENT OFFER */}
                <Route path={RouteScheme.userOffer} element={<UserOffersPage />} />
                {/** STATS */}
                <Route path={RouteScheme.userStats} element={<UserStatsPage />}/>
                {/** PROFIL PAGE */}
                <Route path={RouteScheme.userProfile} element={<UserProfilPage />} />
            </Route>

            {/** EXCLUSIVE CANDIDATES */}
            <Route element={<PublicAppLayout />}>
                {/** APPLICATIONS */}
                <Route 
                  path={RouteScheme.JobApplication}
                  element={
                    <CandidateContextProvider>
                      <JobApplicationPage />
                    </CandidateContextProvider>
                  }
                />
                {/** MY APPLICATIONS */}
                <Route path={RouteScheme.candidateApplications} element={<CandidateApplicationPage />} />
                {/** MY INTERVIEWS */}
                <Route path={RouteScheme.candidateInterviews} element={<CandidateInterviewsPage />} />
                {/* * MY EMPLOYMENT OFFERS*/}
                <Route path={RouteScheme.candidateOffers} element={<CandidateOfferPage  />} /> 
                {/** MY  PROFIL */}
                <Route path={RouteScheme.candidateProfile} element={<CandidateProfilPage />} />
            </Route>


            {/** PUBLIC ACCESS (AUTH) */}
          </Route>


      </Routes>
    </div>
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
const PublicAppLayout = ({ backgroundColor = "#ffffff" }: { backgroundColor?: string }) => {
  return (
    <div  style={{ display: "flex", flexDirection: "column" }}>
      <PublicNavBar backgroundColor={backgroundColor}/>
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
  queries?: Record<string, string>;
  persistMenu?: boolean;
  state?: {
        from?: string;
  };
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
    state,
    queries
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


  if (queries) {
    const searchParams = new URLSearchParams();
    Object.entries(queries).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        searchParams.append(key, String(value));
      }
    });

    const queryString = searchParams.toString();
    if (queryString) {
      finalRoute += `?${queryString}`;
    }
  }


  navigate(finalRoute, {
    state
  });

  /** Persist menu state */
  if (persistMenu && menuId) {
    localStorage.setItem("menu", menuId);
  }
};


export const navigateAndReload = (url: string) => {
  window.location.href = url;
};