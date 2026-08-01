<?php

namespace App\Application\DTO\JobOffer;

use Symfony\Component\Validator\Constraints as Assert;


final class CreateJobOfferRequest
{
    public function __construct(

        #[Assert\NotBlank]
        #[Assert\Length(
            min: 10,
            max: 255
        )]
        public string $title,



        #[Assert\NotBlank]
        #[Assert\Type(type: 'array')]
        public array $content,


        /**
         * @var string[]
         */
        #[Assert\Type(type: 'array')]
        public array $categories = [],


        /**
         * Skill identifiers
         *
         * @var string[]
         */
        #[Assert\Type(type: 'array')]
        public array $skills = [],

        
        /**
         * Required languages identifiers
         *
         * @var RequiredLanguageRequest[] $languages
         */
        #[Assert\Type(type: 'array')]
        public array $languages = [],


        /**
         * Contract type identifier
         */
        #[Assert\Uuid]
        public ?string $contractTypeId = null,


        /**
         * Department identifier
         */
        #[Assert\Uuid]
        public ?int $departmentId = null,


        /**
         * Work mode 
         * ex: remote; onsite; hybrid
         */
        public ?string $workMode = null,


        /**
         * describe the place where the work
         * is located at
         * exemple: [
         *      "id" => string,
         *      "city" => ?city,
         *      "street" => ?street,
         *      "country" => ?country
         * ]
        */
        public array $location = [],

        /**
         * Expertise 
         */
        #[Assert\Uuid]
        public ?string $expertise = null,


        /**
         * Salary information
         *
         * Example:
         * [
         *      "min" => 40000,
         *      "max" => 60000,
         *      "currency" => "EUR"
         * ]
         */
        #[Assert\Type(type: 'array')]
        public ?SalaryRequest $salary = null,


        /**
         * Main offer image
         */
        public mixed $image = null,


        /**
         * Visibility at creation
         */
        public ?string $visibilityStatus = null,

        /**
         * Planned publication datte
         */
        public ?\DateTimeImmutable $publicationDate = null,
    ) {}
}