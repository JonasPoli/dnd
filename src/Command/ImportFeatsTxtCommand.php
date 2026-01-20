<?php

namespace App\Command;

use App\Entity\Feat;
use App\Repository\FeatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import:feats-txt',
    description: 'Import feats from docs/livro-talentos.txt',
)]
class ImportFeatsTxtCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FeatRepository $featRepository,
        private \Symfony\Component\HttpKernel\KernelInterface $kernel
    ) {
        parent::__construct();
        $this->projectDir = $this->kernel->getProjectDir();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $this->projectDir . '/docs/livro-talentos.txt';

        if (!file_exists($filePath)) {
            $io->error("File not found: $filePath");
            return Command::FAILURE;
        }

        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        
        $feats = [];
        $currentFeat = null;
        $buffer = [];
        
        // State machine simplistic approach
        // 0: Search for Name
        // 1: Found Name, verify Type next line
        // 2: Reading Description
        
        $potentialName = null;

        foreach ($lines as $i => $line) {
            $line = trim($line);
            
            // Skip empty lines or page numbers/headers usually found in copy-paste
            if (empty($line) || is_numeric($line) || str_starts_with($line, 'CAPÍTULO') || str_starts_with($line, 'WAYNE ENGLAND')) {
                continue;
            }

            // Heuristic: Feat definition starts with a Name line, followed immediately by a Type line containing "Talento"
            if ($this->isTypeLine($line)) {
                if ($potentialName) {
                    // We found a type, so the previous line was the Name
                    if ($currentFeat) {
                        // Save previous feat
                        $currentFeat['description'] = implode("\n", $buffer);
                        $feats[] = $currentFeat;
                    }

                    // Start new feat
                    $currentFeat = [
                        'name' => $potentialName,
                        'type' => $this->normalizeType($line),
                        'prerequisite' => null,
                        'description' => ''
                    ];
                    $buffer = []; // Reset description buffer
                    
                    // Check for parenthesis in the type line for prerequisites? 
                    // Usually in the text: "Talento Geral (Pré-requisito: Nível 4...)"
                    if (str_contains($line, '(')) {
                       preg_match('/\((.*?)\)/', $line, $matches);
                       if (isset($matches[1])) {
                           $currentFeat['prerequisite'] = str_replace('Pré-requisito: ', '', $matches[1]);
                       }
                    }
                    
                    $potentialName = null; // Consumed
                }
            } else {
                // This line could be a name or part of description
                // If we are reading a feat, add to description buffer first
                // But keep it as a potential name for the next iteration
                
                if ($currentFeat) {
                    $buffer[] = $line;
                }
                $potentialName = $line;
            }
        }

        // Save last feat
        if ($currentFeat) {
            // Remove the last line from buffer as it was the potential name that turned out to be nothing or end of file
            // Actually, if it's end of file, we should keep it. 
            // The logic: $potentialName is set to $line. If loop ends, $line was processed.
            // If the last line was part of description, it's in buffer.
            // If it was a stray line, it doesn't matter. 
            // Since we add to buffer BEFORE setting potentialName, the buffer contains everything.
            // EXCEPT the line that was identified as "Name" for the NEXT feat, which triggers the save of CURRENT feat.
            
            // For the last feat, we just finish it.
            $currentFeat['description'] = implode("\n", $buffer);
            $feats[] = $currentFeat;
        }

        // Remove the "Name" of the next feat from the description of the current feat if it got mixed in?
        // My logic: 
        // 1. Line A (Name) -> $potentialName = A, Buffer adds A
        // 2. Line B (Type) -> Detected Type. 
        //    -> Previous $currentFeat saved. Description = Buffer (which includes A).
        //    WAIT. Buffer shouldn't include A if A is the name of the NEW feat.
        
        // Refined Logic
        // We need to clean the buffer of the *last* line if that line became the name of the new feat.
        
        // Let's restart the loop with a cleaner approach.
        // It's hard to distinguish "Header" from "Text" without lookahead.
        // But we know Type line is distinctive: "Talento de Origem", "Talento Geral", "Talento de Estilo de Luta"
        
        $feats = [];
        $buffer = [];
        
        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            
            if (empty($line) || is_numeric($line) || str_starts_with($line, 'CAPÍTULO') || str_starts_with($line, 'WAYNE ENGLAND')) {
                continue;
            }

            // Look ahead for type
            $nextLineIndex = -1;
            for ($j = $i + 1; $j < count($lines); $j++) {
                if (!empty(trim($lines[$j]))) {
                    $nextLineIndex = $j;
                    break;
                }
            }
            
            $nextLine = $nextLineIndex > -1 ? trim($lines[$nextLineIndex]) : '';

            if ($this->isTypeLine($nextLine)) {
                // Current $line is the Name
                // $nextLine is the Type
                
                // 1. Save accumulated buffer to the PREVIOUS feat (if exists)
                if (!empty($feats)) {
                    $lastIdx = count($feats) - 1;
                    $feats[$lastIdx]['description'] = implode("\n", $buffer);
                }
                
                $buffer = []; // Clear buffer for new feat
                
                // 2. Parse Type and Prerequisite
                $typeLine = $nextLine;
                $prereq = null;
                $type = $this->normalizeType($typeLine);
                
                if (str_contains($typeLine, '(')) {
                    preg_match('/\((.*?)\)/', $typeLine, $matches);
                    if (isset($matches[1])) {
                        $prereq = str_replace('Pré-requisito: ', '', $matches[1]);
                    }
                }

                // 3. Start new feat
                $feats[] = [
                    'name' => $line,
                    'type' => $type,
                    'prerequisite' => $prereq,
                    'description' => '' // to be filled
                ];
                
                $i = $nextLineIndex; // Advance main loop to skip the Type line
                continue;
            }
            
            // Otherwise, it's just content
            if (!empty($feats)) {
                $buffer[] = $line;
            }
        }
        
        // Flush last buffer
        if (!empty($feats)) {
            $lastIdx = count($feats) - 1;
            $feats[$lastIdx]['description'] = implode("\n", $buffer);
        }

        $io->section(sprintf('Found %d feats to process', count($feats)));

        $created = 0;
        $updated = 0;
        
        foreach ($feats as $featData) {
            // Cleanup Name (remove leading/trailing chars if any)
            $name = trim($featData['name']);
            // Remove asterisk if present (e.g. "Habilidoso*")
            $name = str_replace('*', '', $name);

            $feat = $this->featRepository->findOneBy(['name' => $name]);
            
            if (!$feat) {
                $feat = new Feat();
                $feat->setName($name);
                $created++;
            } else {
                $updated++;
            }

            $feat->setType($featData['type']);
            
            if ($featData['prerequisite']) {
                $feat->setPrerequisite($featData['prerequisite']);
            }
            
            $feat->setDescriptionMd($featData['description']);
            $feat->setIsActive(true);

            $this->entityManager->persist($feat);
        }

        $this->entityManager->flush();

        $io->success(sprintf('Import complete. Created: %d, Updated: %d', $created, $updated));
        
        return Command::SUCCESS;
    }

    private function isTypeLine(string $line): bool
    {
        return str_starts_with($line, 'Talento de Origem') || 
               str_starts_with($line, 'Talento Geral') || 
               str_starts_with($line, 'Talento de Estilo de Luta') ||
               str_starts_with($line, 'Talento de Dádiva Épica');
    }

    private function normalizeType(string $line): string
    {
        if (str_contains($line, 'Origem')) return 'Origem';
        if (str_contains($line, 'Estilo de Luta')) return 'Estilo de Luta';
        if (str_contains($line, 'Dádiva Épica')) return 'Dádiva Épica';
        return 'Geral';
    }
}
