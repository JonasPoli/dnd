<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Console\Input\ArrayInput;

#[AsCommand(
    name: 'app:database:restore',
    description: 'Restores the database from a backup SQL file.',
)]
class DatabaseRestoreCommand extends Command
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        #[Autowire('%env(resolve:DATABASE_URL)%')]
        private string $databaseUrl
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'The SQL backup file to restore (relative to /sql/backups or absolute path)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');

        // Resolve file path
        if (!file_exists($file)) {
            $backupDir = $this->projectDir . '/sql/backups';
            $potentialFile = $backupDir . '/' . $file;
            if (file_exists($potentialFile)) {
                $file = $potentialFile;
            } else {
                $io->error("Backup file not found: $file");
                return Command::FAILURE;
            }
        }

        $io->note("Restoring from: $file");

        // 1. Auto-backup current DB
        $io->section('Step 1: Creating safety backup of current database...');
        $backupCommand = $this->getApplication()->find('app:database:backup');
        $backupInput = new ArrayInput([]);
        $returnCode = $backupCommand->run($backupInput, $output);

        if ($returnCode !== Command::SUCCESS) {
            $io->error('Safety backup failed. Aborting restore to prevent data loss.');
            return Command::FAILURE;
        }

        // 2. Clear DB and Keep going
        $io->section('Step 2: clearing and restoring database...');
        
        if (str_starts_with($this->databaseUrl, 'sqlite://')) {
            return $this->restoreSqlite($io, $file);
        } elseif (str_starts_with($this->databaseUrl, 'mysql://')) {
            return $this->restoreMysql($io, $file);
        }

        $io->error('Unsupported database URL scheme.');
        return Command::FAILURE;
    }

    private function restoreSqlite(SymfonyStyle $io, string $file): int
    {
        $dbPath = $this->getSqlitePath();
        
        // SQLite restore logic:
        // 1. Close connections? (For CLI ok)
        // 2. Clear DB? For sqlite, removing the file or emptying it is best.
        // But the .dump format usually contains CREATE TABLE, so existing tables might conflict if not dropped.
        // .dump usually adds DROP TABLE IF EXISTS? No, sqlite3 .dump usually doesn't unless specified?
        // Wait, standard sqlite3 .dump output matches schema.
        
        // Safer approach for SQLite:
        // Delete the db file?
        // sqlite3 .dump output creates tables.
        // If we restore into existing DB, we might get errors if tables exist.
        
        // Let's try to remove the file, but keep it if restore fails?
        // We already have a backup.
        
        // To be safe: Restore to a temp file, then swap?
        // Or just run sqlite3 db < file.
        
        // If I delete the file and invalid SQL is provided, we lose the DB (but we have backup).
        // I will trust the backup we just made.
        
        if (file_exists($dbPath)) {
            unlink($dbPath);
            // Recreating empty file is handled by sqlite3 command as it creates if not exists
        }

        $process = Process::fromShellCommandline("sqlite3 \"$dbPath\" < \"$file\"");
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            $io->error('Restore failed: ' . $process->getErrorOutput());
            return Command::FAILURE;
        }
        
        $io->success('Database restored successfully (SQLite).');
        return Command::SUCCESS;
    }

    private function restoreMysql(SymfonyStyle $io, string $file): int
    {
        $params = parse_url($this->databaseUrl);
        $user = $params['user'] ?? 'root';
        $pass = $params['pass'] ?? '';
        $host = $params['host'] ?? '127.0.0.1';
        $port = $params['port'] ?? 3306;
        $dbName = ltrim($params['path'], '/');

        // Clear database?
        // mysqldump usually adds DROP TABLE IF EXISTS.
        // BUT if there are tables in current DB that are NOT in backup, they will REMAIN.
        // So we MUST drop all tables or drop database.
        
        // Attempt to re-create database to ensure it's clean (and remove extra tables)
        // "DROP DATABASE IF EXISTS name; CREATE DATABASE name;"
        // Note: user needs privileges.
        
        $sql = sprintf('DROP DATABASE IF EXISTS `%s`; CREATE DATABASE `%s`;', $dbName, $dbName);

        $recreateCmd = sprintf(
            'mysql -u%s -p%s -h%s -P%s -e %s',
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($sql)
        );

        $process = Process::fromShellCommandline($recreateCmd);
        $process->run();

        if (!$process->isSuccessful()) {
             $io->warning('Could not drop/recreate database (permissions?). Proceeding with import (extra tables might remain). Error: ' . $process->getErrorOutput());
             // We continue.
        }

        // Import
        $cmd = sprintf(
            'mysql -u%s -p%s -h%s -P%s %s < "%s"',
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($dbName),
            $file
        );

        $process = Process::fromShellCommandline($cmd);
        $process->setTimeout(600);
        $process->run();

        if (!$process->isSuccessful()) {
            $io->error('Restore failed: ' . $process->getErrorOutput());
            return Command::FAILURE;
        }

        $io->success('Database restored successfully (MySQL).');
        return Command::SUCCESS;
    }

    private function getSqlitePath(): string
    {
        if (str_starts_with($this->databaseUrl, 'sqlite:///')) {
             return substr($this->databaseUrl, 10);
        }
        return substr($this->databaseUrl, 9);
    }
}
