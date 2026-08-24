import i18n from "i18next";
import { initReactI18next } from "react-i18next";


// FR
import authFR from "./locales/fr/auth.json";
import globalFR from "./locales/fr/global.json";
import homeFR from "./locales/fr/home.json"
import notificationFR from "./locales/fr/notification.json"
import jobsFR from "./locales/fr/jobs.json"
import schedulerFR from "./locales/fr/scheduler.json"
import applicationFR from './locales/fr/applications.json'
import interviewsFR from './locales/fr/interviews.json'
import candidateFR from "./locales/fr/candidate.json"

// EN
import authEN from "./locales/en/auth.json";
import globalEN from "./locales/en/global.json";
import homeEN from "./locales/en/home.json"
import notificationEN from "./locales/en/notification.json"
import jobsEN from "./locales/en/jobs.json"
import schedulerEN from "./locales/en/scheduler.json"
import applicationEN from './locales/en/applications.json'
import interviewsEN from './locales/en/interviews.json'
import candidateEN from "./locales/en/candidate.json"


const resources = {

    fr: {
        translation: {
            ...globalFR,
            ...authFR,
            ...homeFR,
            ...notificationFR,
            ...jobsFR,
            ...schedulerFR,
            ...applicationFR,
            ...interviewsFR,
            ...candidateFR,
        }
    },

    en: {
        translation: {
            ...globalEN,
            ...authEN,
            ...homeEN,
            ...notificationEN,
            ...jobsEN,
            ...schedulerEN,
            ...applicationEN,
            ...interviewsEN,
            ...candidateEN
        }
    }
};



i18n
    .use(initReactI18next)
    .init({
        resources,
        fallbackLng: "fr",

        lng: "fr",
        interpolation: {
            escapeValue: false
        }
    });



declare module "i18next" {
    interface CustomTypeOptions {
        defaultNS: "translation";

        resources: {
            translation:
                typeof globalFR
                & typeof authFR
                & typeof homeFR
                & typeof notificationFR
                & typeof jobsFR
                & typeof schedulerFR
                & typeof applicationFR
                & typeof interviewsFR
                & typeof candidateFR
        };
    }
}

export default i18n;
