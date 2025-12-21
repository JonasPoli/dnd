<?php

namespace App\Entity;

use App\Repository\ExternalReferenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExternalReferenceRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_SOURCE_TYPE_EXTID', fields: ['rulesSource', 'entityType', 'externalId'])]
class ExternalReference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?RulesSource $rulesSource = null;

    #[ORM\Column(length: 100)]
    private ?string $entityType = null;

    #[ORM\Column(length: 255)]
    private ?string $externalId = null;

    #[ORM\Column]
    private ?int $localEntityId = null;

    #[ORM\Column(length: 64)]
    private ?string $contentHash = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $lastImportedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $firstSeenAt = null;

    #[ORM\Column(length: 20)]
    private ?string $status = 'active';

    public function __construct()
    {
        $this->firstSeenAt = new \DateTimeImmutable();
        $this->lastImportedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRulesSource(): ?RulesSource
    {
        return $this->rulesSource;
    }

    public function setRulesSource(?RulesSource $rulesSource): static
    {
        $this->rulesSource = $rulesSource;

        return $this;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(string $entityType): static
    {
        $this->entityType = $entityType;

        return $this;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(string $externalId): static
    {
        $this->externalId = $externalId;

        return $this;
    }

    public function getLocalEntityId(): ?int
    {
        return $this->localEntityId;
    }

    public function setLocalEntityId(int $localEntityId): static
    {
        $this->localEntityId = $localEntityId;

        return $this;
    }

    public function getContentHash(): ?string
    {
        return $this->contentHash;
    }

    public function setContentHash(string $contentHash): static
    {
        $this->contentHash = $contentHash;

        return $this;
    }

    public function getLastImportedAt(): ?\DateTimeImmutable
    {
        return $this->lastImportedAt;
    }

    public function setLastImportedAt(\DateTimeImmutable $lastImportedAt): static
    {
        $this->lastImportedAt = $lastImportedAt;

        return $this;
    }

    public function getFirstSeenAt(): ?\DateTimeImmutable
    {
        return $this->firstSeenAt;
    }

    public function setFirstSeenAt(\DateTimeImmutable $firstSeenAt): static
    {
        $this->firstSeenAt = $firstSeenAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
