
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
    candidateDashboard: "/candidate/dashboard",

    //-- JOBS
    jobs: "/jobs",
    jobDetails: "/jobs/:id",

    //-- JOB USER
    userJobs: "/user/jobs",
    userJobView: "/user/jobs/:id",

    //-- USER JOB SCHEDULER
    userSchedule: "/user/schedule",

    //-- USER CANDIDATES
    userCandidate: "/user/candidates",

    //-- User (recruteur) & Agents
    createJob: "/jobs/create",
}

export default RouteScheme;



export const PublicRoutes = [
    RouteScheme.login,
];
