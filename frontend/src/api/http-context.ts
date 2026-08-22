import type { NavigateFunction } from "react-router-dom";


export class HttpContext {
  #navigate?: NavigateFunction;

  token?: string;
  locale?: string;
  organizationId?: string;
  
  setNavigate(fn: NavigateFunction) {
      this.#navigate = fn;
  }

  navigate(path: string) {
      this.#navigate?.(path);
  }
}


export const httpContext = new HttpContext();
