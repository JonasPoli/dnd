<?php

namespace App\Command;

use App\Entity\RulesSource;
use App\Entity\ImportRun;
use App\Repository\RulesSourceRepository;
use App\Service\Import\ImportContext;
use App\Service\Import\ImportRunner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:rules:import',
    description: 'Import D&D rules data from external sources',
)]
class RulesImportCommand extends Command
{
    public function __construct(
        private RulesSourceRepository $rulesSourceRepo,
        private ImportRunner $importRunner,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('source', null, InputOption::VALUE_REQUIRED, 'Source identifier (e.g., open5e)')
            ->addOption('dataset', null, InputOption::VALUE_REQUIRED, 'Dataset type (e.g., repo, api)')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Path to data files')
            ->addOption('entity', null, InputOption::VALUE_REQUIRED, 'Entity type to import (all, spells, etc.)', 'all')
            ->addOption('mode', null, InputOption::VALUE_REQUIRED, 'Import mode (incremental, full)', 'incremental')
            ->addOption('chunk', null, InputOption::VALUE_REQUIRED, 'Chunk size for database flushes', 200)
            ->addOption('only-changed', null, InputOption::VALUE_REQUIRED, 'Only update changed records', true)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $sourceSlug = $input->getOption('source');
        if (!$sourceSlug) {
            $io->error('--source is required');
            return Command::FAILURE;
        }

        $source = $this->rulesSourceRepo->findOneBy(['slug' => $sourceSlug]);
        if (!$source) {
            $io->note(sprintf('Rules source "%s" not found. Creating it...', $sourceSlug));
            $source = new RulesSource();
            $source->setSlug($sourceSlug);
            $source->setName(ucfirst($sourceSlug)); // Default name
            $this->entityManager->persist($source);
            $this->entityManager->flush();
        }

        $dataset = $input->getOption('dataset');
        $path = $input->getOption('path');
        $mode = $input->getOption('mode');
        $chunk = (int) $input->getOption('chunk');
        $entityOption = $input->getOption('entity');

        $entityTypes = $entityOption === 'all'
            ? ['spell', 'classes', 'species', 'backgrounds', 'equipment', 'monster', 'feat', 'magicitem', 'condition', 'rulesection']
            : explode(',', $entityOption);


        $importRun = new ImportRun();
        $importRun->setRulesSource($source);
        $importRun->setSource($sourceSlug);
        $importRun->setMode($mode);

        $importRun->setOptionsJson([
            'dataset' => $dataset,
            'path' => $path,
            'entity' => $entityOption,
            'only-changed' => $input->getOption('only-changed'),
        ]);

        $ctx = new ImportContext(
            $source,
            $mode,
            (bool) $input->getOption('only-changed'),
            $chunk,
            $importRun
        );

        $io->title(sprintf('Starting Import: %s (%s)', $source->getName(), $mode));

        try {
            $this->importRunner->run($sourceSlug, $dataset, $path, $entityTypes, $ctx);
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->section('Import Summary');
        foreach ($ctx->getStats() as $type => $stats) {
            $io->text(sprintf(
                '%s: %d processed, %d inserted, %d updated, %d skipped, %d errors',
                ucfirst($type),
                $stats['seen'],
                $stats['inserted'],
                $stats['updated'],
                $stats['skipped'],
                $stats['errors']
            ));
        }

        $io->success('Import completed successfully.');

        return Command::SUCCESS;
    }
}

