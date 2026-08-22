<?php


namespace App\Domain\Shared\Skill;


interface SkillRepositoryInterface{
   public function get(string $id): Skill;

   /**
     * Searches skills matching a text query in translations and aliases, returning a mapped associative array.
     *
     * Performs a two-step prioritized lookup:
     * 1. Priority search in skill translations and slugs filtered by locale.
     * 2. Fallback search in skill aliases (excluding already matched skills).
     *
     * @param string $text The query term to search against names, slugs, and aliases.
     * @param string $locale The 2-letter ISO language code (e.g., 'fr', 'en'). Truncated automatically if longer.
     * @param array{
     *     id?: string|bool,
     *     name?: string|bool,
     *     slug?: string|bool,
     *     escoUri?: string|bool,
     *     onetCode?: string|bool,
     *     canonicalName?: string|bool,
     *     language?: string|bool
     * } $scheme Map defining which fields to select in the Doctrine DQL query (e.g., ['id' => true, 'name' => 'st.name']).
     *
     * @return array<int, array{
     *     id?: string,
     *     name?: string,
     *     slug?: string,
     *     escoUri?: string|null,
     *     onetCode?: string|null,
     *     canonicalName?: string,
     *     language?: string
     * }> List of matching skill associative arrays (limited to 15 items max).
     */
   public function fetchAssociativeArray(string $text, string $locale, array $scheme): array;

   /** check existence */
   public function exists(string $skillId): bool;

      
   public function findExistingIds(array $ids): array;

   /**
    * Search a skill in the bdd
    */
   public function resolveSkill(string $text, string $locale = "fr"): ?SkillResolution;

}