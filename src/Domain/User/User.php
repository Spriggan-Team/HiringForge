<?php


namespace App\Domain\User;


final class User
{
    private UserId $id;
    public string  $name;
    private string $email;
    private string $siret;
    private string $password;
    private string $imagePath;

    private function __construct(
        UserId $id,
        string $name,
        string $email,
        string $siret,
        string $password,
        string $imagePath,
    ) {
        $this->id    = $id;
        $this->name  = $name;
        $this->email = $email;
        $this->siret = $siret;
        $this->setPassword($password);
        $this->imagePath = $imagePath;
    }

    public static function create(
        UserId $id,
        string $name,
        string $email,
        string $siret,
        string $password,
        string $imagePath,
    ){
        return new self(
            $id,
            $name,
            $email,
            $siret,
            $password,
            $imagePath
        );
    }
    


    //  --------------- Business access
    
    public function id(): UserId { return $this->id; }

    public function name(): string { return $this->name; }

    public function email(): string { return $this->email; }

    public function password(): string { return $this->password; }

    public function imagePath(): string { return $this->imagePath; }

    public function siret(){ return $this->siret; }

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
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

}
