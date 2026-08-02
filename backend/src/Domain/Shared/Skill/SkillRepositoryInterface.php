<?php


namespace App\Domain\Shared\Skill;


interface SkillRepositoryInterface{
   public function get(string $id): Skill;

   
   /** 
    * @param array<string, mixed> $scheme Defines the expected output structure/fields. Named constructor for the initial creation of a Skill
    * @return array<int, array<string, mixed>>
    */
   public function fetchAssociativeArray(string $text, string $locale, array $scheme): array;

   /** check existence */
   public function exists(string $skillId): bool;

      
   public function findExistingIds(array $ids): array;
}