
import RouteScheme, { PublicRoutes } from "../route.scheme";
import { AccountRole } from "./enums/AccountRole";


export const getSession = () => {
    const token = localStorage.getItem("token");
    const role = localStorage.getItem("role");

    return { token, role };
};


const isPublicRoute = (pathname: string) =>
    PublicRoutes.some(route => matchRoute(pathname, route));


export const redirectAccordingToSession = (
    navigate: (route: string) => void,
    location?: {
        pathname: string;
        state?: {
            from?: string;
        };
    }
) => {
    const { token, role } = getSession();
    const pathname = location?.pathname ?? "";

    // Not authenticated
    if (!token) {
        if (!isPublicRoute(pathname)) {
            navigate(RouteScheme.login);
        }

        return;
    }

    // Already authenticated
    if (pathname === RouteScheme.login) {
        if (location?.state?.from) {
            navigate(location.state.from);
            return;
        }

        if (role === AccountRole.USER) {
            navigate(RouteScheme.userHome);
        }
    }
};


/**
 * calcul match between a pathname & route template
 * @param pathname - actual path
 * @param route - path tamplate
 * @returns 
 */
export const matchRoute = (pathname: string, route: string): boolean => {
    const pattern = route
        .replace(/:[^/]+/g, "[^/]+")
        .replace(/\//g, "\\/");

    return new RegExp(`^${pattern}$`).test(pathname);
};