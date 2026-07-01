import { useEffect, useRef, useState } from "react";

//-- Services
import "leaflet/dist/leaflet.css";
import { geocode } from "../../../../api/services/map/geocoding";

//-- Custom components
import { MapContainer, Marker, Popup, TileLayer, useMap } from "react-leaflet";

//- CSS Styles
import styles from "./Map.module.css";
import { useTranslation } from "react-i18next";

interface CustomMapContainerProps {
    zoom?: number;

    address?: string;
    street?:  string;
    city?:    string;
    country?: string;

    height?: string;
    width?: string;
    className?: string;
}


/**
 * Dealing with dynamique position change
 */
const RecenterMap: React.FC<{ position: [number, number] }> = ({ position }) => {
    const map = useMap();

    useEffect(() => {
        map.setView(position, map.getZoom());
    }, [position, map]);

    return null;
};

// ─────────────────────────────────────────────────────────────
// CustomMapContainer
// ─────────────────────────────────────────────────────────────

const DEFAULT_POSITION: [number, number] = [48.8566, 2.3522]; // Paris fallback

const CustomMapContainer: React.FC<CustomMapContainerProps> = ({
    address,
    zoom = 13,
    city,
    street,
    country,

    width,
    height,
    className,
}) => {
    const {t} = useTranslation();
    const [position, setPosition] = useState<[number, number]>(DEFAULT_POSITION);
    const [status,   setStatus]   = useState<"idle" | "loading" | "error" | "success">("idle");

    // Prevents a slow, stale request from overwriting a newer one
    const requestIdRef = useRef(0);

    useEffect(() => {
        const fullAddress = (
            address ?? `${street ?? ""} ${city ?? ""} ${country ?? ""}`
        ).trim();

        if (!fullAddress) {
            setStatus("idle");
            return;
        }

        const currentRequestId = ++requestIdRef.current;
        setStatus("loading");

        async function load() {
            try {
                const coords = await geocode(fullAddress);

                // Ignore this result if a newer request has started since
                if (currentRequestId !== requestIdRef.current) return;

                if (coords) {
                    setPosition([coords.latitude, coords.longitude]);
                    setStatus("success");
                } else {
                    setStatus("error");
                }
            } catch {
                if (currentRequestId !== requestIdRef.current) return;
                setStatus("error");
            }
        }

        load();
        window.dispatchEvent(new Event("resize"));
    }, [address, street, city, country]);

    return (
        <div className={`${styles.container} ${className ?? ""}`}>
            <MapContainer
                zoom={zoom}
                center={position}
                style={{ height: height ?? "200px", width: width ?? "100%" }}
            >
                <TileLayer
                    url="https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png"
                    // attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                />
                <RecenterMap position={position} />
                <Marker position={position}>
                    <Popup>📍</Popup>
                </Marker>
            </MapContainer>

            {status === "error" && (
                <p className={styles.errorMessage}>
                    {t("global.map.error")}
                </p>
            )}
        </div>
    );
};

export default CustomMapContainer;