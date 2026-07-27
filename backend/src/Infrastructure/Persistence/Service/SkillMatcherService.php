<?php

namespace App\Infrastructure\Persistence\Service;

use App\Domain\Shared\Language\LanguageRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Language\LanguageEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillTranslationEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;

class SkillMatcherService
{
    public function __construct(
        private EntityManagerInterface $em,
        private SluggerInterface $slugger,
        private LanguageRepositoryInterface $languageRepository
    ){}

    /**
     * Cleans a string to facilitate semantic comparison
     */
    public function normalize(
        string $text,
    ): string {
        return $this->slugger->slug(mb_strtolower($text))->toString();
    }

    /**
     *  Find an Existing Skill  by External ID or Semantic Similarity
     * (or create one)
     */
    public function findOrCreateSkill(
        string $name,
        string $locale,
        ?string $escoUri = null,
        ?string $onetCode = null
    ): SkillEntity {
        $skillRepo = $this->em->getRepository(SkillEntity::class);
        $transRepo = $this->em->getRepository(SkillTranslationEntity::class);

        //-- Search with identifiant
        if ($escoUri) {
            $existing = $skillRepo->findOneBy(['escoUri' => $escoUri]);
            if ($existing) 
                return $this->enrichSkill($existing, $onetCode);
        }

        if ($onetCode) {
            $existing = $skillRepo->findOneBy(['onetCode' => $onetCode]);
            if ($existing) 
                return $this->enrichSkill($existing, null, $escoUri);
        }

        //-- Exact semantique research with normalize name
        $language = $this->em->getRepository(LanguageEntity::class)->findOneBy(['code' => $locale]);
        if (!$language) {
            throw new \Exception("The language '$locale' was not found in the database..");
        }

        $slug = $this->normalize($name);
        $translation = $transRepo->findOneBy([
            'language' => $language,
            'slug' => $slug
        ]);

        if ($translation) {
            return $this->enrichSkill($translation->getSkill(), $onetCode, $escoUri);
        }

        // -------------------------------------------------------------
        // 3. Search by Fuzzy Similarity (Levenshtein) if no exact match
        // -------------------------------------------------------------
        $firstLetter = substr($slug, 0, 1);

        $candidates = $transRepo->createQueryBuilder('t')
            ->join('t.language', 'l')
            ->where('l.code = :locale')
            ->andWhere('t.slug LIKE :prefix')
            ->setParameter('locale', $locale)
            ->setParameter('prefix', $firstLetter . '%')
            ->getQuery()
            ->getResult();

        $bestMatchSkill = null;
        $highestSimilarity = 0.0;
        $similarityThreshold = 85.0; // Min thershold  à 85%

        /** @var SkillTranslationEntity $candidate */
        foreach ($candidates as $candidate) {
            $candidateSlug = $candidate->getSlug();

            //-- levenshtein
            if (strlen($slug) > 255 || strlen($candidateSlug) > 255) {
                continue;
            }

            $similarity = $this->getSimilarityPercentage($slug, $candidateSlug);

            if ($similarity >= $similarityThreshold && $similarity > $highestSimilarity) {
                $highestSimilarity = $similarity;
                $bestMatchSkill = $candidate->getSkill();
            }
        }

        //-- find more than > 85
        if ($bestMatchSkill !== null) {
            return $this->enrichSkill($bestMatchSkill, $onetCode, $escoUri);
        }
        

        //-----------------------------
        //-- Generate Entity
        //------------------------------

        $skill = new SkillEntity();
        if ($escoUri) 
            $skill->setEscoUri($escoUri);
        if ($onetCode) 
            $skill->setOnetCode($onetCode);


        $translation = new SkillTranslationEntity(
            name: $name,
            slug: $slug,
            skill: $skill,
            language: $language,
        );
        $this->em->persist($translation);

        return $skill;
    }


    private function enrichSkill(SkillEntity $skill, ?string $onetCode = null, ?string $escoUri = null): SkillEntity
    {
        if ($onetCode && !$skill->getOnetCode()) {
            $skill->setOnetCode($onetCode);
        }
        if ($escoUri && !$skill->getEscoUri()) {
            $skill->setEscoUri($escoUri);
        }
        return $skill;
    }

    public function getSimilarityPercentage(string $str1, string $str2): float
    {
        $lev = levenshtein($str1, $str2);
        $maxLength = max(strlen($str1), strlen($str2));

        if ($maxLength === 0) {
            return 100.0;
        }

        // Formule : (1 - (distance / longueur_max)) * 100
        return (1 - ($lev / $maxLength)) * 100;
    }
}