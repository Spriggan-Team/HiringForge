
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

    
    //-- CANDIDATE
    candidateProfile: "/candidate/profil",

    //-- USER CANDIDATES
    userCandidate: "/user/candidates",

    //-- JOBS (PUBLICS)
    jobs: "/jobs",
    jobDetails: "/jobs/:id",

    //-- JOB USER
    userJobs: "/user/jobs",
    userJobView: "/user/jobs/:id",

    //-- User (recruteur) & Agents
    createJob: "/jobs/create",

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
