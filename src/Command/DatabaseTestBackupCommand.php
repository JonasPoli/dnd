<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:database:test-backup',
    description: 'Tests the backup and restore process.',
)]
class DatabaseTestBackupCommand extends Command
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Testing Database Backup and Restore');

        $backupDir = $this->projectDir . '/sql/backups';

        // 1. Create a Backup
        $io->section('Step 1: Running Backup');
        $backupCommand = $this->getApplication()->find('app:database:backup');
        $backupInput = new ArrayInput([]);
        $returnCode = $backupCommand->run($backupInput, $output);

        if ($returnCode !== Command::SUCCESS) {
            $io->error('Backup step failed.');
            return Command::FAILURE;
        }

        // Determine created file (simple way: check latest file in dir)
        // Since the command output might be noisy, we need to find the latest file.
        $files = glob($backupDir . '/*.sql');
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        $latestBackup = $files[0] ?? null;

        if (!$latestBackup) {
            $io->error('No backup file found after running backup command.');
            return Command::FAILURE;
        }
        
        $filename = basename($latestBackup);
        $io->success("Backup created: $filename");

        // 2. Restore the Backup
        $io->section('Step 2: Restoring Backup');
        $restoreCommand = $this->getApplication()->find('app:database:restore');
        $restoreInput = new ArrayInput(['file' => $filename]);
        
        // We need to confirm input if restore asks for confirmation (it doesn't currently, but normally restore is dangerous)
        // My restore command automatically does safety backup, so it is somewhat safe, but still.
        // It runs non-interactively.

        $returnCode = $restoreCommand->run($restoreInput, $output);

        if ($returnCode !== Command::SUCCESS) {
            $io->error('Restore step failed.');
            return Command::FAILURE;
        }

        $io->success('Restore completed successfully.');

        $io->success('Backup and Restore cycle tested successfully.');

        return Command::SUCCESS;
    }
}
