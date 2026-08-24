<?php

namespace App\Api\Controllers\Candidate\Mapper;

use App\Domain\Shared\Address;

use App\Domain\Shared\Skill\BasicSkillModel;
use App\Application\DTO\Candidate\ChangeCandidateCommand;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ChangeCandidateMapper
{
    /**
     * @param array<int, array{id: string, name: string}> $skills
     * @param array{street?: string, city?: string, postalCode?: string, country?: string} $location
     */
    public static function fromArray(
        string $id,
        array $context,
        array $skills,
        array $location = [],
        ?UploadedFile $image = null,
    ): ChangeCandidateCommand {
        $address = null;

        if (
            isset(
                $location['city'],
                $location['country'],
                $location['street'],
                $location['postalCode'],
            )
        ) {
            $address = Address::create(
                city: $location['city'],
                country: $location['country'],
                street: $location['street'],
                postalCode: $location['postalCode'],
            );
        }

        return new ChangeCandidateCommand(
            id: $id,

            firstName: $context['firstName'] ?? '',
            lastName: $context['lastName'] ?? '',
            email: $context['email'] ?? '',

            image: $image,

            address: $address,

            searchRadius: isset($context['searchRadius'])
                ? (int) $context['searchRadius']
                : null,

            description: $context['description'] ?? null,

            skills: array_map(
                static fn (array $skill): BasicSkillModel => new BasicSkillModel(
                    id: (string) $skill['id'],
                    name: (string) $skill['name'],
                ),
                array_filter(
                    $skills,
                    static fn (array $skill): bool =>
                        !empty($skill['id']) &&
                        !empty($skill['name']),
                ),
            ),
        );
    }
}