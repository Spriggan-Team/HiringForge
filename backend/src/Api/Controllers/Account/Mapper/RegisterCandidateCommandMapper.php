<?php

namespace  App\Api\Controllers\Account\Mapper;

use App\Domain\Shared\Address;
use App\Application\DTO\Candidate\RegisterCandidateCommand;

use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\InputBag;


class RegisterCandidateCommandMapper
{
    public static function map(
        InputBag $form,
        FileBag $files
    ): RegisterCandidateCommand {
        return new RegisterCandidateCommand(
            lastName: $form->get('lastName'),
            firstName: $form->get('firstName'),
            email: $form->get('email'),
            password: $form->get('password'),

            image: $files->get('profileImage', null),
            cv: $files->get('cv', null),

            address: Address::tryCreate([
                'city' => $form->get('city'),
                'street' => $form->get('street'),
                'country' => $form->get('country'),
                'postalCode' => $form->get('postalCode'),
            ]),

            searchRadius: $form->get('searchRadius'),
            verificationCode: $form->get('verificationCode')
        );
    }
}