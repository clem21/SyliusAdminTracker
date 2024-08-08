<?php

declare(strict_types=1);

namespace ACSEO\SyliusAdminTrackerPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_user_action')]
class UserAction implements ResourceInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: AdminUserInterface::class)]
    #[ORM\JoinColumn(nullable: false)]
    private AdminUserInterface $user;

    #[ORM\Column(type: 'string')]
    private string $action;

    #[ORM\Column(type: 'array')]
    private array $oldValue = [];

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUser(): AdminUserInterface
    {
        return $this->user;
    }

    public function setUser(AdminUserInterface $user): void
    {
        $this->user = $user;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function getOldValue(): array
    {
        return $this->oldValue;
    }

    public function setOldValue(array $oldValue): void
    {
        $this->oldValue = $oldValue;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
