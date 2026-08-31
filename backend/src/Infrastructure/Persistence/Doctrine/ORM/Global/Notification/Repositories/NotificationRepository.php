<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Notification\Repositories;

use App\Domain\Notification\Notification;
use App\Domain\Notification\NotificationRepositoryInterface;
use App\Domain\Notification\NotificationType;
use App\Domain\Notification\RecipientType;

use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AccountEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Notification\NotificationEntity;

use Override;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;



/**
 * @extends ServiceEntityRepository<NotificationEntity>
 */
class NotificationRepository extends ServiceEntityRepository
    implements NotificationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationEntity::class);
    }


    #[Override]
    public function markNotificationAsRead(array $notificationIds, string $recipientId): void
    {
        if (empty($notificationIds)) {
            return;
        }

        $this->createQueryBuilder('n')
            ->update()
            ->set('n.readAt', ':now')
            ->set('n.isRead', ':isRead')
            ->where('n.id IN (:ids)')
            ->andWhere('n.readAt IS NULL')
            ->andWhere('n.recipientAccount = :recipientId OR n.recipientCompany = :recipientId')
            ->setParameters([
                'now'         => new \DateTimeImmutable(),
                'isRead'      => true,
                'ids'         => $notificationIds,
                'recipientId' => $recipientId,
            ])
            ->getQuery()
            ->execute();
    }


    /**
     * @param array<NotificationType> $types Tableau de filtres par type (vide = tous les types)
     * @return array<int, array{
     *     id: string,
     *     targetUrl: string|null,
     *     isRead: bool,
     *     data: array,
     *     account: array{
     *         id: string|null,
     *         firstName: string,
     *         lastName: string
     *     }|null,
     *     type: NotificationType,
     *     readAt: \DateTimeImmutable|null,
     *     createdAt: \DateTimeImmutable
     * }>
     */
    public function getNotifications(
        string $userId, 
        ?string $jobId = null, 
        int $limit = 7, 
        int $skip = 0,
        array $types = [] 
    ): array {
        $qb = $this->createQueryBuilder('n')
            ->select(
                'n.id',
                'n.targetUrl',
                'n.isRead',
                'n.data',
                'n.type',
                'n.readAt',
                'n.createdAt',
                'sender.id AS senderId',
                'sender.firstName AS senderFirstName',
                'sender.lastName AS senderLastName'
            )
            ->leftJoin('n.account', 'sender')
            ->where('n.recipientAccount = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($skip);

        // Conditionnal filter on  jobId
        if ($jobId) {
            $qb->andWhere('n.data LIKE :jobIdPattern')
               ->setParameter('jobIdPattern', '%"jobId":"' . $jobId . '"%');
        }

        // Filter types
        if (!empty($types)) {
            $qb->andWhere('n.type IN (:types)')
               ->setParameter(
                   'types', 
                   array_map(fn($t) => $t instanceof \BackedEnum ? $t->value : $t, $types)
               );
        }

        $results = $qb->getQuery()->getArrayResult();

        return array_map(function (array $row): array {
            $hasSender = $row['senderId'] !== null || $row['senderFirstName'] !== null;

            return [
                'id'        => $row['id'],
                'targetUrl' => $row['targetUrl'],
                'isRead'    => (bool) $row['isRead'],
                'data'      => $row['data'],
                'account'   => $hasSender ? [
                    'id'        => $row['senderId'],
                    'firstName' => $row['senderFirstName'] ?? '',
                    'lastName'  => $row['senderLastName'] ?? '',
                ] : null,
                'type'      => $row['type'] instanceof NotificationType 
                                ? $row['type'] 
                                : NotificationType::from($row['type']),
                'readAt'    => $row['readAt'],
                'createdAt' => $row['createdAt'] instanceof \DateTimeImmutable
                                    ?  $row['createdAt']->format(\DateTimeInterface::ATOM)
                                    :  $row['createdAt'] ,
            ];
        }, $results);
    }

    /**
     * Counts the number of unread notifications for a given target (Account or Company).
     *
     * @param string $recipientId Identifier of the target (Account UUID or Company UUID).
     * @param RecipientType $recipientType 'ACCOUNT' or 'COMPANY'
     *
     * @return int Number of unread notifications.
     */
    public function countUnreadForTarget(
        string $recipientId,
        RecipientType $recipientType = RecipientType::ACCOUNT
    ): int {
        $qb = $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.readAt IS NULL');
        
        if ($recipientType->value === RecipientType::COMPANY) {
            $qb->andWhere('n.recipientCompany = :recipientId');
        }
        else {
            $qb->andWhere('n.recipientAccount = :recipientId');
        }

        $qb->setParameter('recipientId', $recipientId);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }


    /**
     * Retrieves recent notifications for a given recipient (Account or Company).
     *
     * @param string $recipientId Identifier of the recipient (Account UUID or Company UUID).
     * @param RecipientType $recipientType 'ACCOUNT' or 'COMPANY'
     * @param NotificationType|null $type Notification type to retrieve.
     * @param int|null $limit Maximum number of notifications to return.
     * @param array $scheme Fields to fetch from persistence.
     *
     * @return array<int, array<string, mixed>>
     */
    #[Override]
    public function getRecentForTarget(
        string $recipientId,
        RecipientType $recipientType = RecipientType::ACCOUNT,
        ?NotificationType $type = null,
        ?int $limit = 7,
        array $scheme = ['id' => true],
    ): array {
        $qb = $this->createQueryBuilder('n');
        $isCompanyRecipient = strtoupper($recipientType->value) === RecipientType::COMPANY ;

        $selects = [];

        // Direct Notification Fields
        if ($scheme['id'] ?? false) {
            $selects[] = 'n.id';
        }
        if ($scheme['type'] ?? false) {
            $selects[] = 'n.type';
        }
        if ($scheme['targetUrl'] ?? false) {
            $selects[] = 'n.targetUrl';
        }
        if ($scheme['isRead'] ?? false) {
            $selects[] = 'n.isRead';
        }
        if ($scheme['readAt'] ?? false) {
            $selects[] = 'n.readAt';
        }
        if ($scheme['createdAt'] ?? false) {
            $selects[] = 'n.createdAt';
        }
        if ($scheme['data'] ?? false) {
            $selects[] = 'n.data';
        }

        //  Managing the Notification Sender (Account)
        if (!empty($scheme['account'])) {
            $qb->leftJoin('n.account', 'sender');

            if ($scheme['account']['id'] ?? false) {
                $selects[] = 'sender.id AS accountId';
            }
            if ($scheme['account']['firstName'] ?? false) {
                $selects[] = 'sender.firstName AS accountFirstName';
            }
            if ($scheme['account']['lastName'] ?? false) {
                $selects[] = 'sender.lastName AS accountLastName';
            }
        }

        // Handling the RECIPIENT based on the $recipientType (Company or Account)
        if (!empty($scheme['recipient'])) {
            if ($isCompanyRecipient) {
                $qb->leftJoin('n.recipientCompany', 'rc');

                if ($scheme['recipient']['id'] ?? false) {
                    $selects[] = 'rc.id AS recipientId';
                }
                if ($scheme['recipient']['name'] ?? false) {
                    $selects[] = 'rc.name AS recipientName';
                }
            } else {
                $qb->leftJoin('n.recipientAccount', 'ra');

                if ($scheme['recipient']['id'] ?? false) {
                    $selects[] = 'ra.id AS recipientId';
                }
                if ($scheme['recipient']['firstName'] ?? false) {
                    $selects[] = 'ra.firstName AS recipientFirstName';
                }
                if ($scheme['recipient']['lastName'] ?? false) {
                    $selects[] = 'ra.lastName AS recipientLastName';
                }
            }
        }

        // Safety check if the scheme is empty
        if (empty($selects)) {
            $selects[] = 'n.id';
        }

        // Filtering by the correct recipient
        $qb->select(implode(', ', $selects));

        if ($isCompanyRecipient) {
            $qb->where('n.recipientCompany = :recipientId');
        } else {
            $qb->where('n.recipientAccount = :recipientId');
        }

        $qb->setParameter('recipientId', $recipientId)
           ->orderBy('n.createdAt', 'DESC');

        // 5. Optional Filtering by Notification Type
        if ($type !== null) {
            $qb->andWhere('n.type = :type')
               ->setParameter('type', $type);
        }

        //  Application of the Limit
        if ($limit !== null && $limit > 0) {
            $qb->setMaxResults($limit);
        }

        $results = $qb->getQuery()->getArrayResult();

        // Post-processing: Restructuring of sub-objects (“account” & “recipient”) + Injection of the recipient type
        return array_map(function (array $row) use ($scheme, $isCompanyRecipient) {
            // Structure of the Issuer
            if (!empty($scheme['account'])) {
                $account = [];
                if (array_key_exists('accountId', $row)) {
                    $account['id'] = $row['accountId'];
                    unset($row['accountId']);
                }
                if (array_key_exists('accountFirstName', $row)) {
                    $account['firstName'] = $row['accountFirstName'];
                    unset($row['accountFirstName']);
                }
                if (array_key_exists('accountLastName', $row)) {
                    $account['lastName'] = $row['accountLastName'];
                    unset($row['accountLastName']);
                }

                if (!empty($account)) {
                    $row['account'] = $account;
                }
            }

            // Recipient Structure + ‘recipientType’ Injection
            if (!empty($scheme['recipient'])) {
                $recipient = [
                    'type' => $isCompanyRecipient ? 'COMPANY' : 'ACCOUNT',
                ];

                if (array_key_exists('recipientId', $row)) {
                    $recipient['id'] = $row['recipientId'];
                    unset($row['recipientId']);
                }

                if ($isCompanyRecipient) {
                    if (array_key_exists('recipientName', $row)) {
                        $recipient['name'] = $row['recipientName'];
                        unset($row['recipientName']);
                    }
                } else {
                    if (array_key_exists('recipientFirstName', $row)) {
                        $recipient['firstName'] = $row['recipientFirstName'];
                        unset($row['recipientFirstName']);
                    }
                    if (array_key_exists('recipientLastName', $row)) {
                        $recipient['lastName'] = $row['recipientLastName'];
                        unset($row['recipientLastName']);
                    }
                }

                $row['recipient'] = $recipient;
            }

            return $row;
        }, $results);
    }



    #[Override]
    public function save(Notification $notification): string
    {
        $em = $this->getEntityManager();
        $owner = $em->getReference(AccountEntity::class, $notification->accountId());

        $recipientAccount = null;
        if ($notification->recipientId() !== null) {
            $recipientAccount = $em->getReference(
                AccountEntity::class,
                $notification->recipientId()
            );
        }

        $recipientCompany = null;

        if ($notification->recipientCompanyId() !== null) {
            $recipientCompany = $em->getReference(
                CompanyEntity::class,
                $notification->recipientCompanyId()
            );
        }

        $entity = NotificationEntityMapper::toEntity(
            owner: $owner,
            domain: $notification,
            recipientAccount: $recipientAccount,
            recipientCompany: $recipientCompany
        );

        $em->persist($entity);
        $em->flush();

        return $entity->getId();
    }


    
    #[Override]
    /**
     * @param array<int, Notification> $notifications
     */
    public function saveAll(array $notifications): void
    {
        if (empty($notifications)) {
            return;
        }

        $em = $this->getEntityManager();

        foreach ($notifications as $notification) {
            $owner = $em->getReference(AccountEntity::class, $notification->accountId());

            $recipientAccount = null;
            if ($notification->recipientId() !== null) {
                $recipientAccount = $em->getReference(
                    AccountEntity::class,
                    $notification->recipientId()
                );
            }

            $recipientCompany = null;
            if ($notification->recipientCompanyId() !== null) {
                $recipientCompany = $em->getReference(
                    CompanyEntity::class,
                    $notification->recipientCompanyId()
                );
            }

            $entity = NotificationEntityMapper::toEntity(
                owner: $owner,
                domain: $notification,
                recipientAccount: $recipientAccount,
                recipientCompany: $recipientCompany
            );

            $em->persist($entity);
        }

        $em->flush();
    }
}