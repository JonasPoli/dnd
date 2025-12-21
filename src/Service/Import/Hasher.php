<?php

namespace App\Service\Import;

use App\Service\Import\NormalizedRecord;

class Hasher
{
    public function hashNormalized(NormalizedRecord $record): string
    {
        $payload = $record->getPayload();
        ksort($payload);

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return hash('sha256', $json);
    }
}
