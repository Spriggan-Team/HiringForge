<?php

namespace App\Application\Command\Utils;

use Symfony\Component\Security\Core\User\UserInterface;

final class AuthenticatedPerson implements UserInterface
{
    public function __construct(
        /** The id of a person */
        private string $id,

        /** @var string $sub represent the subscriber, currently it is the email */
        private string $sub,
        /**
         * @var array  string AccountRole::value
        * This array contains the roles of a defined user
        */
        private array $roles
    ) {}

    public function getId()
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return $this->sub;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void {}
}
