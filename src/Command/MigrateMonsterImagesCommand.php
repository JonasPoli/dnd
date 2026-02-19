<?php

namespace App\Command;

use App\Entity\Monster;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:migrate-monster-images',
    description: 'Migrates monster images from public/uploads to public/media',
)]
class MigrateMonsterImagesCommand extends Command
{
    private $entityManager;
    private $params;
    private $filesystem;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $params)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->params = $params;
        $this->filesystem = new Filesystem();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute without making changes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');

        if ($dryRun) {
            $io->note('Running in DRY-RUN mode. No changes will be made.');
        }

        $projectDir = $this->params->get('kernel.project_dir');
        $publicDir = $projectDir . '/public';
        $mediaDir = $publicDir . '/media/monsters';

        if (!$this->filesystem->exists($mediaDir)) {
            $io->note("Creating directory: $mediaDir");
            if (!$dryRun) {
                $this->filesystem->mkdir($mediaDir);
            }
        }

        $query = $this->entityManager->getRepository(Monster::class)
            ->createQueryBuilder('m')
            ->where('m.imgMain LIKE :pattern')
            ->setParameter('pattern', '%uploads/monsters%')
            ->getQuery();

        $count = 0;
        $errors = 0;
        $skipped = 0;
        $batchSize = 20;
        $i = 0;

        foreach ($query->toIterable() as $monster) {
            $currentPath = $monster->getImgMain();

            if (!$currentPath) {
                continue;
            }

            // Check if it's in the old location (redundant check but safe)
            if (str_contains($currentPath, 'uploads/monsters')) {
                $sourceFile = $publicDir . '/' . ltrim($currentPath, '/');
                $filename = basename($currentPath);
                $destFile = $mediaDir . '/' . $filename;
                $newDbPath = 'media/monsters/' . $filename;

                if (!$this->filesystem->exists($sourceFile)) {
                    // Check if it already exists in destination
                     if ($this->filesystem->exists($destFile)) {
                         $io->warning("Source missing ($sourceFile) but found in destination. Updating DB only.");
                         if (!$dryRun) {
                             $monster->setImgMain($newDbPath);
                             $count++;
                         }
                    } else {
                        $io->warning("Source file not found for monster ID {$monster->getId()} ({$monster->getName()}): $sourceFile");
                        $errors++;
                    }
                } else {
                    // File exists in source
                    $io->text("Migrating: {$monster->getName()} (ID: {$monster->getId()})");
                    $io->text("  Source: $currentPath");
                    $io->text("  Dest:   $newDbPath");

                    if (!$dryRun) {
                        try {
                            // Move file
                            $this->filesystem->rename($sourceFile, $destFile, true); // true = overwrite
                            
                            // Update DB
                            $monster->setImgMain($newDbPath);
                            $count++;
                        } catch (\Exception $e) {
                            $io->error("Failed to move file: " . $e->getMessage());
                            $errors++;
                        }
                    } else {
                        $count++;
                    }
                }
            } else {
                $skipped++;
            }

            if (!$dryRun) {
                if (($i % $batchSize) === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear(); // Detach all objects from Doctrine!
                }
            }
            $i++;
        }

        if (!$dryRun) {
            $this->entityManager->flush();
            $this->entityManager->clear();
            $io->success("Migration complete. Processed $count images. Errors: $errors. Skipped: $skipped.");
        } else {
            $io->success("Dry run complete. Would process $count images. Errors: $errors. Skipped: $skipped.");
        }

        return Command::SUCCESS;
    }
}
