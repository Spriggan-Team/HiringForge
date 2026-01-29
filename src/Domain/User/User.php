<?php


namespace App\Domain\User;

use App\Domain\Image\UploadedImage;
use App\Domain\Shared\ValueObject\Address;

final class User
{
    private UserId $id;
    public  string  $name;
    private string $email;
    private Siret $siret;
    private Address $address;
    private string  $password;

    /**
     * @var array<UploadedImage>  $images
     */
    
    private array  $images;
    
    private function __construct(
        UserId $id,
        string $name,
        string $email,
        Siret|int|string $siret,
        string $password,
        array $images,
        Address $address,
        ) {
        $this->id    = $id;
        $this->name  = $name;
        $this->email = $email;
        $this->siret = !$siret instanceof Siret ?  
                            new Siret($siret)
                            : $siret;
        $this->setPassword($password);
        $this->images = $images;
        $this->address = $address;
    }

    public static function create(
        string $name,
        string $email,
        string $siret,
        string $password,
        array $images,
        Address  $address,
        ?UserId  $userId = null
    ){
        return new self(
            $userId ?? new UserId(),
            $name,
            $email,
            $siret,
            $password,
            $images,
            $address,
        );
    }
    


    //  --------------- Business access
    
    public function id(): string { return $this->id->value(); }

    public function name(): string { return $this->name; }

    public function email(): string { return $this->email; }

    public function password(): string { return $this->password; }

    public function images(): array
    { 
        return $this->images;
    }

    public function siret(): string { return $this->siret->value(); }

    public function address(): Address { return $this->address; }

    // ---------------- Business change ----------------

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

    public function setPassword(string $password): static
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
        return $this;
    }

}
