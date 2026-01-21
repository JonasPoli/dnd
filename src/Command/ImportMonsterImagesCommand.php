<?php

namespace App\Command;

use App\Entity\Monster;
use App\Repository\MonsterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:import:monster-images',
    description: 'Imports monster images from dnd5eapi.co',
)]
class ImportMonsterImagesCommand extends Command
{
    private const API_BASE = 'https://www.dnd5eapi.co';

    public function __construct(
        private HttpClientInterface $httpClient,
        private MonsterRepository $monsterRepository,
        private EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Limit the number of monsters to process', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = $input->getOption('limit');

        $io->title('Importing Monster Images from D&D 5e API');

        // 1. Fetch List
        $io->section('Fetching Monster List...');
        $response = $this->httpClient->request('GET', self::API_BASE . '/api/2014/monsters');
        $data = $response->toArray();
        $apiMonsters = $data['results'] ?? [];

        $io->note(sprintf('Found %d monsters in API.', count($apiMonsters)));

        $filesystem = new Filesystem();
        $uploadDir = $this->projectDir . '/public/uploads/monsters';
        if (!$filesystem->exists($uploadDir)) {
            $filesystem->mkdir($uploadDir);
        }

        $processed = 0;
        foreach ($apiMonsters as $apiMonster) {
            if ($limit && $processed >= $limit) {
                break;
            }

            $slug = $apiMonster['index'];
            // $url = $apiMonster['url']; // e.g. /api/2014/monsters/aboleth

            // 2. Find Local Monster by Slug
            $localMonster = $this->monsterRepository->findOneBy(['ruleSlug' => $slug]);

            if (!$localMonster) {
                // Not found locally, skip silent or verbose debug? 
                // Request implies we loop looking for matches. If not matched, maybe ignore.
                continue;
            }

            // 3. Fetch Detail to check for image
            // We could just check if we have an image locally first to save API calls, 
            // but the prompt says: "verifique se o monstro possui imagem, se não tiver, baixe"
            // AND "mostrar em branco, os que não foram precisos porque já temos a imagem"

            // So we need to know if the API has an image to color code correctly.

            try {
                $detailResponse = $this->httpClient->request('GET', self::API_BASE . $apiMonster['url']);
                $detailData = $detailResponse->toArray();
            } catch (\Exception $e) {
                $io->error("Failed to fetch details for $slug: " . $e->getMessage());
                continue;
            }

            $apiImageUrl = $detailData['image'] ?? null;

            if (!$apiImageUrl) {
                // YELLOW: Found in DB, but API has no image
                $io->writeln(sprintf('<comment>%s: No image in API (Yellow)</comment>', $localMonster->getName()));
            } else {
                // API has image
                if ($localMonster->getImgMain()) {
                    // WHITE: Already have image locally
                    $io->writeln(sprintf('<info>%s: Image already exists locally (White)</info>', $localMonster->getName()));
                    // Note: <info> is green in Symfony default, but standard text is white. 
                    // User said "em branco". Standard output is white.
                    // Let's use strict styling if needed.
                    // I will use `write` without tags for white/default, or define styles.
                    // Actually, symfony console default text is white.
                    // But <info> is green. <comment> is yellow. <error> is red.
                    // Let's try to stick to basic tags.
                    // "White" -> standard output.

                    // Wait, re-reading: "em branco, os que não foram precisos porque já temos a imagem"
                    // "mostrar em branco" -> Standard text.
                    $io->writeln(sprintf(' %s: Image already exists locally', $localMonster->getName()));
                } else {
                    // GREEN: Don't have image, downloading
                    $io->write(sprintf('<info>%s: Downloading image... </info>', $localMonster->getName()));

                    try {
                        $fullImageUrl = self::API_BASE . $apiImageUrl;
                        $imageContent = file_get_contents($fullImageUrl);

                        if ($imageContent) {
                            $filename = $slug . '.png'; // assuming png as per example, or extract extension
                            $extension = pathinfo($apiImageUrl, PATHINFO_EXTENSION);
                            if ($extension) {
                                $filename = $slug . '.' . $extension;
                            }

                            $targetPath = $uploadDir . '/' . $filename;
                            file_put_contents($targetPath, $imageContent);

                            $localMonster->setImgMain('uploads/monsters/' . $filename);
                            $this->entityManager->flush();

                            $io->writeln('<info>Done (Green)</info>');
                        } else {
                            $io->writeln('<error>Failed to download content</error>');
                        }
                    } catch (\Exception $e) {
                        $io->writeln('<error>Error downloading: ' . $e->getMessage() . '</error>');
                    }
                }
            }

            $processed++;
        }

        $io->success('Import complete.');

        return Command::SUCCESS;
    }
}
