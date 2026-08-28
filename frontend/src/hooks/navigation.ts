import { useLocation, useMatches, useNavigate } from 'react-router-dom';
import RouteScheme, { type AppRoute } from '../route.scheme';


export interface NavigateOptions {
  params?: Record<string, string | number | boolean>;
  queries?: Record<string, string | number | boolean | undefined | null>;
  menuId?: string;
  persistMenu?: boolean;
  state?: Record<string, any>;
  from?: AppRoute;
}


export const useAppNavigate = () => {
  const navigate = useNavigate();
  const location = useLocation();

  const navigateTo = (route: AppRoute | (string & {}), options: NavigateOptions = {}) => {
    const {
      params,
      queries,
      menuId,
      persistMenu = true,
      state: customState,
      from
    } = options;

    let finalRoute: string = route;

    //  Safe Replace of params
    if (params) {
      Object.entries(params).forEach(([key, value]) => {
        const regex = new RegExp(`:${key}\\b`, 'g');
        finalRoute = finalRoute.replace(regex, encodeURIComponent(String(value)));
      });
    }

    // Treating Queries
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

    // Current route values (Pattern & Location réelle)
    const routePattern = from ?? location.pathname;

    const navigationState = {
      from: routePattern,
      realLocation: location.pathname + location.search,
      ...customState
    };

    // Nav Exec
    navigate(finalRoute, {
      state: navigationState
    });

    // Persistence du menu
    if (persistMenu && menuId) {
      localStorage.setItem("menu", menuId);
    }
  };

  return navigateTo;
};