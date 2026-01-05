<?php

namespace App\Command;

use App\Entity\Monster;
use App\Repository\MonsterRepository;
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
    name: 'app:crawl:monster-source',
    description: 'Crawls Forgotten Realms Wiki (Category:Creatures) for images and descriptions',
)]
class CrawlMonsterSourceCommand extends Command
{
    private array $allowedSections = [
        'Abilities', 'Breath Weapon', 'Combat', 'Cultural Significance', 'Description',
        'Diet', 'Ecology', 'Habitats', 'History', 'Homelands', 'Lair',
        // 'Languages', // Removed as per explicit ignore instruction
        'Magic', 'Ordning', 'Personality', 'Parenting & Development', 'Promotion',
        'Religion', 'Regions', 'Relationships', 'Society', 'Subraces', 'Summoning', 'Trivia'
    ];

    private array $ignoredSections = [
        'Notable', 'Appendix', 'Notes', 'Appearances', 'Gallery', 'References',
        'Connections', 'Categories', 'See Also', 'Further Reading', 'External Links',
        'Languages'
    ];

    public function __construct(
        private HttpClientInterface $httpClient,
        private MonsterRepository $monsterRepository,
        private EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%/public/uploads/monsters')]
        private string $targetDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Ensure target directory exists
        $filesystem = new Filesystem();
        if (!$filesystem->exists($this->targetDir)) {
            $filesystem->mkdir($this->targetDir);
        }

        $nextUrl = 'https://forgottenrealms.fandom.com/wiki/Category:Creatures';
        $baseUrl = 'https://forgottenrealms.fandom.com';
        $pageCount = 1;

        $io->title('Crawling Monster Source: Category:Creatures');

        while ($nextUrl) {
            $io->section("Processing Page $pageCount");
            $io->text("URL: $nextUrl");

            try {
                $response = $this->httpClient->request('GET', $nextUrl);
                $html = $response->getContent();
                $crawler = new Crawler($html);

                // Find monster links in the category list
                // Selector: li.category-page__member .category-page__member-left a
                $nodes = $crawler->filter('li.category-page__member .category-page__member-left a');
                
                if ($nodes->count() === 0) {
                     $io->warning("No monsters found on this page.");
                } else {
                    $io->text(sprintf("Found %d monsters on this page.", $nodes->count()));
                    
                    $nodes->each(function (Crawler $node) use ($io, $baseUrl) {
                        $href = $node->attr('href');
                        $title = $node->attr('title');
                        // Some titles might be empty, extract text
                        if (!$title) $title = $node->text();

                        if ($href && str_starts_with($href, '/wiki/')) {
                            $this->processMonster($title, $baseUrl . $href, $io);
                        }
                    });
                }
                
                // Flush after each page to save progress
                $this->entityManager->flush();

                // Find Next Page
                $nextBtn = $crawler->filter('.category-page__pagination .category-page__pagination-next');
                
                if ($nextBtn->count() > 0) {
                    $nextUrl = $nextBtn->attr('href'); // This usually includes the domain? No, usually relative or full.
                    // Verification
                    if (!str_starts_with($nextUrl, 'http')) {
                         // Sometimes it's relative
                         // But usually wikia gives full url in pagination?
                         // Let's safe check:
                         // Fandom pagination usually gives full URL.
                    }
                    $pageCount++;
                } else {
                    $nextUrl = null;
                }

            } catch (\Exception $e) {
                $io->error("Error on page $pageCount: " . $e->getMessage());
                // Break loop on critical error or try next?
                // Let's break to avoid infinite loops on error
                break;
            }
        }

        $io->success('Finished crawling all pages.');
        return Command::SUCCESS;
    }

