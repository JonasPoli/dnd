<?php

namespace App\Service\Import\Importer;

use App\Service\Import\ImportContext;
use App\Service\Import\NormalizedRecord;

interface ImporterInterface
{
    public function getEntityType(): string;

    public function normalize(array $raw, ImportContext $ctx): NormalizedRecord;

    /**
     * Returns the local entity ID or null on failure.
     */
    public function upsert(NormalizedRecord $record, ImportContext $ctx): ?int;
}
