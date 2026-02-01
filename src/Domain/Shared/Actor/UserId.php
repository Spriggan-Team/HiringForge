<?php


namespace App\Domain\Shared\Actor;


use DomainException;

class ActorId
{
    private string $id;

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


    public function value(): string
    {
        return $this->id;
    }


    public static function isValid(string $id)
    {
        return \Ramsey\Uuid\Uuid::isValid($id);
    }

    private static function generateId(){
        return \Ramsey\Uuid\Uuid::uuid4();
    }
}