<?php

namespace App\Domain\Company;


use App\Domain\File\StaticMedia;
use App\Domain\File\TimedMedia;
use App\Domain\Shared\Address;
use App\Domain\Shared\CustomUUID;
use App\Domain\User\Siret;
use App\Domain\User\UserId;



class Company{
    private string $id; //-- uuid
    private string $name;

    private Siret $siret;
    
    /**
     * @var array<int, Address>
     */
    private array $address;

    private ?StaticMedia $logo = null;


    /** @var array<int, string> */
    private array $recruiters = [];
    

    /**
     * @var array<int, StaticMedia>  $images
     */
    private array $images = [];

    /**
     * @var ?UploadedFile
     * This property is a video presentation of the user
     * it is optionnal
     */
    private ?TimedMedia $videoPresentation = null;



    public function __construct(
        string $id,
        string $name,
        Siret $siret,

        /** @var array<int, Address> */
        array  $address,
        /** @var @var array<int, StaticMedia> */
        array $images= [],

        array $recruiters = [],
        ?StaticMedia $logo = null,   
    ){
        $this->id = $id;
        $this->address = $address;

        $this->images = $images;
        $this->name = $name;
        $this->logo = $logo;
        $this->siret = $siret;

        $this->recruiters = $recruiters;
    }



    /***
     * The public methode for creating an user domain object.
     */
    public static function create(
        string $name,
        Siret $siret,
        array  $address=[],
        array $images= [],
        ?string $id = null,
        ?StaticMedia $logo = null,
    ){
        return new self(
            id: $id ?? CustomUUID::generate(),
            name: $name,
            images: $images,
            address: $address,
            logo: $logo,
            siret: $siret,
        );
    }

    public static function hydrate(
        string $id,
        string $name,
        Siret $siret,
        /** @var array<int, Address> */
        array  $address,
        /** @var @var array<int, StaticMedia> */
        array $images,
        array $recruiters,
        ?StaticMedia $logo,   
    ):self
    {
        return new self(
            id: $id,
            name: $name,
            siret: $siret,
            address: $address,
            images: $images,
            logo: $logo,
            recruiters: $recruiters
        );
    }
    
    //----------------------------
    //   Business access
    //--------------------------

    public function id(){
        return $this->id;
    }

    public function videoPresentation(): ?TimedMedia{
        return $this->videoPresentation;
    }


    public function name(): string {
        return $this->name;
    }

    public function logo(): ?StaticMedia{
        return $this->logo;
    }

    public function siret(): string { return $this->siret->value(); }

    /**
     * @return array<int, Address>
     */
    public function address(): array { return $this->address; }

    
    /**
     * @return array<int, StaticMedia>
     */
    public function images() : array {
        return $this->images;
    }

    /**
     * @return array<int, string>
     */
    public function recruiters(): array {
        return $this->recruiters;
    }
    
    //------------------------------------------
    // - Business change --
    //-----------------------------------------

    public function setLogo(StaticMedia $logo): static{
        $this->logo = $logo;
        return $this;
    }

    public function addAddress(Address $address): static{
        $this->address[] = $address;
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

    public function addRecruiter(string $recruiterId): static{
        $this->recruiters[] = $recruiterId;
        return $this;
    }

    /**
     * Tell if the image respect the format, size limitation and ....
     * before associting it to an user
     */
    public function addImages(StaticMedia $image):static
    {
        $image->mustBe(sizeLimitation: 12500000);
        $this->images[] = $image;
        return $this;
    }

    public function removeImage(StaticMedia $image): static
    {
        foreach ($this->images as $key => $img) {
            if ($img->name === $img->name) {
                unset($images[$key]);
                break;
            }
        }
        return $this;
    }


    public function addVideoPresentation(?TimedMedia $videoPresentation): static
    {
        $videoPresentation->mustBe(type: "video", secondsLimitation: 720);
        $this->videoPresentation = $videoPresentation;
        return $this;
    }


    public function removeVideoPresentation(): static
    {
        $this->videoPresentation = null;
        return $this;
    }

    
}