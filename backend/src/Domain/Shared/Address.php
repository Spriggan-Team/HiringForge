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
        public readonly string $postalCode,
        public readonly string $country,
        public readonly ?string $street = null,
    ) {}

    /** 
     *  It enforces domain verification
     *  !IMPORTANT: Exception will be thrown when condition/requirement are not met
     */
    public static function create(
        string $postalCode,
        string $country,
        ?string $street = null,
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
            $street,
            $postalCode,
            $country,
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
                $data["street"] ?? null,
                $data["postalCode"],
                $data['country'],
                $data['visibilityRange'] ?? 10.0
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
        ?string $street = null,
        string $postalCode,
        string $country,
    ): self
    {
        return new self(
            street: $street,
            postalCode: $postalCode,
            country: $country
        );
    }

    //------------------------
    //---Data representation Methods
    //---------------------------
    
    public function __toString()
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return "{$this->street}, {$this->postalCode} , {$this->country}";
    }


}
