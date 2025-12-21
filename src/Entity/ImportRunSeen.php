<?php

namespace App\Entity;

use App\Repository\ImportRunSeenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImportRunSeenRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_RUN_TYPE_EXTID', fields: ['importRun', 'entityType', 'externalId'])]
class ImportRunSeen
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ImportRun $importRun = null;

    #[ORM\Column(length: 100)]
    private ?string $entityType = null;

    #[ORM\Column(length: 255)]
    private ?string $externalId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImportRun(): ?ImportRun
    {
        return $this->importRun;
    }

    public function setImportRun(?ImportRun $importRun): static
    {
        $this->importRun = $importRun;

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
}
