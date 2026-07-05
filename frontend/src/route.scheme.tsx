
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
    candidateDashboard: "/candidate/dashboard",
    candidateProfile: "/candidate/profil",

    //-- JOBS
    jobs: "/jobs",
    jobDetails: "/jobs/:id",

    //--JOB USER
    userJobs: "/user/jobs",
    userJobView: "/user/jobs/:id",

    //-- User (recruteur) & Agents
    createJob: "/jobs/create",
}

export default RouteScheme;