<?php

namespace App\Command;

use App\Entity\Monster;
use App\Entity\RulesSource;
use App\Repository\MonsterRepository;
use App\Repository\RulesSourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:import:monsters-json',
    description: 'Imports monsters from external JSON source including images',
)]
class ImportMonstersJsonCommand extends Command
{
    private const MONSTERS_JSON_URL = 'https://raw.githubusercontent.com/alexandregpereira/Monster-Compendium-Content/refs/heads/main/json/monsters.json';
    private const IMAGES_JSON_URL = 'https://raw.githubusercontent.com/alexandregpereira/Monster-Compendium-Content/main/json/monster-images.json';

    public function __construct(
        private HttpClientInterface $httpClient,
        private MonsterRepository $monsterRepository,
        private RulesSourceRepository $rulesSourceRepository,
        private EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%/public/uploads/monsters')]
        private string $targetDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Importing Monsters from JSON');

        // 1. Ensure Target Directory
        $filesystem = new Filesystem();
        if (!$filesystem->exists($this->targetDir)) {
            $filesystem->mkdir($this->targetDir);
        }

        // 2. Fetch Data
        $io->section('Fetching Data...');
        try {
            $monstersData = $this->httpClient->request('GET', self::MONSTERS_JSON_URL)->toArray();
            $imagesData = $this->httpClient->request('GET', self::IMAGES_JSON_URL)->toArray();
            $io->success(sprintf('Fetched %d monsters and %d image records.', count($monstersData), count($imagesData)));
        } catch (\Exception $e) {
            $io->error('Failed to fetch data: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // 3. Build Image Map
        $imageMap = [];
        foreach ($imagesData as $img) {
            if (isset($img['monster_index']) && isset($img['image_url'])) {
                $imageMap[$img['monster_index']] = $img['image_url'];
            }
        }

        // 4. Ensure Rules Source
        $source = $this->rulesSourceRepository->findOneBy(['name' => 'System Reference Document 5.1']);
        if (!$source) {
            $source = new RulesSource();
            $source->setName('System Reference Document 5.1');
            $source->setSlug('srd');
            $this->entityManager->persist($source);
            $this->entityManager->flush();
            $io->note('Created default Rules Source: SRD');
        }

        // 5. Process Monsters
        $io->section('Processing Monsters...');

        $updatedCount = 0;
        $createdCount = 0;

        foreach ($monstersData as $m) {
            $index = $m['index'] ?? null;
            if (!$index)
                continue;

            $monster = $this->monsterRepository->findOneBy(['ruleSlug' => $index]);
            $imageUrl = $imageMap[$index] ?? null;

            if ($monster) {
                // Update Existing
                if ($imageUrl && !$monster->getImgMain()) {
                    $filename = $this->downloadImage($imageUrl, $index);
                    if ($filename) {
                        $monster->setImgMain('uploads/monsters/' . $filename);
                        $this->entityManager->flush();
                        $io->writeln("<info>Updated {$monster->getName()} with new image</info>"); // GREEN message
                        $updatedCount++;
                    }
                }
            } else {
                // Create New
                $this->createMonster($m, $source, $imageUrl, $io);
                $createdCount++;
            }
        }

        $io->success(sprintf('Finished. Created: %d, Updated Images: %d', $createdCount, $updatedCount));

        // 6. Backfill Missing Images
        $io->section('Backfilling Missing Images...');

        // Increase memory limit for this process
        ini_set('memory_limit', '512M');

        $query = $this->monsterRepository->createQueryBuilder('m')
            ->where('m.imgMain IS NULL')
            ->getQuery();

        $iterable = $query->toIterable();
        $backfilledCount = 0;
        $batchSize = 20;
        $idx = 0;

        foreach ($iterable as $noImgMonster) {
            $slug = $noImgMonster->getRuleSlug();
            if ($slug && isset($imageMap[$slug])) {
                $filename = $this->downloadImage($imageMap[$slug], $slug);
                if ($filename) {
                    $noImgMonster->setImgMain('uploads/monsters/' . $filename);
                    $io->writeln("<info>Backfilled image for {$noImgMonster->getName()}</info>");
                    $backfilledCount++;
                }
            }

            if (($idx++ % $batchSize) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear(); // Detach all to free memory
                // Re-fetch default rules source if needed later, but we are just looping.
            }
        }

        // Final flush
        $this->entityManager->flush();

        if ($backfilledCount > 0) {
            $io->success("Backfilled $backfilledCount images.");
        } else {
            $io->info("No missing images found to backfill.");
        }

        // 7. Sync Images by Name (Sibling Check)
        $io->section('Syncing Images by Name...');

        // Use a clean query for reliability
        $missingQuery = $this->monsterRepository->createQueryBuilder('m')
            ->where('m.imgMain IS NULL')
            ->getQuery();

        $missingIterable = $missingQuery->toIterable();
        $syncedCount = 0;
        $batchSize = 20;
        $idx = 0;

        foreach ($missingIterable as $noImgMonster) {
            $name = $noImgMonster->getName();
            if (!$name)
                continue;

            // Find ONE sibling with an image
            $sibling = $this->monsterRepository->createQueryBuilder('s')
                ->where('s.name = :name')
                ->andWhere('s.imgMain IS NOT NULL')
                ->setParameter('name', $name)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if ($sibling) {
                $noImgMonster->setImgMain($sibling->getImgMain());
                $io->writeln("<info>Synced image for {$name} (ID: {$noImgMonster->getId()}) from sibling (ID: {$sibling->getId()})</info>");
                $syncedCount++;
            }

            if (($idx++ % $batchSize) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        $this->entityManager->flush();
        $io->success("Synced $syncedCount images from siblings.");

        // 8. Wiki Fallback Crawl
        $io->section('Wiki Fallback Crawl...');

        $finalMissingQuery = $this->monsterRepository->createQueryBuilder('m')
            ->where('m.imgMain IS NULL')
            ->getQuery();

        $finalIterable = $finalMissingQuery->toIterable();
        $wikiRecoveredCount = 0;
        $idx = 0;

        foreach ($finalIterable as $noImgMonster) {
            $recovered = $this->crawlWikiImage($noImgMonster, $io);
            if ($recovered) {
                $wikiRecoveredCount++;
            }

            if (($idx++ % $batchSize) === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }

            // Be nice to the server
            usleep(250000); // 250ms delay
        }

        $this->entityManager->flush();

        if ($wikiRecoveredCount > 0) {
            $io->success("Recovered $wikiRecoveredCount images from Wiki.");
        } else {
            $io->info("No images recovered from Wiki.");
        }

        return Command::SUCCESS;
    }

    private function crawlWikiImage(Monster $monster, SymfonyStyle $io): bool
    {
        $name = $monster->getName();
        if (!$name)
            return false;

        // Construct URL: https://forgottenrealms.fandom.com/wiki/Name
        // Handle spaces
        $wikiName = str_replace(' ', '_', $name);
        $url = "https://forgottenrealms.fandom.com/wiki/$wikiName";

        try {
            $response = $this->httpClient->request('GET', $url);
            if ($response->getStatusCode() !== 200) {
                // Try "Name_(5e)" variant?
                $url5e = $url . '_(5e)';
                $response = $this->httpClient->request('GET', $url5e);
                if ($response->getStatusCode() !== 200)
                    return false;
            }

            $html = $response->getContent();
            $crawler = new Crawler($html);

            // Look for main image
            // Usually figure.pi-item.pi-image a.image.image-thumbnail
            $imageNode = $crawler->filter('figure.pi-item.pi-image a')->first();

            if ($imageNode->count() > 0) {
                $imageUrl = $imageNode->attr('href');
                if ($imageUrl) {
                    $filename = $this->downloadImage($imageUrl, $monster->getRuleSlug() ?: 'wiki-' . $monster->getId());
                    if ($filename) {
                        $monster->setImgMain('uploads/monsters/' . $filename);
                        $io->writeln("<info>Recovered image for {$name} from Wiki</info>");
                        return true;
                    }
                }
            }

        } catch (\Exception $e) {
            // Silently fail or simple log?
            // $io->warning("Error crawling $name: " . $e->getMessage());
            return false;
        }

        return false;
    }

    private function createMonster(array $data, RulesSource $source, ?string $imageUrl, SymfonyStyle $io): void
    {
        $monster = new Monster();
        $monster->setRulesSource($source);
        $monster->setRuleSlug($data['index']);
        $monster->setName($data['name']);

        // Basic Fields
        $monster->setSize($data['size'] ?? null);
        $monster->setType($data['type'] ?? null);
        $monster->setSubtype($data['subtype'] ?? null);
        $monster->setGroup($data['group'] ?? null); // 'group' field in JSON maps to 'group' entity field
        $monster->setAlignment($data['alignment'] ?? null);
        $monster->setArmorClass($data['armor_class'] ?? null);
        $monster->setHitPoints($data['hit_points'] ?? null);
        $monster->setHitDice($data['hit_dice'] ?? null);

        // CR
        // JSON has 'challenge_rating' as float/int
        if (isset($data['challenge_rating'])) {
            $monster->setCr((string) $data['challenge_rating']);
            // Convert to string representation like "1/4" if needed?
            // The entity fields are `cr` (decimal) and `challengeRating` (string).
            // Let's populate decimal `cr`.
            // For string `challengeRating`, usually it's "1", "10", "1/4".
            // The JSON has float 0.25.
            $monster->setChallengeRating($this->formatCr($data['challenge_rating']));
            $monster->setCr((string) $data['challenge_rating']);
        }

        // Stats
        if (isset($data['ability_scores'])) {
            foreach ($data['ability_scores'] as $stat) {
                $val = $stat['value'];
                match ($stat['type']) {
                    'STRENGTH' => $monster->setStrength($val),
                    'DEXTERITY' => $monster->setDexterity($val),
                    'CONSTITUTION' => $monster->setConstitution($val),
                    'INTELLIGENCE' => $monster->setIntelligence($val),
                    'WISDOM' => $monster->setWisdom($val),
                    'CHARISMA' => $monster->setCharisma($val),
                    default => null,
                };
            }
        }

        // Saves
        if (isset($data['saving_throws'])) {
            foreach ($data['saving_throws'] as $save) {
                $mod = $save['modifier'];
                match ($save['type']) {
                    'STRENGTH' => $monster->setStrengthSave($mod),
                    'DEXTERITY' => $monster->setDexteritySave($mod),
                    'CONSTITUTION' => $monster->setConstitutionSave($mod),
                    'INTELLIGENCE' => $monster->setIntelligenceSave($mod),
                    'WISDOM' => $monster->setWisdomSave($mod),
                    'CHARISMA' => $monster->setCharismaSave($mod),
                    default => null,
                };
            }
        }

        // JSON Fields
        if (isset($data['speed']))
            $monster->setSpeedJson($data['speed']);
        if (isset($data['skills']))
            $monster->setSkillsJson($data['skills']);
        if (isset($data['special_abilities']))
            $monster->setSpecialAbilitiesJson($data['special_abilities']);
        if (isset($data['actions']))
            $monster->setActionsJson($data['actions']);
        if (isset($data['legendary_actions']))
            $monster->setLegendaryActionsJson($data['legendary_actions']);
        if (isset($data['reactions']))
            $monster->setReactionsJson($data['reactions']);

        // Array to String Lists
        if (isset($data['senses'])) {
            $monster->setSenses(is_array($data['senses']) ? implode(', ', $data['senses']) : $data['senses']);
        }
        // Languages is string in JSON? "Deep Speech, telepathy 120 ft."
        if (isset($data['languages'])) {
            $monster->setLanguages($data['languages']);
        }

        $this->mapListToString($monster, $data, 'damage_vulnerabilities', 'setDamageVulnerabilities');
        $this->mapListToString($monster, $data, 'damage_resistances', 'setDamageResistances');
        $this->mapListToString($monster, $data, 'damage_immunities', 'setDamageImmunities');
        $this->mapListToString($monster, $data, 'condition_immunities', 'setConditionImmunities');

        // Capture Source JSON
        $monster->setSrcJson($data);

        // Image
        if ($imageUrl) {
            $filename = $this->downloadImage($imageUrl, $data['index']);
            if ($filename) {
                $monster->setImgMain('uploads/monsters/' . $filename);
            }
        }

        $this->entityManager->persist($monster);
        $this->entityManager->flush(); // Flush per monster to ensure safety/updates? Or batch?
        // Flush per monster is safer for long running processes if memory is issue, but here 300 items is fine.
        // User asked for output "Added [Name]".

        $io->writeln("<info>Added {$monster->getName()} with new image</info>"); // GREEN message
    }

    private function mapListToString(Monster $monster, array $data, string $key, string $setter): void
    {
        if (!isset($data[$key]))
            return;

        $val = $data[$key];
        if (is_array($val)) {
            // Sometimes it's a list of strings ["fire", "poison"]
            // Sometimes list of objects? In this JSON it seems to be list of strings for these fields.
            // Check content of array.
            if (empty($val))
                return;
            if (is_string($val[0])) {
                $monster->$setter(implode(', ', $val));
            }
        } else if (is_string($val)) {
            $monster->$setter($val);
        }
    }

    private function formatCr(float|int $cr): string
    {
        if ($cr == 0.125)
            return "1/8";
        if ($cr == 0.25)
            return "1/4";
        if ($cr == 0.5)
            return "1/2";
        return (string) $cr;
    }

    private function downloadImage(string $url, string $slug): ?string
    {
        try {
            $response = $this->httpClient->request('GET', $url);
            if ($response->getStatusCode() !== 200)
                return null;

            $content = $response->getContent();

            // Determine extension
            $headers = $response->getHeaders();
            $contentType = $headers['content-type'][0] ?? 'image/png';
            $ext = 'png';
            if (str_contains($contentType, 'jpeg') || str_contains($contentType, 'jpg'))
                $ext = 'jpg';
            if (str_contains($contentType, 'webp'))
                $ext = 'webp';

            $filename = $slug . '.' . $ext;
            $filepath = $this->targetDir . '/' . $filename;

            file_put_contents($filepath, $content);
            return $filename;
        } catch (\Exception $e) {
            return null;
        }
    }
}
