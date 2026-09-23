import type { NavigateFunction } from "react-router-dom";


export class HttpContext {
  #navigate?: NavigateFunction;
  #sessionExpiredHandler?: ()=>void;

  token?: string;
  locale?: string;
  organizationId?: string;
  
  setNavigate(fn: NavigateFunction) {
    this.#navigate = fn;
  }

  navigate(path: string) {
    this.#navigate?.(path);
  }

  //-- Authentification Session Callback
  setSessionExpiredHandler(callback?: ()=>void): void{
    this.#sessionExpiredHandler = callback;
  }

  triggerSessionExpired(): void {
    this.#sessionExpiredHandler?.();
  }
}


export const httpContext = new HttpContext();
