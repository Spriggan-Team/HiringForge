<?php

namespace App\Infrastructure\Persistence\Commands;


use App\Infrastructure\Persistence\Doctrine\ORM\Global\Contract\ContractTypeEntity;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;




#[AsCommand(
    name: 'app:seed:contract-types',
    description: 'Insert default contact type',
)]
class SeedContractTypesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        $contractTypes = [
            [
                'label' => 'CDI',
                'country' => 'FR',
            ],
            [
                'label' => 'CDD',
                'country' => 'FR',
            ],
            [
                'label' => 'Stage',
                'country' => 'FR',
            ],
            [
                'label' => 'Alternance',
                'country' => 'FR',
            ],
            [
                'label' => 'Intérim',
                'country' => 'FR',
            ],
            [
                'label' => 'Freelance',
                'country' => 'FR',
            ],
            [
                'label' => 'Temps partiel',
                'country' => 'FR',
            ],
            [
                'label' => 'Temps plein',
                'country' => 'FR',
            ],
        ];

        $repository = $this->entityManager->getRepository(ContractTypeEntity::class);

        $created = 0;
        $skipped = 0;

        foreach ($contractTypes as $contractTypeData) {
            $existing = $repository->findOneBy([
                'label' => $contractTypeData['label'],
                'country' => $contractTypeData['country'],
                'organizationId' => null,
            ]);

            if ($existing !== null) {
                $skipped++;

                $io->text(
                    sprintf(
                        '⏭ Already present : %s (%s)',
                        $contractTypeData['label'],
                        $contractTypeData['country']
                    )
                );

                continue;
            }

            $contractType = new ContractTypeEntity();

            $contractType
                ->setLabel($contractTypeData['label'])
                ->setCountry($contractTypeData['country'])
                ->setDefault(true)
                ->setOrganizationId(null);

            $this->entityManager->persist($contractType);

            $created++;

            $io->text(
                sprintf(
                    '✓ Created : %s (%s)',
                    $contractTypeData['label'],
                    $contractTypeData['country']
                )
            );
        }

        $this->entityManager->flush();

        $io->newLine();

        $io->success(
            sprintf(
                '%d created type, %d already present(s).',
                $created,
                $skipped
            )
        );

        return Command::SUCCESS;
    }
}