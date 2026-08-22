<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Notification\Repositories;

use App\Domain\Notification\Notification;

use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AccountEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Notification\NotificationEntity;


class NotificationEntityMapper
{

    public static function toEntity(
        Notification $domain,
        AccountEntity $owner,
        ?AccountEntity $recipientAccount,
        ?CompanyEntity $recipientCompany
    ):NotificationEntity {
        $entity = NotificationEntity::create(
            account: $owner,
            data: $domain->data(),
            type: $domain->type(),
            targetUrl: $domain->targetUrl(),
            recipientAccount: $recipientAccount,
            recipientCompany: $recipientCompany
        );

        return $entity;
    }

}