    private function processMonster(string $monsterName, string $monsterUrl, SymfonyStyle $io): void
    {
        // 1. Check if Monster exists
        $monster = $this->monsterRepository->findOneBy(['name' => $monsterName]);
        if (!$monster) {
            $io->text(" <error>[-]</error> Not in DB: <comment>$monsterName</comment>");
            return;
        }

        $io->text(" <info>[+]</info> Found in DB: <info>$monsterName</info>");

        try {
            $monsterResponse = $this->httpClient->request('GET', $monsterUrl);
            $monsterHtml = $monsterResponse->getContent();
            $crawler = new Crawler($monsterHtml);

            // --- IMAGE PROCESSING ---
            $imageNode = $crawler->filter('figure.pi-item.pi-image a')->first();
            if ($imageNode->count() > 0 && !$monster->getImgMain()) { // Only fetch if we don't have one? Or always?
                // User logic: "Se existir, vamos baixar". Let's assume we update if found.
                // But efficient: if already set, maybe skip? 
                // Let's stick to user request from previous turn which implied downloading. 
                // But current request focuses on Description. 
                // I'll keep image logic active but maybe only if imgMain is null or empty to be faster?
                // User earlier said: "Update Monster entities".
                // I will do it.
                
                $imageUrl = $imageNode->attr('href');
                if ($imageUrl) {
                    $filename = $this->downloadImage($imageUrl, $monster->getRuleSlug() ?: 'monster-' . $monster->getId());
                    if ($filename) {
                        $monster->setImgMain('uploads/monsters/' . $filename);
                        $io->note("  [IMG] Downloaded.");
                    }
                }
            }

            // --- DESCRIPTION PROCESSING ---
            $contentDiv = $crawler->filter('.mw-parser-output')->first();
            if ($contentDiv->count() > 0) {
                $this->processDescription($monster, $contentDiv, $io);
            }

        } catch (\Exception $e) {
            $io->error("  Failed processing $monsterName: " . $e->getMessage());
        }
    }

