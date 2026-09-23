<?php

namespace App\Domain\Company;

use App\Domain\Department\Department;
use App\Domain\File\StaticMedia;
use App\Domain\File\TimedMedia;
use App\Domain\Shared\Address;
use App\Domain\Shared\CustomUUID;
use App\Domain\User\Siret;
use App\Domain\User\UserId;



class Company{
    private string $id; //-- uuid
    private string $name;
    private ?string $description = null;

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

    private ?StaticMedia $mainImage = null;

    /**
     * @var ?TimedMedia
     * This property is a video presentation of the user
     * it is optionnal
     */
    private ?TimedMedia $videoPresentation = null;


    /**
     * @var array<int,Department> departments
     */
    private array $departments = [];


    public function __construct(
        string $id,
        string $name,
        Siret $siret,

        /** @var array<int, Address> */
        array  $address,
        ?string $description = null,
        
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
        $this->description = $description;
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
        ?string $description = null,
        ?StaticMedia $logo = null,
    ){
        return new self(
            id: $id ?? CustomUUID::generate(),
            name: $name,
            images: $images,
            address: $address,
            logo: $logo,
            siret: $siret,
            description: $description
        );
    }

    public static function hydrate(
        string $id,
        string $name,
        Siret $siret,

        /** @var @var array<int, StaticMedia> */
        array $images = [],

        /** @var array<int,string> */
        array $recruiters = [],

        /** @var array<int, Address> */
        array $address = [],

        /** @var array<int,Department> departments */
        array $departments = [],

        ?StaticMedia $mainImage = null,

        ?StaticMedia $logo = null,

        ?TimedMedia $videoPresentation = null
    ):self
    {
        $domain = new self(
            id: $id,
            name: $name,
            siret: $siret,
            address: $address,
            images: $images,
            logo: $logo,
            recruiters: $recruiters,
        );

        $domain->mainImage = $mainImage;
        $domain->address  = $address;
        $domain->departments = $departments;
        $domain->videoPresentation = $videoPresentation;

        return $domain;
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

    public function description(){
        return $this->description;
    }

    public function logo(): ?StaticMedia{
        return $this->logo;
    }

    public function siret(): string { return $this->siret->value(); }

    /**
     * @return array<int, Address>
     */
    public function address(): array { return $this->address; }

    
    public function imageWithId(int $id){
        foreach($this->images as $img){
           if($img->id === $id){
                return $img;
           } 
        }
        return null;
    }

    /**
     * @return array<int, StaticMedia>
     */
    public function images() : array {
        return $this->images;
    }

    public function mainImage(): ?StaticMedia{
        return $this->mainImage;
    }

    /**
     * @return array<int, string>
     */
    public function recruiters(): array {
        return $this->recruiters;
    }
    

    /**
     * @return array<int,Department> departments
     */
    public function departments():array{
        return $this->departments;
    }


    //------------------------------------------
    // - Business change --
    //-----------------------------------------

    public function rename(string $name) : static {
        $this->name = $name;
        return $this;
    }

    public function changeDescription(?string $description = null){
        $this->description = $description;
        return $this;
    }

    public function setLogo(?StaticMedia $logo = null): static{
        $this->logo = $logo;
        return $this;
    }

    public function addAddress(Address $address): static{
        $this->address[] = $address;
        return $this;
    }

    public function removeAddressById(int $id): static
    {
        foreach($this->address as $key => $address){
            if ($address->id === $id) { //-- id
                unset($this->address[$key]);
                break;
            }
        }
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
     * Add images to company
     * Tell if the image respect the format, size limitation and ....
     * before associting it to an user
     */
    public function addImages(StaticMedia $image):static
    {
        $image->mustBe(sizeLimitation: 12500000);
        $this->images[] = $image;
        return $this;
    }

    public function changeMainImage(?StaticMedia $media){
        $this->mainImage = $media;
        return $this;
    }

    public function removeImage(StaticMedia $image): static
    {
        foreach ($this->images as $key => $img) {
            if ($image->name === $img->name) {
                unset($this->images[$key]);
                $this->images = array_values($this->images);
                break;
            }
        }
        return $this;
    }


    public function removeImageById(int $id): static
    {
        foreach ($this->images as $key => $img) {
            if ($img->id === $id) {
                unset($this->images[$key]);
                $this->images = array_values($this->images);
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


    /**
     * @var array<int,Department> $departments
     */
    public function setDepartments(array $departments){
        $this->departments = $departments;
        return $this;
    }

    public function addDepartment(Department $department){
        $this->departments[] = $department;
        return $this;
    }


    public function removeDepartmentById(int $departmentId){
        foreach ($this->departments as $key => $department) {
            if ($department->id() === $departmentId) {
                unset($this->departments[$key]);
                break;
            }
        }
        return $this;
    }
    
}