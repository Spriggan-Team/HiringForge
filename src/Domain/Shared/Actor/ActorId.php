<?php


namespace App\Domain\Shared\Actor;


use DomainException;

class ActorId
{
    private string $id;

    /**
     * This class enforces id verification format.
     * If nothing is provided as an id, an id will be autmatically created created
     * @param ?string $id represents a potentially valid id
     */
    public function __construct(?string $id = null)
    {
        //Generate Id if nothing pass down to the constructor
        if(!$id){
            $this->id = self::generateId();
            return;
        }
        //Validate the id if $id parameter is passed to the constructor
        if(!self::isValid($id)){
            throw new DomainException("Invalid User Id");
        }
        //Generate UserId based on a given $id
        $this->id = $id;
    }


    public static function fromString(?string $id = null): static
    {
        return new self($id);
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
    private static function generateId(){
        return \Ramsey\Uuid\Uuid::uuid4();
    }
}