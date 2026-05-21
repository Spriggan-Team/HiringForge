<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Fixtures;

use App\Domain\JobOffer\JobStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\User\Fixtures\UserFixtures;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;


use Ramsey\Uuid\Uuid;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;



class JobOfferFixtures extends Fixture implements DependentFixtureInterface
{
    public const JOBS = [
        ['Backend Developer PHP', 'Développement d’APIs Symfony et maintenance applicative'],
        ['Frontend React Developer', 'Développement d’interfaces modernes en React'],
        ['DevOps Engineer', 'CI/CD, Docker, Kubernetes et monitoring'],
        ['Data Scientist', 'Analyse de données et modèles prédictifs'],
        ['Product Owner', 'Gestion du backlog et coordination produit'],
        ['UX/UI Designer', 'Conception d’interfaces centrées utilisateur'],
        ['Mobile Developer Flutter', 'Développement d’applications mobiles cross-platform'],
        ['QA Engineer', 'Tests automatisés et qualité logicielle'],
        ['Cybersecurity Analyst', 'Audit sécurité et protection des systèmes'],
        ['Cloud Architect', 'Architecture cloud AWS/GCP'],
        ['AI Engineer', 'Développement de solutions IA'],
        ['Scrum Master', 'Animation des cérémonies agiles'],
        ['Business Analyst IT', 'Analyse des besoins métiers'],
        ['Software Architect', 'Conception d’architectures logicielles'],
        ['Embedded Systems Engineer', 'Développement bas niveau embarqué'],
        ['Game Developer Unity', 'Développement de jeux vidéo'],
        ['SEO Specialist', 'Optimisation du référencement'],
        ['Digital Marketing Manager', 'Stratégie marketing digital'],
        ['IT Support Technician', 'Support technique utilisateurs'],
        ['System Administrator', 'Gestion serveurs et réseaux'],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::JOBS as $i => [$title, $description]) {
            $job = (new JobOfferEntity())
                ->setId(Uuid::uuid4()->toString())
                ->setTitle($title)
                ->setContent([
                    'description' => $description,
                    'requirements' => 'Expérience 2+ ans, esprit d’équipe, autonomie',
                ])
                ->setCreatedAt(new \DateTimeImmutable('-'.random_int(1, 30).' days'))
                ->setUpdatedAt(new \DateTimeImmutable())
                ->setStatus(JobStatus::PUBLISHED)
                ->setUser($this->getReference('user_'.($i % 20), UserEntity::class));

            $manager->persist($job);
            $this->addReference('job_'.$i, $job);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
