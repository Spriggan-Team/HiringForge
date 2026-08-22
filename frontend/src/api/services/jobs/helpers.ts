import type { FilterState } from "../../../features/shared/global";


export const buildFilterQueryParams = (
    filters?: FilterState,
    extraParams: Record<string, any> = {}
): string => {
    console.log(filters);
    const params = new URLSearchParams();

    // Adding additional parameters (limit, skip, etc.)
    Object.entries(extraParams).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
            params.append(key, String(value));
        }
    });

    if (!filters) return params.toString();

    // Filtres textuels et numériques
    if (filters.searchText) params.append('searchText', filters.searchText);
    if (filters.searchAddress) params.append('searchAddress', filters.searchAddress);
    if (filters.salary > 0) params.append('salary', String(filters.salary));
    if (filters.candidateCount > 0) params.append('candidateCount', String(filters.candidateCount));

    // Publication status (publishedState) -> generates publishedState[draft]=true...
    params.append('publishedState[draft]', String(filters.publishedState.draft));
    params.append('publishedState[published]', String(filters.publishedState.published));
    params.append('publishedState[closed]', String(filters.publishedState.closed));

    // 4. Offer status (offerState) -> sets offerState[active]=true...
    params.append('offerState[active]', String(filters.offerState.active));
    params.append('offerState[pending]', String(filters.offerState.pending));
    params.append('offerState[inactive]', String(filters.offerState.inactive))

    return params.toString();
};