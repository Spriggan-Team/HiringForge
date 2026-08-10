

import React, { useCallback, useEffect, useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";

//-- Services
import { useAppContext } from "../hooks/context";
import { AccountRole } from "../core/enums/AccountRole";
import UserQueriesServices from "../api/services/user/queries";
import { getSession, redirectAccordingToSession } from "../core/auth.helpers";
import { navigateTo } from "../App";
import RouteScheme from "../route.scheme";



interface EntryPageProps{
    children?: React.ReactNode 
}


const AuthEntryPage: React.FC<EntryPageProps> = ({ children }) => {
  const navigate = useNavigate();
  const location = useLocation();

  const { setCurrentActor } = useAppContext();
  const [isInitializing, setIsInitializing] = useState<boolean>(true);

  const initializeData = useCallback(async () => {
    try {
      const { role } = getSession();

      if (role === AccountRole.USER) {
        const data = await UserQueriesServices.getCurrentUserContext();
        console.log({ data });

        setCurrentActor({
          type: "user",
          id: data.user.id,
          lastName: data.user.lastName,
          firstName: data.user.firstName,
          email: data.user.email,
          avatarUrl: data.user.avatarUrl ?? null,
          company: {
            id: data.company.id,
            name: data.company.name,
            location: data.company.location,
            logoUrl: data.company.logoUrl ?? null,
          },
        });
      }
      else {

      }
      
    }
    catch (error) {
      console.warn("Something went wrong during initialization", error);
      setCurrentActor(null);
    }
    finally {
      setIsInitializing(false);
    }
  }, [setCurrentActor]);



  useEffect(() => {
    const setup = async () => {
      redirectAccordingToSession(navigate, location);
      await initializeData();
    };

    setup();
  }, [initializeData, location, navigate]);


  //-- Initializing guard
  if (isInitializing) {
    return (
      <div style={{ display: "flex", justifyContent: "center", alignItems: "center", height: "100vh" }}>
        <span>Chargement de votre session...</span>
      </div>
    );
  }

  return <>{children}</>;
};

export default AuthEntryPage;