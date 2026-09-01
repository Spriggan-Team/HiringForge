import ApplicationQueries from "../api/services/application/queries";
import type { AutoCompleteSearchResultItem, CandidateSearchItem } from "../features/shared/global";



class CandidateSearchService {
    private searchCache = new Map<
        string,
        CandidateSearchItem[]
    >();

    private imageCache = new Map<
        string,
        string
    >();

    async search(
        query: string
    ): Promise<CandidateSearchItem[]> {
        const cleanQuery = query.trim().toLowerCase();

        //--------------------------------
        // Cache HIT
        //--------------------------------
        const cached = this.searchCache.get(cleanQuery);

        if (cached) {
            return cached;
        }

        //--------------------------------
        // API
        //--------------------------------
        try {
            const data = await ApplicationQueries.searchCandidateByApplication(cleanQuery);

            //--------------------------------
            // Mapping
            //--------------------------------
            const items = await Promise.all(
                data.map(async (value) => {
                    const cacheKey =`${value.candidateId}_${value.applicationId}`;
                    let image = this.imageCache.get(cacheKey);

                    //--------------------------------
                    // Image cache MISS
                    //--------------------------------

                    if (!image) {
                        try {
                            const blob =  await ApplicationQueries.getCandidateProfilImage({
                                candidateId:value.candidateId,
                                applicationId: value.applicationId,
                            });

                            if ( blob &&blob.size > 0) {
                                image = URL.createObjectURL(blob);
                                this.imageCache.set(
                                    cacheKey,
                                    image
                                );
                            }
                        } catch (error) {
                            console.warn(
                                "Erreur récupération image",
                                error
                            );
                        }
                    }

                    return {
                        ...value,
                        id: value.candidateId,
                        image,
                        label: `${value.firstName} ${value.lastName}`,
                        sublabel: value.jobTitle,
                        applicationId: value.applicationId,
                    };
                })
            );

            //--------------------------------
            // Cache
            //--------------------------------

            this.searchCache.set(
                cleanQuery,
                items
            );

            return items;
        } 
        catch (error) {
            console.warn(
                "Erreur recherche candidat",
                error
            );

            return [];
        }
    }

    //--------------------------------
    // Cache invalidation
    //--------------------------------

    clearSearchCache(): void {
        this.searchCache.clear();
    }

    clearImageCache(): void {
        this.imageCache.forEach((url) => {
            URL.revokeObjectURL(url);
        });

        this.imageCache.clear();
    }

    clearAll(): void {
        this.clearSearchCache();
        this.clearImageCache();
    }
}

export const candidateSearchService =  new CandidateSearchService();
