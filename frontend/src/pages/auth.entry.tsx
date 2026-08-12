

import React, { useCallback, useEffect, useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";

//-- Services
import { useAppContext } from "../hooks/context";
import {  redirectAccordingToSession } from "../core/auth.helpers";


interface EntryPageProps{
    children?: React.ReactNode 
}


const AuthEntryPage: React.FC<EntryPageProps> = ({ children }) => {
  const navigate = useNavigate();
  const location = useLocation();

  const { currentActor, initializeData, isAppInitializing } = useAppContext();




  //-- Initializing guard
  if (isAppInitializing) {
    return (
      <div style={{ display: "flex", justifyContent: "center", alignItems: "center", height: "100vh" }}>
        <span>Chargement de votre session...</span>
      </div>
    );
  }

  return <>{children}</>;
};

export default AuthEntryPage;