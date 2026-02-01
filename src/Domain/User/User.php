<?php


namespace App\Domain\User;

use App\Domain\File\StaticMedia;
use App\Domain\File\TimedMedia;

use App\Domain\Shared\Address;
use App\Domain\Shared\Actor\Actor;
use App\Domain\Shared\Actor\ActorRole;

final class User implements Actor
{
    private UserId $id;
    public  string  $name;
    private Siret $siret;
    private Address $address;

    private string  $email;
    private string  $passwordHash;

    /**
     * @var array<StaticMedia>  $images
     */
    private array  $images;

    /**
     * @var ?UploadedFile
     * This property is a video presentation of the user
     * it is optionnal
     */
    private ?TimedMedia $presentation = null;
    
    private function __construct(
        UserId $id,
        string $name,
        string $email,
        Siret $siret,
        string $passwordHash,
        array $images,
        Address $address, 
        ?TimedMedia $presentation = null,
    ) {
        $this->id    = $id;
        $this->name  = $name;
        $this->email = $email;
        $this->siret = $siret;
        $this->passwordHash = $passwordHash;
        $this->images = $images;
        $this->address = $address;
        $this->presentation = $presentation;
    }

    public static function create(
        string $name,
        string $email,
        Siret $siret,
        string $passwordHash,
        array $images,
        Address  $address,
        ?UserId  $userId = null,
        ?TimedMedia $presentation=null,
    ){
        return new self(
            $userId ?? new UserId(),
            $name,
            $email,
            $siret,
            $passwordHash,
            $images,
            $address,
            $presentation,
        );
    }
    

    //----------------------------
    //   Business access
    //--------------------------
    
    public function id(): string 
    {
        return $this->id->value();
    }

    public function name(): string {
        return $this->name;
    }

    public function email(): string 
    { 
        return $this->email;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function images(): array
    { 
        return $this->images;
    }

    public function siret(): string { return $this->siret->value(); }

    public function address(): Address { return $this->address; }

    //------------------------------------------
    // - Business change --
    //-----------------------------------------

    public function rename(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function changeSiret(Siret $siret){
        $this->siret = $siret;
        return $this;
    }

    public function setPassword(
        string $passwordHash, 
    ): static
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    public function role(): ActorRole
    {
        throw new \Exception('Not implemented');
    }
}
