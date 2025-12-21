<?php

namespace App\Service\Import\Adapter;

use App\Service\Import\ImportContext;

interface SourceAdapterInterface
{
    public function supports(string $source, string $dataset): bool;

    /**
     * @return iterable<array>
     */
    public function iterate(string $entityType, string $path, ImportContext $ctx): iterable;
}
