<?php

namespace App\Domain\Shared\Language;


interface LanguageRepositoryInterface{
    public function get(int $languageId): Language;

    public function findByCode(string $code): ?Language;

    /** 
     * @return array<int, Language>
    */
    public function getAll(): array;
}