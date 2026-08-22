<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Notification;

use App\Domain\Notification\NotificationDataInterface;
use App\Domain\Notification\NotificationType;
use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AccountEntity;

use DateTimeImmutable;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\JoinColumn;



#[ORM\Entity]
#[ORM\Table(name: 'notifications')]
class NotificationEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    #[GeneratedValue('CUSTOM')]
    #[ORM\CustomIdGenerator('doctrine.uuid_generator')]
    private ?string $id = null;

    #[ORM\Column(enumType: NotificationType::class, length: 55)]
    private NotificationType $type;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true)]
    private ?string $targetUrl = null;

    #[ORM\Column(type: Types::JSON)]
    private array $data = [];

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isRead = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $readAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;


    //----------------------------
    //---- Relation
    //----------------------------

    #[ORM\ManyToOne(
        targetEntity: AccountEntity::class,
    )]
    #[JoinColumn(nullable: true)]
    private ?AccountEntity $account = null;

    #[ORM\ManyToOne(targetEntity: AccountEntity::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?AccountEntity $recipientAccount = null;

    // Destinataire de type Entreprise
    #[ORM\ManyToOne(targetEntity: CompanyEntity::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?CompanyEntity $recipientCompany = null;

    //----------------------------
    //-- Construct
    //------------------------------

    private function __construct()
    {}

    
    public static function create(
        NotificationType $type,
        NotificationDataInterface $data,
        ?string $id =null,
        ?AccountEntity $account = null,
        ?AccountEntity $recipientAccount = null,
        ?CompanyEntity $recipientCompany = null,
        ?string $targetUrl = null,
    ): self {
        $notification = new self();
        $notification->id = $id;
        $notification->type = $type;
        $notification->data = $data->toArray();
        $notification->account = $account;
        $notification->recipientAccount = $recipientAccount;
        $notification->recipientCompany = $recipientCompany;
        $notification->targetUrl = $targetUrl;
        $notification->createdAt = new \DateTimeImmutable();

        return $notification;
    }


    public static function reconstitute(
        string $id,
        NotificationType $type,
        ?AccountEntity $account,
        ?AccountEntity $recipientAccount,
        ?CompanyEntity $recipientCompany,
        ?string $targetUrl,
        array $data,
        bool $isRead,
        ?DateTimeImmutable $readAt,
        DateTimeImmutable $createdAt,
    ): self {
        $notification = new self();

        $notification->id = $id;
        $notification->account = $account;
        $notification->type = $type;
        $notification->targetUrl = $targetUrl;
        $notification->data = $data;
        $notification->recipientAccount = $recipientAccount;
        $notification->recipientCompany = $recipientCompany;
        $notification->isRead = $isRead;
        $notification->readAt = $readAt;
        $notification->createdAt = $createdAt;

        return $notification;
    }

    public function markAsRead(): void
    {
        if ($this->isRead) {
            return;
        }

        $this->isRead = true;
        $this->readAt = new DateTimeImmutable();
    }

    // Getters...

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getAccount(): AccountEntity
    {
        return $this->account;
    }

    public function setAccount(AccountEntity $account): self
    {
        $this->account = $account;
        return $this;
    }

    public function getRecipientAccount()
    {
        return $this->recipientAccount;    
    }

    public function setRecipientAccount(AccountEntity $recipientAccount)
    {
        $this->recipientAccount = $recipientAccount;
        return $this;
    }

    public function getRecipientCompagny(){
        return $this->recipientCompany;
    }

    public function setRecipientCompagny(CompanyEntity $recipientCompany){
        $this->recipientCompany = $recipientCompany;
        return $this;
    }

    public function getType(): NotificationType
    {
        return $this->type;
    }

    public function setType(NotificationType $type): self
    {
        $this->type = $type;
        return $this;
    }



    public function getTargetUrl(): ?string
    {
        return $this->targetUrl;
    }

    public function setTargetUrl(?string $targetUrl): self
    {
        $this->targetUrl = $targetUrl;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function getIsRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): self
    {
        $this->isRead = $isRead;
        return $this;
    }

    public function getReadAt(): ?DateTimeImmutable
    {
        return $this->readAt;
    }

    public function setReadAt(?DateTimeImmutable $readAt): self
    {
        $this->readAt = $readAt;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}