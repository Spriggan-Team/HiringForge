//-- types
type AddressProps = {
    street: string;
    postalCode: string;
    city: string;
    country: string;
};

type GeocodingResponse = {
    lat: string;
    lon: string;
}


//-- address: street, postal code, city, country
async function geocode(address: string) {
    const response = await fetch(
        `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(address)}&format=jsonv2`
    );

    const results = await response.json() as GeocodingResponse[];
    if (results.length === 0) return null;

    return {
        latitude: Number(results[0].lat),
        longitude: Number(results[0].lon),
    };
}


async function getCoordinatesWithProps({
    street,
    postalCode,
    city,
    country,
}: AddressProps) {
    const address = `${street}, ${postalCode} ${city}, ${country}`;
    return geocode(address);
}


export {geocode, getCoordinatesWithProps}