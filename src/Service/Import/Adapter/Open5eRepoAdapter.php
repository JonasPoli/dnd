<?php

namespace App\Service\Import\Adapter;

use App\Service\Import\ImportContext;

class Open5eRepoAdapter implements SourceAdapterInterface
{
    private const FILENAME_MAP = [
        'spell' => 'spells.json',
        'monster' => 'monsters.json',
        'feat' => 'feats.json',
        'condition' => 'conditions.json',
        'magicitem' => 'magicitems.json',
        'rulesection' => 'sections.json',

        'classes' => 'classes.json',
        'species' => 'races.json',
        'backgrounds' => 'backgrounds.json',
        'equipment' => 'weapons.json',
        'subclass' => 'classes.json',
        'subrace' => 'races.json',
    ];

    public function supports(string $source, string $dataset): bool
    {
        return $source === 'open5e' && $dataset === 'repo';
    }

    public function iterate(string $entityType, string $path, ImportContext $ctx): iterable
    {
        $filenameKey = $entityType;
        if ($entityType === 'subclass') {
            $filenameKey = 'classes';
        }
        $filename = self::FILENAME_MAP[$filenameKey] ?? $filenameKey . '.json';
        $fullPath = rtrim($path, '/') . '/' . $filename;

        if (!file_exists($fullPath)) {
            throw new \RuntimeException(sprintf('File not found: %s', $fullPath));
        }

        $content = file_get_contents($fullPath);
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        // Open5e dumps usually have a list or are keyed by entity
        $records = $data['results'] ?? $data;

        foreach ($records as $record) {
            if ($entityType === 'subclass') {
                $classSlug = $record['slug'];
                $className = $record['name']; // Needed for level inference
                $archetypes = $record['archetypes'] ?? [];
                foreach ($archetypes as $arch) {
                    $arch['class_slug'] = $classSlug;
                    $arch['class_name'] = $className;
                    yield $arch;
                }
            } elseif ($entityType === 'subrace') {
                $speciesSlug = $record['slug'];
                $subraces = $record['subraces'] ?? [];
                foreach ($subraces as $sub) {
                    $sub['species_slug'] = $speciesSlug;
                    yield $sub;
                }
            } else {
                yield $record;
            }
        }
    }
}
