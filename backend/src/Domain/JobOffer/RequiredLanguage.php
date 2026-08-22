<?php

namespace App\Domain\JobOffer;

use App\Domain\Shared\Language\Language;
use App\Domain\Shared\LanguageLevel;


final class RequiredLanguage
{
    private Language $language;

    private LanguageLevel $level;


    public function __construct(
        Language $language,
        LanguageLevel $level
    ){
        $this->language = $language;
        $this->level = $level;
    }


    public function language(): Language
    {
        return $this->language;
    }


    public function level(): LanguageLevel
    {
        return $this->level;
    }
}