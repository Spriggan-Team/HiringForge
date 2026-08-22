<?php

namespace App\Domain\Shared;

/**
 * This is used to represent an address
 * Its only pupose shoul be to represent an address.
 * This classe is used later as a readonly for json parsing goals.
 */
final class Address
{
    /**
     * The constructor...
     *
     */
    private function __construct(
        public readonly string $city,
        public readonly string $postalCode,
        public readonly string $country,
        public readonly ?string $street = null,
        public readonly ?int $id = null,
        public readonly ?float $visibilityRange = null,
    ) {}

    /** 
     *  It enforces domain verification
     *  !IMPORTANT: Exception will be thrown when condition/requirement are not met
     */
    public static function create(
        string $city,
        string $postalCode,
        string $country,
        ?string $street = null,
        ?float $visibilityRange = null,
        ?int $id = null,
    ):self
    {
        if (!$postalCode || trim($postalCode) === '') {
            throw new \InvalidArgumentException('Postal code required');
        }
        if(!$country || trim($country) == '')
        {
            throw new \InvalidArgumentException('Country name is required');
        }
        return new self(
            id: $id,
            city: $city,
            street: $street,
            postalCode: $postalCode,
            country: $country,
            visibilityRange: $visibilityRange
        );
    }
    


    /** 
     * 
     * This function try creating a Address object base on some input values (array)
     * if it went successfully it will return the object otherwise it will give back a null value
     * 
     * !!IMPORTANT: its use lies in the fact that it doesn't return an exception (DomainException).
     *              Remember here you are just trying to create only when the array input meet the requirements,
     *              otherwise you just dismiss it and get back a "null" value
     * 
     * @param array $data   An associative array containing all the necessary information
     *                      to create an address object.
     * @return ?self
    */
    public static function tryCreate(array $data): ?self
    {
        $isSomethingMissing = empty($data['postalCode']) ||
                              empty($data['country']);

        if(!$isSomethingMissing)
        {
            return  self::create(
                city: $data["city"],
                street: $data["street"] ?? null,
                postalCode: $data["postalCode"],
                country: $data['country'],
                visibilityRange: $data['visibilityRange'] ?? 10.0
            );
        }
        return null;
    }


    /**
     * Here you create an address without any minimum control on the entries
     * 
     * WARNING: To use with cautiouness, because data validation are not fone here.
     *          You should only use this type of function when your purpose is purely to meet performance 
     *          and when you are sure of the validity of your data beforehand.
     */
    public static function hydrate(
        string $city,
        string $postalCode,
        string $country,
        ?string $street = null,
        ?int $id = null,
        ?float $visibilityRange = null,
    ): self
    {
        return new self(
            id: $id,
            city: $city,
            street: $street,
            postalCode: $postalCode,
            country: $country,
            visibilityRange: $visibilityRange
        );
    }

    //------------------------
    //--- Data representation Methods (Formatting)
    //---------------------------
    
    public function __toString()
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return "{$this->street}, {$this->postalCode} , {$this->country}";
    }

    public function toArray(){
        return [
            "id" => $this->id,
            "street" => $this->street,
            "country" => $this->country,
            "postalCode" => $this->postalCode,
            "visibilityRange" => $this->visibilityRange,
        ];
    }
}
