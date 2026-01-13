<?php


namespace App\Domain\Entity;

final class User
{
    private string $id;
    public string $name;
    private string $email;
    private string $siret;
    private string $password;

    private array $snapshot;

    public function __construct(
        string $id,
        string $name,
        string $email,
        string $siret,
        string $password
    ) {
        $this->id    = $id;
        $this->name  = $name;
        $this->email = $email;
        $this->siret = $siret;
        $this->setPassword($password);
        $this->takeSnapshot();
    }

    private function takeSnapshot(): void
    {
        $this->snapshot = [
            'name'     => $this->name,
            'email'    => $this->email,
            'siret'    => $this->siret,
            'password' => $this->password,
        ];
    }

    //  --------------- Business access
    
    public function getId(): string { return $this->id; }

    public function getName(): string { return $this->name; }

    public function getEmail():string { return $this->email; }

    public function getPassword():string { return $this->password; }

    public function getSiret(){ return $this->siret; }

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

    // ---------------- Diff ----------------

    public function hasChanged(string $field): bool
    {
        if (!array_key_exists($field, $this->snapshot)) {
            throw new \InvalidArgumentException("Unknown field $field");
        }

        return $this->$field !== $this->snapshot[$field];
    }

    public function changedFields(): array
    {
        $changes = [];

        foreach ($this->snapshot as $field => $value) {
            if ($this->$field !== $value) {
                $changes[$field] = $this->$field;
            }
        }

        return $changes;
    }
}
