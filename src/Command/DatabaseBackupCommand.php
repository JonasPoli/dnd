<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:database:backup',
    description: 'Backs up the SQLite database to a SQL file.',
)]
class DatabaseBackupCommand extends Command
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        #[Autowire('%env(resolve:DATABASE_URL)%')]
        private string $databaseUrl
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting database backup...');

        $backupDir = $this->projectDir . '/sql/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'backup_' . date('Y-m-d') . '.sql';
        $backupFile = $backupDir . '/' . $filename;

        if (str_starts_with($this->databaseUrl, 'sqlite://')) {
            return $this->backupSqlite($output, $backupFile);
        } elseif (str_starts_with($this->databaseUrl, 'mysql://')) {
            return $this->backupMysql($output, $backupFile);
        }

        $output->writeln('<error>Unsupported database URL scheme.</error>');
        return Command::FAILURE;
    }

    private function backupSqlite(OutputInterface $output, string $backupFile): int
    {
        $dbPath = $this->getSqlitePath();
        if (!file_exists($dbPath)) {
             $output->writeln("<error>Database file not found at $dbPath</error>");
             return Command::FAILURE;
        }

        $process = Process::fromShellCommandline("sqlite3 \"$dbPath\" .dump > \"$backupFile\"");
        $process->setTimeout(300);
        $process->run();

        return $this->checkProcess($process, $output, $backupFile);
    }

    private function backupMysql(OutputInterface $output, string $backupFile): int
    {
        $params = parse_url($this->databaseUrl);
        
        $user = $params['user'] ?? 'root';
        $pass = $params['pass'] ?? '';
        $host = $params['host'] ?? '127.0.0.1';
        $port = $params['port'] ?? 3306;
        $dbName = ltrim($params['path'], '/');

        // Construct command
        // Note: putting password in command line is visible in process list.
        // For better security, could use .my.cnf or MYSQL_PWD env var.
        $cmd = sprintf(
            'mysqldump -u%s -p%s -h%s -P%s %s > "%s"',
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($dbName),
            $backupFile
        );

        $process = Process::fromShellCommandline($cmd);
        $process->setTimeout(300);
        $process->run();

        return $this->checkProcess($process, $output, $backupFile);
    }

    private function checkProcess(Process $process, OutputInterface $output, string $backupFile): int
    {
        if (!$process->isSuccessful()) {
            $output->writeln('<error>Backup failed: ' . $process->getErrorOutput() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln("<info>Backup successfully created at: $backupFile</info>");
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
