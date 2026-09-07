<?php


namespace App\Tests\Support\Fixtures;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\Language\LanguageEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillAliasEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillTranslationEntity;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

use Override;


class SkillFixtures extends Fixture implements FixtureGroupInterface
{
    #[Override]
    public static function getGroups(): array
    {
        return ['skills'];
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        // Creating language
        $langEn = LanguageEntity::reconstitute(1, 'en', 'English');
        $langFr = LanguageEntity::reconstitute(2, 'fr', 'Français');
        
        $manager->persist($langEn);
        $manager->persist($langFr);

        // 12 mock skills
        $skillsData = [
            ['id' => '11111111-1111-1111-1111-111111111111', 'canonical' => 'python programming', 'en' => 'Python Programming', 'fr' => 'Programmation Python', 'alias' => 'Python'],
            ['id' => '22222222-2222-2222-2222-222222222222', 'canonical' => 'project management', 'en' => 'Project Management', 'fr' => 'Gestion de projet', 'alias' => 'PM'],
            ['id' => '33333333-3333-3333-3333-333333333333', 'canonical' => 'data analysis', 'en' => 'Data Analysis', 'fr' => 'Analyse de données', 'alias' => 'Data Analytics'],
            ['id' => '44444444-4444-4444-4444-444444444444', 'canonical' => 'machine learning', 'en' => 'Machine Learning', 'fr' => 'Apprentissage automatique', 'alias' => 'ML'],
            ['id' => '55555555-5555-5555-5555-555555555555', 'canonical' => 'docker containerization', 'en' => 'Docker Containerization', 'fr' => 'Conteneurisation Docker', 'alias' => 'Docker'],
            ['id' => '66666666-6666-6666-6666-666666666666', 'canonical' => 'sql database management', 'en' => 'SQL Database Management', 'fr' => 'Gestion de base de données SQL', 'alias' => 'SQL'],
            ['id' => '77777777-7777-7777-7777-777777777777', 'canonical' => 'git version control', 'en' => 'Git Version Control', 'fr' => 'Gestion de version Git', 'alias' => 'Git'],
            ['id' => '88888888-8888-8888-8888-888888888888', 'canonical' => 'agile methodology', 'en' => 'Agile Methodology', 'fr' => 'Méthodologie Agile', 'alias' => 'Agile'],
            ['id' => '99999999-9999-9999-9999-999999999999', 'canonical' => 'symfony framework', 'en' => 'Symfony Framework', 'fr' => 'Framework Symfony', 'alias' => 'Symfony'],
            ['id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'canonical' => 'api design', 'en' => 'API Design', 'fr' => 'Conception d API', 'alias' => 'REST API'],
            ['id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'canonical' => 'problem solving', 'en' => 'Problem Solving', 'fr' => 'Résolution de problèmes', 'alias' => 'Problem solving'],
            ['id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc', 'canonical' => 'technical writing', 'en' => 'Technical Writing', 'fr' => 'Rédaction technique', 'alias' => 'Tech writing'],
        ];

        foreach ($skillsData as $data) {
            $skill = SkillEntity::create(
                id: $data['id'],
                canonicalName: $data['canonical'],
                escoUri: 'http://data.europa.eu/esco/skill/' . $data['id'],
                isDefault: true
            );

            // Translatiosn EN & FR
            $transEn = SkillTranslationEntity::create($data['en'], strtolower(str_replace(' ', '-', $data['en'])), $skill, $langEn);
            $transFr = SkillTranslationEntity::create($data['fr'], strtolower(str_replace(' ', '-', $data['fr'])), $skill, $langFr);
            
            $skill->addTranslation($transEn);
            $skill->addTranslation($transFr);

            // Alias
            $alias = SkillAliasEntity::create(alias: $data['alias'], skill: $skill);

            $manager->persist($skill);
            $manager->persist($alias);
        }

        $manager->flush();
    }
}