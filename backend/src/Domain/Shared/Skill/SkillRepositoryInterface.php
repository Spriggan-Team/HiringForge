<?php


namespace App\Domain\Shared\Skill;


interface SkillRepositoryInterface{
   public function get(string $id): Skill;
}