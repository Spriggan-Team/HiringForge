

import { useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { useAppContext } from './context';
import { useNavigate } from 'react-router-dom';
import AuthServices from '../api/services/auth/auth';
import { AccountAlreadyRegistered } from '../api/services/auth/exceptions';
import RouteScheme from '../route.scheme';


interface UseSendOTPOptions {
  onSuccess?: () => void;
  onAccountAlreadyRegistered?: () => void;
}

export const useSendOTP = (options?: UseSendOTPOptions) => {
  const { t } = useTranslation();
  const { setLoading, setPopup } = useAppContext();
  const navigate = useNavigate();

  const sendOTPCode = useCallback(async (email: string, type: "SIGNUP" | "PASSWORD_RESET" = "SIGNUP") => {
    if (!email) {
      console.warn("sendOTPCode appelé sans adresse email.");
      return;
    }

    setLoading({ state: true, subtitle: t("register.emailVerification.next.loadingMessage") });

    try {
      await AuthServices.askVerificationCode(email, type);
      setLoading({ state: false, subtitle: undefined });

      setPopup({ 
        status: "success", 
        message: t("register.apiResponse.verifyMailBox.success") 
      });

      // Custom callback
      if (options?.onSuccess) {
        options.onSuccess();
      }
    }
    catch (error) {
      setLoading({ state: false, subtitle: undefined });

      if (error instanceof Error) {
        if (error instanceof AccountAlreadyRegistered) {
          setPopup({ 
            status: "warning", 
            message: t("register.apiResponse.codeVerification.error.accountAlreadyResgistered") 
          });

          if (options?.onAccountAlreadyRegistered) {
            options.onAccountAlreadyRegistered();
          }
          else {
            navigate(RouteScheme.login);
          }
          return;
        }

        setPopup({
          status: "error",
          message: t("global.messages.error")
        });
        console.error("Error sending OTP:", error.message, error.stack);
      }
      else {
        console.error("Unknown error:", error);
      }
    }
  }, [setLoading, setPopup, t, navigate, options]);

  return { sendOTPCode };
};