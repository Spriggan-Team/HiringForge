<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Notification\Repositories;

use App\Domain\Notification\NotificationRepositoryInterface;
use App\Domain\Notification\NotificationType;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Notification\NotificationEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Override;

<?php

namespace App\Repository;

use App\Entity\NotificationEntity;
use App\Enum\NotificationType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotificationEntity>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationEntity::class);
    }

    /**
     * Retrieves recent notifications for a given recipient (Account or Company).
     *
     * @param string $recipientId Identifier of the recipient (Account UUID or Company UUID).
     * @param string $recipientType 'ACCOUNT' or 'COMPANY'
     * @param NotificationType|null $type Notification type to retrieve.
     * @param int|null $limit Maximum number of notifications to return.
     * @param array{
     *     id?: bool,
     *     type?: bool,
     *     targetUrl?: bool,
     *     data?: bool,
     *     account?: array{
     *         id?: bool,
     *         firstName?: bool,
     *         lastName?: bool
     *     },
     *     recipient?: array{
     *         id?: bool,
     *         name?: bool,       // Enriched for Company
     *         firstName?: bool,  // Enriched for Account
     *         lastName?: bool    // Enriched for Account
     *     },
     *     readAt?: bool,
     *     isRead?: bool,
     *     createdAt?: bool
     * } $scheme Fields to fetch from persistence.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRecentForTarget(
        string $recipientId,
        string $recipientType = 'ACCOUNT',
        ?NotificationType $type = null,
        ?int $limit = 7,
        array $scheme = ['id' => true],
    ): array {
        $qb = $this->createQueryBuilder('n');
        $isCompanyRecipient = strtoupper($recipientType) === 'COMPANY';

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
}