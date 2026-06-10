import i18n from "i18next";
import { initReactI18next } from "react-i18next";


// FR
import authFR from "./locales/fr/auth.json";
import globalFR from "./locales/fr/global.json";
import homeFR from "./locales/fr/home.json"

// EN
import authEN from "./locales/en/auth.json";
import globalEN from "./locales/en/global.json";
import homeEN from "./locales/en/home.json"


const resources = {

    fr: {
        translation: {
            ...globalFR,
            ...authFR,
            ...homeFR,
        }
    },

    en: {
        translation: {
            ...globalEN,
            ...authEN,
            ...homeEN,
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
        };
    }
}

export default i18n;
