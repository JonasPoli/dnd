<?php

namespace App\Service\Import;

use App\Entity\ImportRun;
use App\Entity\RulesSource;

class ImportContext
{
    private array $stats = [];

    public function __construct(
        private RulesSource $rulesSource,
        private string $mode = 'incremental',
        private bool $onlyChanged = true,
        private int $chunkSize = 200,
        private ?ImportRun $importRun = null,
        private array $options = []
    ) {
    }

    public function getRulesSource(): RulesSource
    {
        return $this->rulesSource;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function isOnlyChanged(): bool
    {
        return $this->onlyChanged;
    }

    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    public function getImportRun(): ?ImportRun
    {
        return $this->importRun;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function addStats(string $entityType, string $action, int $count = 1): void
    {
        if (!isset($this->stats[$entityType])) {
            $this->stats[$entityType] = [
                'seen' => 0,
                'inserted' => 0,
                'updated' => 0,
                'skipped' => 0,
                'deleted' => 0,
                'errors' => 0,
            ];
        }

        $this->stats[$entityType][$action] += $count;
    }

    public function getStats(): array
    {
        return $this->stats;
    }
}
