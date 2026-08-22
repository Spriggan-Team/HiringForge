<?php

namespace App\Infrastructure\Services\Skills;

use Symfony\Component\String\Slugger\SluggerInterface;


/**
 * Normalize skill -> slug
 */
class SkillNormalizer
{
    private const STOPWORDS = [
        'fr' => ['de', 'du', 'la', 'le', 'des', 'les', 'en', 'un', 'une', 'et', 'a', 'pour', 'par'],
        'en' => ['of', 'the', 'and', 'in', 'for', 'a', 'an', 'to', 'with', 'by', 'on', 'at'],
    ];

    public function __construct(private readonly SluggerInterface $slugger) {}

    public function normalize(string $text, string $locale = 'fr'): string
    {
        $cleanText = mb_strtolower(trim($text));
        $stopwords = self::STOPWORDS[$locale] ?? [];

        if (!empty($stopwords)) {
            $words = preg_split('/\s+/u', $cleanText);
            $filteredWords = array_filter($words, static fn($w) => !in_array($w, $stopwords, true));
            $cleanText = implode(' ', $filteredWords);
        }

        return $this->slugger->slug($cleanText)->toString();
    }
}