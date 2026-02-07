<?php


namespace App\Domain\User;

use App\Domain\File\StaticMedia;
use App\Domain\File\TimedMedia;

use App\Domain\Shared\Address;
use App\Domain\Shared\Actor\Actor;
use App\Domain\Shared\Actor\ActorRole;
use App\Domain\Shared\EmailAddress;

final class User implements Actor
{
    private UserId $id;
    public  string  $name;
    private Siret $siret;
    private Address $address;

    private EmailAddress  $email;
    private string  $passwordHash;

    private ?string $desc = null;

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
        EmailAddress $email,
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

    /***
     * The public methode for creating an user domain object.
     */
    public static function create(
        string $name,
        EmailAddress $email,
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
        return $this->email->value();
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function images(): array
    { 
        return $this->images;
    }

    public function presentation(): ?TimedMedia{
        return $this->presentation;
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

    public function setEmail(EmailAddress $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function changeSiret(Siret $siret){
        $this->siret->change($siret);
        return $this;
    }

    public function setPassword(
        string $passwordHash, 
    ): static
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    public function addImages(StaticMedia $image)
    {}

    public function removeImage(string $uniqName)
    {}

    public function setPresentation(?TimedMedia $presentation): static
    {
        $this->presentation = $presentation;
        return $this;
    }

    public function role(): ActorRole
    {
        throw new \Exception('Not implemented');
    }
}
