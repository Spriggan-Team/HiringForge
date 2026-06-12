import RouteScheme from "../route.scheme";
import { AccountRole } from "./enums/AccountRole";

export const getSession = () => {
    const token = localStorage.getItem("token");
    const role = localStorage.getItem("role");

    return { token, role };
};


export const redirectAccordingToSession = (
    navigate: (route: string) => void
) => {
    const { token, role } = getSession();

    if (!token) {
        navigate(RouteScheme.login);
        return;
    }

    if (role === AccountRole.USER) {
        navigate(RouteScheme.userHome);
    }
};