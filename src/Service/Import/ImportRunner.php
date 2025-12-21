<?php

namespace App\Service\Import;

use App\Entity\ImportRun;
use App\Entity\ImportRunSeen;
use App\Service\Import\Adapter\SourceAdapterInterface;
use App\Service\Import\Importer\ImporterInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class ImportRunner
{
    /** @var iterable<SourceAdapterInterface> */
    private iterable $adapters;

    /** @var iterable<ImporterInterface> */
    private iterable $importers;

    public function __construct(
        #[TaggedIterator('app.import_adapter')]
        iterable $adapters,
        #[TaggedIterator('app.importer')]
        iterable $importers,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
        $this->adapters = $adapters;
        $this->importers = $importers;
    }

    public function run(string $source, string $dataset, string $path, array $entityTypes, ImportContext $ctx): void
    {
        $adapter = $this->resolveAdapter($source, $dataset);

        $importRun = $ctx->getImportRun();
        if ($importRun) {
            $importRun->setStartedAt(new \DateTimeImmutable());
            $this->entityManager->persist($importRun);
            $this->entityManager->flush();
        }

        foreach ($entityTypes as $entityType) {
            $importer = $this->resolveImporter($entityType);
            if (!$importer) {
                $this->logger->warning(sprintf('No importer found for entity type "%s"', $entityType));
                continue;
            }

            $count = 0;
            foreach ($adapter->iterate($entityType, $path, $ctx) as $rawRecord) {
                try {
                    $normalized = $importer->normalize($rawRecord, $ctx);
                    $ctx->addStats($entityType, 'seen');

                    $entityId = $importer->upsert($normalized, $ctx);

                    if ($entityId && $importRun) {
                        $this->markSeen($importRun, $entityType, $normalized->getExternalId());
                    }

                    $count++;
                    if ($count % $ctx->getChunkSize() === 0) {
                        $this->entityManager->flush();
                    }

                } catch (\Exception $e) {
                    $this->logger->error(sprintf('Error importing %s: %s', $entityType, $e->getMessage()));
                    $ctx->addStats($entityType, 'errors');
                }
            }

            $this->entityManager->flush();

            if ($ctx->getMode() === 'full') {
                $this->handleDeletions($ctx, $entityType);
            }
        }

        if ($importRun) {
            $importRun->setFinishedAt(new \DateTimeImmutable());
            $importRun->setStatus('success');
            $this->entityManager->flush();
        }
    }

    private function resolveAdapter(string $source, string $dataset): SourceAdapterInterface
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($source, $dataset)) {
                return $adapter;
            }
        }
        throw new \RuntimeException(sprintf('No adapter found for source "%s" and dataset "%s"', $source, $dataset));
    }

    private function resolveImporter(string $entityType): ?ImporterInterface
    {
        foreach ($this->importers as $importer) {
            if ($importer->getEntityType() === $entityType) {
                return $importer;
            }
        }
        return null;
    }

    private function markSeen(ImportRun $run, string $type, string $extId): void
    {
        $seen = new ImportRunSeen();
        $seen->setImportRun($run);
        $seen->setEntityType($type);
        $seen->setExternalId($extId);
        $this->entityManager->persist($seen);
    }

    private function handleDeletions(ImportContext $ctx, string $entityType): void
    {
        $this->logger->info(sprintf('Handling deletions for %s (full sync mode)', $entityType));

        $conn = $this->entityManager->getConnection();

        // Find all active external references for this source and type
        // that were NOT seen in the current import run.
        $sql = "
            SELECT er.id, er.local_entity_id 
            FROM external_reference er
            LEFT JOIN import_run_seen irs ON irs.external_id = er.external_id 
                AND irs.entity_type = er.entity_type 
                AND irs.import_run_id = :run_id
            WHERE er.rules_source_id = :source_id
                AND er.entity_type = :entity_type
                AND er.status = 'active'
                AND irs.id IS NULL
        ";

        $rows = $conn->fetchAllAssociative($sql, [
            'run_id' => $ctx->getImportRun()->getId(),
            'source_id' => $ctx->getRulesSource()->getId(), // This might need bin conversion if UUID
            'entity_type' => $entityType,
        ]);

        foreach ($rows as $row) {
            $conn->executeStatement(
                "UPDATE external_reference SET status = 'deleted' WHERE id = :id",
                ['id' => $row['id']]
            );

            // Dynamically determine table name from entity type if possible, 
            // or use specific logic for supported entities.
            $tableName = $this->getTableNameForEntityType($entityType);
            if ($tableName) {
                $conn->executeStatement(
                    "UPDATE $tableName SET is_active = 0 WHERE id = :id",
                    ['id' => $row['local_entity_id']]
                );
            }

            $ctx->addStats($entityType, 'deleted');
        }
    }

    private function getTableNameForEntityType(string $type): ?string
    {
        return match ($type) {
            'spell', 'spells' => 'spell',
            'classes' => 'class_def',
            'species' => 'species',
            'backgrounds' => 'background',
            'equipment' => 'equipment',
            default => null
        };
    }
}