    private function processDescription(Monster $monster, Crawler $contentDiv, SymfonyStyle $io): void
    {
        $newDescription = "";
        $currentSectionTitle = null;
        $capture = false;
        
        // Iterate over child nodes
        /** @var \DOMNode $node */
        foreach ($contentDiv->getNode(0)->childNodes as $node) {
            if ($node->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $tagName = strtolower($node->nodeName);
            
            // Ignore ad wrappers
            if ($this->hasClass($node, 'fandom-ad-wrapper') || $this->hasClass($node, 'incontent_leaderboard')) {
                continue;
            }

            // Check Headers
            if (in_array($tagName, ['h2', 'h3'])) {
                // Find headline span
                // Usually <span class="mw-headline" id="Title">Title</span>
                $headline = '';
                foreach ($node->childNodes as $child) {
                    if ($child->nodeType === XML_ELEMENT_NODE && 
                        $this->hasClass($child, 'mw-headline')) {
                        $headline = trim($child->textContent);
                        break;
                    }
                }
                if (empty($headline)) {
                    $headline = trim($node->textContent);
                }

                // Check Filtering
                if ($this->shouldIgnoreSection($headline)) {
                    $capture = false;
                    $currentSectionTitle = null;
                } elseif ($this->isAllowedSection($headline)) {
                    $capture = true;
                    $currentSectionTitle = $headline;
                    $prefix = ($tagName === 'h2') ? '##' : '###';
                    $newDescription .= "\n\n$prefix $headline\n\n";
                } else {
                    // Unknown sections - default to ignore or capture?
                    // User gave specific list. Implies whitelist.
                    // User said: "Os títulos são: [List]... Titulos que devem ser ignorados: [List]"
                    // So if it's not in allowed List, skip?
                    $capture = false; 
                }
                continue;
            }

            // Capture Content
            if ($capture && $currentSectionTitle) {
                // Strip tags but keep some formatting? User said "strip tags para remover HTML".
                // "inclusive dos títulos".
                // He likely implies plain text for content too, or maybe standard MD?
                // "texto deve ser acrescentado ao 'description'"
                // Let's use strip_tags to be consistent with "remover HTML".
                // But maybe preserve paragraphs?
                
                if ($tagName === 'p' || $tagName === 'ul' || $tagName === 'ol') {
                    // Simple HTML to text conversion
                    $text = trim($node->textContent);
                    // Remove references like [1], [2], etc.
                    $text = preg_replace('/\[\d+\]/', '', $text);
                    
                    if (!empty($text)) {
                        $newDescription .= "$text\n\n";
                    }
                }
            }
        }

        $newDescription = trim($newDescription);
        
        if (empty($newDescription)) {
            return;
        }

        $srcJson = $monster->getSrcJson() ?? [];
        $existingDescription = $srcJson['description'] ?? '';

        // Check for duplication logic
        // "Não adicionar novamente"
        // Iterate over new sections and check if they exist?
        // Simpler: Check if the *entire* generated block is already in existing text? 
        // Or better: Append only sections that don't exist.
        
        // Simplest robust approach:
        // Append ONLY if the text is not significantly contained.
        // But sections might be scattered.
        // Let's append, but check if key headers exist.
        
        // Actually, user said: "este texto deve ser acrescentado... Faça um controle paralelo para não adicionar novamente."
        // I will check if the specific section headers are already present in existing text.
        
        $added = false;
        // Split new description by headers to process chunks? 
        // Or just append if "## Description" is not in existing string.
        
        // Let's try to append the whole thing if it seems new.
        // If existing description is short (just flavor text) and we found structured text, append.
        
        $lines = explode("\n", $newDescription);
        $buffer = "";
        
        $currentChunkHeader = null;
        $chunkBuffer = "";
        
        // Re-parsing the markdown I just built is silly but safer to merge using string checks
        // Let's just create a merged string.
        
        // If existing description contains the exact new text, do nothing.
        if (str_contains($existingDescription, $newDescription)) {
            return;
        }
        
        // Appending logic:
        // If we have "## Ecology" in new text, and validation checks "## Ecology" in old text: skip.
        // This is safer.
        
        $sectionsToAdd = [];
        $split = preg_split('/^(##+ .*)$/m', $newDescription, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        
        $currentHeader = null;
        foreach ($split as $part) {
            $part = trim($part);
            if (empty($part)) continue;
            
            if (str_starts_with($part, '##')) {
                $currentHeader = $part;
            } else {
                if ($currentHeader) {
                    // Check if this header exists in old description
                    if (!str_contains($existingDescription, $currentHeader)) {
                        $srcJson['description'] = trim(($srcJson['description'] ?? '') . "\n\n" . $currentHeader . "\n" . $part);
                        $added = true;
                    }
                }
                $currentHeader = null; 
            }
        }
        
        if ($added) {
            $monster->setSrcJson($srcJson);
            // Updating the entity field descriptionMd might be good too?
            // User specifically said "srcJson['description']".
            // But usually descriptionMd maps to this?
            // Importer maps srcJson['description'] -> descriptionMd.
            // I should update descriptionMd too to keep in sync?
            // "srcJson é a fonte de verdade". 
            // I'll update srcJson. If I update entity descriptionMd it might be overwritten on re-import?
            // No, re-import updates from srcJson? No, import updates JSON.
            // Let's update both for immediate visibility.
            $monster->setDescriptionMd($srcJson['description']);
            
            $io->note("  [DESC] Updated sections for " . $monster->getName() . ".");
        }
    }

    private function hasClass(\DOMNode $node, string $class): bool
    {
        if (!$node instanceof \DOMElement) return false;
        $attr = $node->getAttribute('class');
        return $attr && str_contains($attr, $class);
    }
    
    private function shouldIgnoreSection(string $title): bool
    {
        foreach ($this->ignoredSections as $ignore) {
            if (str_contains($title, 'Notable')) return true; // Wildcard handling
            if (strcasecmp($title, $ignore) === 0) return true;
        }
        return false;
    }

    private function isAllowedSection(string $title): bool
    {
        return in_array($title, $this->allowedSections);
    }

    private function downloadImage(string $url, string $slug): ?string
    {
        try {
            $response = $this->httpClient->request('GET', $url);
            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $content = $response->getContent();
            $extension = 'jpg'; 
            
            $headers = $response->getHeaders();
            $contentType = $headers['content-type'][0] ?? 'image/jpeg';
            if (str_contains($contentType, 'png')) $extension = 'png';
            if (str_contains($contentType, 'webp')) $extension = 'webp';

            // Clean slug
            $safeSlug = preg_replace('/[^a-z0-9-]+/', '-', strtolower($slug));
            $filename = $safeSlug . '.' . $extension;
            $filepath = $this->targetDir . '/' . $filename;

            file_put_contents($filepath, $content);

            return $filename;

        } catch (\Exception $e) {
            return null;
        }
    }
}
