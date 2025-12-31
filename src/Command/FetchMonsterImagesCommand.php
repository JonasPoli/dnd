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
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DomCrawler\Crawler;

#[AsCommand(
    name: 'app:fetch:monster-images',
    description: 'Crawls Forgotten Realms Wiki to fetch and download monster images.',
)]
class FetchMonsterImagesCommand extends Command
{
    public function __construct(
        private MonsterRepository $monsterRepository,
        private EntityManagerInterface $entityManager,
        private HttpClientInterface $httpClient,
        #[Autowire('%kernel.project_dir%/public/uploads/monsters')]
        private string $targetDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Number of items to process', 1)
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force update existing images')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = (int) $input->getOption('limit');
        $force = $input->getOption('force');

        // Ensure target directory exists
        $filesystem = new Filesystem();
        if (!$filesystem->exists($this->targetDir)) {
            $filesystem->mkdir($this->targetDir);
        }

        // Find monsters
        $qb = $this->monsterRepository->createQueryBuilder('m');
        if (!$force) {
            $qb->where('m.imgMain IS NULL OR m.imgMain = :empty')
               ->setParameter('empty', '');
        }
        $items = $qb->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        if (empty($items)) {
            $io->success('No monsters found needing images.');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Processing %d monsters...', count($items)));

        foreach ($items as $monster) {
            /** @var Monster $monster */
            $io->section("Processing: " . $monster->getName());

            try {
                $imageUrl = $this->findImageForMonster($monster, $io);

                if ($imageUrl) {
                    $io->text("Found image URL: $imageUrl");
                    
                    // Download and save
                    $filename = $this->downloadImage($imageUrl, $monster->getRuleSlug());
                    
                    if ($filename) {
                        $monster->setImgMain('uploads/monsters/' . $filename);
                        $this->entityManager->flush();
                        $io->success("Saved to: $filename");
                    } else {
                        $io->error("Failed to download image.");
                    }
                } else {
                    $io->warning("No suitable image found on wiki.");
                }

            } catch (\Exception $e) {
                $io->error("Error: " . $e->getMessage());
            }

            usleep(500000); // Politeness delay
        }

        return Command::SUCCESS;
    }

    private function findImageForMonster(Monster $monster, SymfonyStyle $io): ?string
    {
        // 1. Search Fandom (Internal scope, specifically File namespace)
        // Query construction: Name + Size + Type + "Monster Manual D&D"
        $parts = [$monster->getName()];
        if ($monster->getSize()) $parts[] = $monster->getSize();
        if ($monster->getType()) $parts[] = $monster->getType();
        $parts[] = 'Monster Manual D&D';
        
        $query = implode(' ', $parts);
        
        $io->text(sprintf('Search Query: "%s"', $query));

        $queryParams = [
            'query' => $query,
            'scope' => 'internal',
            'filter' => 'imageOnly',
            'ns' => [0 => 6] // Namespace 6 is File
        ];

        // Using http_build_query to handle encoding and arrays correctly
        $searchUrl = 'https://forgottenrealms.fandom.com/wiki/Special:Search?' . http_build_query($queryParams);
        
        $io->text(sprintf('Search URL: %s', $searchUrl));

        $response = $this->httpClient->request('GET', $searchUrl);
        $html = $response->getContent();

        // Regex to find the first result link.
        // In image search, the results are usually links to "File:..." pages.
        // We look for <a href="..." ... class="unified-search__result__title"> or similar within the result block.
        // Or specific to image results: <div class="unified-search__result__image"> ... <a href="...">
        // Let's look for any link containing "wiki/File:" acting as a result title or image wrapper.
        
        // Pattern: href="(https?://[^"]+/wiki/File:[^"]+)"
        // We capture the first one.
        if (preg_match('/href="([^"]+\/wiki\/File:[^"]+)"/i', $html, $matches)) {
            $pageUrl = $matches[1];
            $io->text("Found File Page: $pageUrl");
        } else {
             // Fallback: Check if we were redirected to a File page or if there's an immediate image
             // (Unlikely with Special:Search, but possible)
             if (preg_match('/property="og:image"\s+content="([^"]+)"/i', $html, $imgMatches)) {
                return $imgMatches[1];
             }
             return null;
        }

        // 2. Visit the File Page to get the high-res URL
        $pageResponse = $this->httpClient->request('GET', $pageUrl);
        $pageHtml = $pageResponse->getContent();

        // 3. Extract Image
        // On a File page, og:image is usually the full resolution image or a high quality preview.
        // Alternatively, look for the "Original file" link or the main displayed image.
        if (preg_match('/property="og:image"\s+content="([^"]+)"/i', $pageHtml, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function downloadImage(string $url, string $slug): ?string
    {
        try {
            $response = $this->httpClient->request('GET', $url);
            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $content = $response->getContent();
            $extension = 'jpg'; // Default
            
            // Try to guess extension from content-type
            $headers = $response->getHeaders();
            $contentType = $headers['content-type'][0] ?? 'image/jpeg';
            if (str_contains($contentType, 'png')) $extension = 'png';
            if (str_contains($contentType, 'webp')) $extension = 'webp';

            $filename = $slug . '.' . $extension;
            $filepath = $this->targetDir . '/' . $filename;

            file_put_contents($filepath, $content);

            return $filename;

        } catch (\Exception $e) {
            return null;
        }
    }
}
