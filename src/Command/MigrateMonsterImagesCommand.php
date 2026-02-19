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

        // Part 1: Move files from uploads/ to media/monsters/
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

            if (str_contains($currentPath, 'uploads/monsters')) {
                $sourceFile = $publicDir . '/' . ltrim($currentPath, '/');
                $filename = basename($currentPath);
                $destFile = $mediaDir . '/' . $filename;
                // DATABASE FIX: Store ONLY the filename, VichUploader adds the prefix
                $newDbPath = $filename; 

                if (!$this->filesystem->exists($sourceFile)) {
                     if ($this->filesystem->exists($destFile)) {
                         $io->warning("Source missing ($sourceFile) but found in destination. Updating DB to filename.");
                         if (!$dryRun) {
                             $monster->setImgMain($newDbPath);
                             $count++;
                         }
                    } else {
                        $io->warning("Source file not found for monster ID {$monster->getId()} ({$monster->getName()}): $sourceFile");
                        $errors++;
                    }
                } else {
                    $io->text("Migrating: {$monster->getName()} (ID: {$monster->getId()})");
                    $io->text("  Source: $currentPath");
                    $io->text("  Dest:   $mediaDir/$newDbPath");
                    $io->text("  DB:     $newDbPath");

                    if (!$dryRun) {
                        try {
                            $this->filesystem->rename($sourceFile, $destFile, true);
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
                    $this->entityManager->clear(); 
                }
            }
            $i++;
        }
        
        if (!$dryRun) {
            $this->entityManager->flush();
            $this->entityManager->clear();
        }

        // Part 2: Fix existing paths that incorrectly contain 'media/monsters/'
        $io->section("Checking for double paths (media/monsters/ prefix)...");
        
        $queryFix = $this->entityManager->getRepository(Monster::class)
            ->createQueryBuilder('m')
            ->where('m.imgMain LIKE :pattern')
            ->setParameter('pattern', 'media/monsters/%')
            ->getQuery();

        $fixedCount = 0;
        $j = 0;
        
        foreach ($queryFix->toIterable() as $monster) {
             $currentPath = $monster->getImgMain();
             // Strip the prefix
             $newPath = str_replace('media/monsters/', '', $currentPath);
             
             // Ensure no lingering leading slashes if any
             $newPath = ltrim($newPath, '/');
             
             if ($currentPath !== $newPath) {
                 $io->text("Fixing path for {$monster->getName()}: '$currentPath' -> '$newPath'");
                 
                 if (!$dryRun) {
                     $monster->setImgMain($newPath);
                     $fixedCount++;
                     
                     if (($j % $batchSize) === 0) {
                        $this->entityManager->flush();
                        $this->entityManager->clear();
                    }
                 } else {
                     $fixedCount++;
                 }
             }
             $j++;
        }

        if (!$dryRun) {
            $this->entityManager->flush();
            $this->entityManager->clear();
            $io->success("Migration complete.\nMoved/Updated: $count\nPath Fixes: $fixedCount\nErrors: $errors\nSkipped: $skipped");
        } else {
            $io->success("Dry run complete.\nWould Move/Update: $count\nWould Fix Paths: $fixedCount\nErrors: $errors\nSkipped: $skipped");
        }

        return Command::SUCCESS;
    }
}
