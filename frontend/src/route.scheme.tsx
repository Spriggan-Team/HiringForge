import JobApplicationPage from "./pages/candidate/apply/job.application.page";

const RouteScheme = {
    main: "/",
    userHome: "/user/home",
    
    //-- lOGIN
    login: "/login",
    forgottenPassword: "/forgotten-password",
    
    //-- REGISTER
    register: "/resgister",
    userRegister: "/user/resgister",
    candidateRegister: "/candidate/register",
    directorRegister: "/candidate/register",
    candidateOffers: "/candidate/offers",
    candidateInterviews: "/candidate/interviews",
    
    //-- CANDIDATE
    candidateProfile: "/candidate/profil",
    JobApplication: "/jobs/:id/apply",
    candidateApplications: "/candidate/applications",

    //-- USER
    userProfile: "/user/profile",

    //-- USER CANDIDATES
    userCandidate: "/user/candidates",

    //-- JOBS (PUBLICS)
    jobs: "/jobs",
    jobDetails: "/jobs/:id",

    //-- JOB USER
    userJobs: "/user/jobs",
    userJobView: "/user/jobs/:id",

    //-- User (recruteur &/| Agents)
    createJob: "/jobs/create",
    modifyJob: "/jobs/create/:id",

    //-- USER JOB SCHEDULER/INTERVIEWS
    userSchedule: "/user/schedule",

    //-- USER OFFERS
    userOffer: '/user/interviews',

    //-- STATS
    userStats: '/user/stats'
}

export default RouteScheme;



export const PublicRoutes = [
    RouteScheme.login,
    RouteScheme.jobs
];


export type AppRoute = typeof RouteScheme[keyof typeof RouteScheme];
export type RouteKey = keyof typeof RouteScheme;