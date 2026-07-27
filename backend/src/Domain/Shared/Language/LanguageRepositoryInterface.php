<?php

namespace App\Domain\Shared\Language;


interface LanguageRepositoryInterface{
    public function get(int $languageId): Language;

    /** 
     * @return array<int, Language>
    */
    public function getAll(): array;
}