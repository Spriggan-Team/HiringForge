<?php


namespace App\Application\DTO\Company;

use App\Application\DTO\EditImageDto;
use App\Application\DTO\Department\EditDepartmentDto;
use App\Application\DTO\Location\EditLocationDto;

final class EditCompanyDto
{
    public ?string $name = null;

    public ?string $siret = null;

    public ?string $description = null;

    public ?EditImageDto $logo = null;

    public ?EditImageDto $videoPresentation = null;

    /** @var EditDepartmentDto[] */
    public array $departmentsAdded = [];

    /** @var int[] */
    public array $departmentsRemoved = [];

    /** @var EditLocationDto[] */
    public array $locationsAdded = [];

    /** @var int[] */
    public array $locationsRemoved = [];

    /** @var EditCompanyImageDto[] */
    public array $imagesAdded = [];

    /** @var int[] */
    public array $imagesRemoved = [];
}
