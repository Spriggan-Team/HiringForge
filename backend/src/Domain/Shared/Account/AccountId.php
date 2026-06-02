<?php


namespace App\Domain\Shared\Account;


use DomainException;

abstract class AccountId
{
    public function __construct(
        private string $id
    ){}

    /**
     * This class enforces id verification format.
     * If nothing is provided as an id, an id will be autmatically created created
     * @param ?string $id represents a potentially valid id
     */
    public static function create(?string $id = null): self
    {
        //Generate Id if nothing pass down to the constructor
        if(!$id){
            return new static(self::generateId());
        }
        //Validate the id if $id parameter is passed to the constructor
        if(!self::isValid($id)){
            throw new DomainException("Invalid User Id");
        }
        //Generate UserId based on a given $id
        return new static($id);
    }

    /**
     * WARNING: This function is not safe bacause it doesn't apply buisness logic
     */
    public static function hydrate(string $id): static
    {
        return new static($id);
    }

    /**
     * Its give back the (string) id
     */
    public function value(): string
    {
        return $this->id;
    }

    /**
     * It encapsculate how an id is supposed to be valid
     */
    public static function isValid(string $id)
    {
        return \Ramsey\Uuid\Uuid::isValid($id);
    }

    /**
     * It encapsculates the id genretaing logic
     */
    private static function generateId(): string 
    {
        return \Ramsey\Uuid\Uuid::uuid4();
    }
}