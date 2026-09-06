<?php

namespace App\Infrastructure\Persistence\Commands;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Language\LanguageEntity;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


#[AsCommand(
    name: 'app:seed-languages',
    description: 'Peuple la table des langues de base (fr, en, es, etc.)'
)]
class ImportLanguagesCommand extends Command
{
    // Liste initiale des langues courantes (code => label)
    private const DEFAULT_LANGUAGES = [
        'en' => 'English',
        'fr' => 'Français',
        'es' => 'Español',
        'de' => 'Deutsch',
        'it' => 'Italiano',
        'pt' => 'Português',
        'nl' => 'Nederlands',
        'pl' => 'Polski',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Initialisation / Peuple des Langues');

        $repository = $this->entityManager->getRepository(LanguageEntity::class);
        $createdCount = 0;
        $existingCount = 0;

        foreach (self::DEFAULT_LANGUAGES as $code => $label) {
            // Vérifie si la langue existe déjà pour éviter les doublons
            $existingLanguage = $repository->findOneBy(['code' => $code]);

            if ($existingLanguage) {
                $existingCount++;
                continue;
            }

            // Utilisation de ta méthode statique create()
            $language = LanguageEntity::create($code, $label);
            
            $this->entityManager->persist($language);
            $createdCount++;
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            'Terminé ! %d langue(s) ajoutée(s), %d déjà existante(s).',
            $createdCount,
            $existingCount
        ));

        return Command::SUCCESS;
    }
}