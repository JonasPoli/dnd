<?php

namespace App\Service\Import;

class NormalizedRecord
{
    public function __construct(
        private string $entityType,
        private string $externalId,
        private array $payload
    ) {
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
