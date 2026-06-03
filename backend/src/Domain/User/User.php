<?php


namespace App\Domain\User;

use App\Domain\File\StaticMedia;
use App\Domain\File\TimedMedia;

use App\Domain\Shared\Address;
use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\Account\Account;



final class User implements Account
{
    private UserId $id;
    public  string  $name;
    private Siret $siret;
    private Address $address;

    private EmailAddress  $email;
    private string  $passwordHash;

    private ?string $description = null;

    /**
     * @var array<StaticMedia>  $images
     */
    private array  $images;

    /**
     * @var ?UploadedFile
     * This property is a video presentation of the user
     * it is optionnal
     */
    private ?TimedMedia $videoPresentation = null;
    
    private function __construct(
        UserId $id,
        string $name,
        EmailAddress $email,
        Siret $siret,
        string $passwordHash,
        array $images,
        Address $address, 
        ?TimedMedia $videoPresentation = null,
    ) {
        $this->id    = $id;
        $this->name  = $name;
        $this->email = $email;
        $this->siret = $siret;
        $this->passwordHash = $passwordHash;
        $this->images = $images;
        $this->address = $address;
        $this->videoPresentation = $videoPresentation;
    }

    /***
     * The public methode for creating an user domain object.
     */
    public static function create(
        UserId  $userId,
        string $name,
        EmailAddress $email,
        Siret $siret,
        string $passwordHash,
        Address  $address,
        array $images= [],
        ?TimedMedia $videoPresentation=null,
    ){
        return new self(
            $userId ?? UserId::create(),
            $name,
            $email,
            $siret,
            $passwordHash,
            $images,
            $address,
            $videoPresentation,
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

    /**
     * @return array<StaticMedia> images
     */
    public function images(): array
    { 
        return $this->images;
    }

    public function videoPresentation(): ?TimedMedia{
        return $this->videoPresentation;
    }

    public function siret(): string { return $this->siret->value(); }

    public function address(): Address { return $this->address; }

    public function description() : ?string
    {
        return $this->description;    
    }

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

    /**
     * This function change the value of the siret while enforcing
     * mandatory control
     * @param Siret $other      The new value object to use to make change
     * @throws \DomainException It is raised when the control failed
     * @return void             If the function make its way here without any exception thrown,
     *                          then everything went smoothly
     */
    public function changeSiret(Siret $other){
        if(
            $this->siret->siren() === $other->siren()
        ){
            $this->siret = $other;
            return $this;
        }
        throw new \DomainException("You must meet the conditions of validation to change a siret");
    }


    
    public function setPassword(
        string $passwordHash, 
    ): static
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }


    /**
     * Tell if the image respect the format, size limitation and ....
     * before associting it to an user
     */
    public function addImages(StaticMedia $image):static
    {
        $image->mustBe(sizeLimitation: 18432);
        $this->images[] = $image;
        return $this;
    }

    public function removeImage(StaticMedia $image)
    {}


    public function addVideoPresentation(?TimedMedia $videoPresentation): static
    {
        $videoPresentation->mustBe(type: "video", secondsLimitation: 15);
        $this->videoPresentation = $videoPresentation;
        return $this;
    }

    public function removeVideoPresentation(): static
    {
        $this->videoPresentation = null;
        return $this;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }
}
