<?php


namespace App\Domain\User;

use App\Domain\File\StaticMedia;

use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\Account\Account;
use App\Domain\Shared\CustomUUID;

final class User extends Account
{    
    private UserRole $role;
    private  string $companyId;

    private function __construct(
        string $id,
        string $firstName,
        string $lastName,
        EmailAddress $email,
        string $passwordHash,
        UserRole $role,
        string $companyId,
        ?string $description = null,
        ?StaticMedia $image = null,
    ) {
        parent::__construct(
            id: $id ?? CustomUUID::generate(),
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
            passwordHash: $passwordHash,
            image: $image,
            description: $description
        );
        $this->role = $role;
        $this->companyId = $companyId;
    }


    /***
     * The public methode for creating an user domain object.
     */
    public static function create(
        string $firstName,
        string $lastName,
        EmailAddress $email,
        string $passwordHash,
        UserRole $role,
        string $companyId,
        ?string  $userId = null,
        ?StaticMedia $image= null,
        ?string $description = null,
    ){
        return new self(
            id: $userId ?? CustomUUID::generate(),
            email: $email,
            passwordHash: $passwordHash,
            lastName: $lastName,
            firstName: $firstName,
            role: $role,
            image: $image,
            description: $description,
            companyId: $companyId
        );
    }
    

    //----------------------------
    //   Business access
    //--------------------------
    
    public function role(): UserRole{
        return $this->role;
    }

    public function companyId(){
        return $this->companyId;
    }

    //------------------------------------------
    // - Business change --
    //-----------------------------------------

    public function changeRole(UserRole $role): self{
        $this->role = $role;
        return $this;
    }

    public function setCompanyId(string $companyId): self{
        $this->companyId = $companyId;
        return $this;
    }
}
