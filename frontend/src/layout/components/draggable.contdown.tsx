import type { TFunction } from 'i18next';
import React, { useState, useEffect, useRef, useCallback } from 'react';

import styles from './DraggableCountdown.module.css';


interface Position {
  x: number;
  y: number;
}


export interface DraggableCountdownProps {
  t?: (key: string, fallback?: string) => string;
  initialSeconds?: number;
  initialPosition?: Position;
  onExpire?: () => void;
  label?: string;
}


export const COUNTDOWN_EXPIRED_STORAGE_KEY = "otp.countdown.expiresAt"; //-- !Important has it is used in the app context
export const COUNTDOWN_LABEL_STORAGE_KEY = "otp.countdown.label";       //-- ! Important has it is beeing used in the app context

export const DraggableCountdown: React.FC<DraggableCountdownProps> = ({
  t,
  initialSeconds = 300,
  initialPosition,
  onExpire,
  label = "OTP valide :",
}) => {
  //-- Position
  const [position, setPosition] = useState<Position>(() => {
    if (initialPosition) 
      return initialPosition;
    const defaultX = typeof window !== 'undefined' ? window.innerWidth - 180 : 200;
    return { x: defaultX, y: 20 };
  });


  //- retraive label from localstorage
  const [currentLabel, setCurrentLabel] = useState<string>(() => {
    if (typeof window !== 'undefined') {
      const storedLabel = localStorage.getItem(COUNTDOWN_LABEL_STORAGE_KEY);
      if (storedLabel) return storedLabel;
    }
    return label;
  });


  const [timeLeft, setTimeLeft] = useState<number>(initialSeconds);
  const [isDragging, setIsDragging] = useState<boolean>(false);


  const onExpireRef = useRef(onExpire);
  const dragOffset = useRef<{ x: number; y: number }>({ x: 0, y: 0 });

  
  useEffect(() => {
    onExpireRef.current = onExpire;
  }, [onExpire]);


  /**
   * Initialise / Restore decompte
   */
  useEffect(() => {
    const storedExpiresAt = localStorage.getItem(COUNTDOWN_EXPIRED_STORAGE_KEY);
    let expiresAt: number;

    if (storedExpiresAt) {
      expiresAt = Number(storedExpiresAt);
    }
    else {
      expiresAt = Date.now() + initialSeconds * 1000;
      localStorage.setItem(COUNTDOWN_EXPIRED_STORAGE_KEY, String(expiresAt));
      localStorage.setItem(COUNTDOWN_LABEL_STORAGE_KEY, label);
      setCurrentLabel(label);
    }

    
    const updateTimer = () => {
      const remaining = Math.max(
        0,
        Math.ceil((expiresAt - Date.now()) / 1000)
      );

      setTimeLeft(remaining);

      if (remaining <= 0) {
        localStorage.removeItem(COUNTDOWN_EXPIRED_STORAGE_KEY);
        localStorage.removeItem(COUNTDOWN_LABEL_STORAGE_KEY);
        onExpireRef.current?.();
      }
    };

    updateTimer();
    const timer = setInterval(updateTimer, 1000);

    return () => clearInterval(timer);
  }, [initialSeconds]);
  


  // Format Time mm:ss
  const formatTime = useCallback((seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  }, []);


  // Pointer Handlers
  const handlePointerDown = (e: React.PointerEvent<HTMLDivElement>) => {
    setIsDragging(true);
    e.currentTarget.setPointerCapture(e.pointerId);

    dragOffset.current = {
      x: e.clientX - position.x,
      y: e.clientY - position.y,
    };
  };

  const handlePointerMove = (e: React.PointerEvent<HTMLDivElement>) => {
    if (!isDragging) return;

    //-- calcul limites
    const maxX = window.innerWidth - 160;
    const maxY = window.innerHeight - 60;

    const newX = Math.max(10, Math.min(maxX, e.clientX - dragOffset.current.x));
    const newY = Math.max(10, Math.min(maxY, e.clientY - dragOffset.current.y));

    setPosition({ x: newX, y: newY });
  };

  const handlePointerUp = (e: React.PointerEvent<HTMLDivElement>) => {
    setIsDragging(false);
    if (e.currentTarget.hasPointerCapture(e.pointerId)) {
      e.currentTarget.releasePointerCapture(e.pointerId);
    }
  };

  if (timeLeft <= 0) {
    return null;
  }

  return (
    <div
      className={`${styles.countdownBadge} ${isDragging ? styles.dragging : ''} ${
        timeLeft <= 60 ? styles.warning : ''
      }`}
      style={{
        left: `${position.x}px`,
        top: `${position.y}px`,
      }}
      onPointerDown={handlePointerDown}
      onPointerMove={handlePointerMove}
      onPointerUp={handlePointerUp}
    >
      <div className={styles.dragHandle}>⋮⋮</div>
      <div className={styles.content}>
        <span className={styles.label}>{currentLabel}</span>
        <span className={styles.timer}>
          {timeLeft > 0 
            ? formatTime(timeLeft) 
            : t ? t("global.text.expired", "Expiré") : 'Expiré'
          }
        </span>
      </div>
    </div>
  );
};

export default DraggableCountdown;