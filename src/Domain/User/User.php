<?php


namespace App\Domain\User;

use App\Domain\Shared\ValueObject\Address;

final class User
{
    private UserId $id;
    public string  $name;
    private string $email;
    private string $siret;
    private Address $address;
    private string  $password;
    private array   $images;

    private function __construct(
        UserId $id,
        string $name,
        string $email,
        string $siret,
        string $password,
        array $images,
        Address $address,
    ) {
        $this->id    = $id;
        $this->name  = $name;
        $this->email = $email;
        $this->siret = $siret;
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
        Address $address,
    ){
        return new self(
            new UserId(),
            $name,
            $email,
            $siret,
            $password,
            $images,
            $address,
        );
    }
    


    //  --------------- Business access
    
    public function id(): UserId { return $this->id; }

    public function name(): string { return $this->name; }

    public function email(): string { return $this->email; }

    public function password(): string { return $this->password; }

    public function images(): array { return $this->images; }

    public function siret(){ return $this->siret; }

    public function address(): Address { return $this->address; }

    // ---------------- Business change ----------------

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
    }

}
