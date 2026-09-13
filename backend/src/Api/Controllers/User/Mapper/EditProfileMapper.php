<?php

namespace App\Api\Controllers\User\Mapper;

use App\Application\DTO\Company\EditCompanyDto;
use App\Application\DTO\Company\EditCompanyImageDto;
use App\Application\DTO\Department\EditDepartmentDto;
use App\Application\DTO\EditImageDto;
use App\Application\DTO\Location\EditLocationDto;
use App\Application\DTO\User\Edition\EditProfileDto;
use App\Application\DTO\User\Edition\EditUserDto;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;


final class EditProfileMapper
{
    public function map(Request $request): EditProfileDto
    {
        $data = $request->request->all();
        $files = $request->files->all();

        $dto = new EditProfileDto();

        if (isset($data['company']) || isset($files['company'])) {
            $dto->company = $this->mapCompany(
                $data['company'] ?? [],
                $files['company'] ?? []
            );
        }

        if (isset($data['user']) || isset($files['user'])) {
            $dto->user = $this->mapUser(
                $data['user'] ?? [],
                $files['user'] ?? []
            );
        }

        return $dto;
    }

    private function mapCompany(
        array $data,
        array $files
    ): EditCompanyDto {
        $dto = new EditCompanyDto();

        $dto->name = $data['name'] ?? null;
        $dto->siret = $data['siret'] ?? null;
        $dto->description = $data['description'] ?? null;

        $dto->logo = $this->mapImage(
            $data['logo'] ?? [],
            $files['logo'] ?? []
        );

        $dto->videoPresentation = $this->mapImage(
            $data['videoPresentation'] ?? [],
            $files['videoPresentation'] ?? []
        );

        $departments = $data['departments'] ?? [];

        foreach ($departments['added'] ?? [] as $department) {
            $item = new EditDepartmentDto();
            $item->name = $department['name'];

            $dto->departmentsAdded[] = $item;
        }

        foreach ($departments['removed'] ?? [] as $department) {
            $dto->departmentsRemoved[] = (int) $department['id'];
        }

        $locations = $data['location'] ?? [];

        foreach ($locations['added'] ?? [] as $location) {
            $item = new EditLocationDto();

            $item->country = $location['country'];
            $item->street = $location['street'];
            $item->postalCode = $location['postalCode'];
            $item->city = $location['city'];

            $dto->locationsAdded[] = $item;
        }

        foreach ($locations['removed'] ?? [] as $location) {
            $dto->locationsRemoved[] = (int) $location['id'];
        }

        $imagesData = $data['images']['added'] ?? [];
        $imagesFiles = $files['images']['added'] ?? [];

        foreach ($imagesData as $index => $image) {
            $item = new EditCompanyImageDto();

            $item->isMain = filter_var(
                $image['isMain'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

            $item->file = $imagesFiles[$index]['file'] ?? null;

            $dto->imagesAdded[] = $item;
        }

        foreach ($data['images']['removed'] ?? [] as $image) {
            $dto->imagesRemoved[] = (int) $image['id'];
        }

        return $dto;
    }

    private function mapUser(
        array $data,
        array $files
    ): EditUserDto {
        $dto = new EditUserDto();

        $dto->firstName = $data['firstName'] ?? null;
        $dto->lastName = $data['lastName'] ?? null;
        $dto->description = $data['description'] ?? null;
        $dto->email = $data['email'] ?? null;

        $dto->image = $this->mapImage(
            $data['image'] ?? [],
            $files['image'] ?? []
        );

        return $dto;
    }

    private function mapImage(
        array $data,
        array $files
    ): ?EditImageDto {
        if ($data === [] && $files === []) {
            return null;
        }

        $dto = new EditImageDto();

        if (isset($data['id'])) {
            $dto->id = (int) $data['id'];
        }

        $dto->file = $files['file'] ?? null;

        return $dto;
    }
}