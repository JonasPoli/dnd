<?php

namespace App\Command;

use App\Entity\Species;
use App\Entity\Subrace;
use App\Repository\SpeciesRepository;
use App\Repository\SubraceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:import:species-2024',
    description: 'Import 2024 species traits from docs/species-2024.txt',
)]
class ImportSpecies2024Command extends Command
{
    private string $projectDir;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private SpeciesRepository $speciesRepository,
        private SubraceRepository $subraceRepository,
        private KernelInterface $kernel
    ) {
        parent::__construct();
        $this->projectDir = $this->kernel->getProjectDir();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $this->projectDir . '/docs/species-2024.txt';

        if (!file_exists($filePath)) {
            $io->error("File not found: $filePath");
            return Command::FAILURE;
        }

        $content = file_get_contents($filePath);
        $blocks = explode("\n\n", $content); // Split by double newlines for species blocks

        foreach ($blocks as $block) {
            $lines = explode("\n", trim($block));
            if (empty($lines)) continue;

            $speciesName = trim($lines[0]);
            $io->section("Processing Species: $speciesName");

            $species = $this->speciesRepository->findOneBy(['name' => $speciesName]);
            if (!$species) {
                // If specific 2024 species doesn't exist, we skip or create?
                // The prompt implies we are UPDATING/FIXING. Let's create if missing to be safe.
                $species = new Species();
                $species->setName($speciesName);
                $species->setSpeedWalk(9); // Default 9m/30ft
                $species->setSize('Médio'); // Default
                $this->entityManager->persist($species);
                $io->text("Creating new species: $speciesName");
            }
            
            $traits = [];
            $subracesData = []; // Special handling for nested subraces

            // Determine if we are inside a subrace block (hacky parsing)
            // The text format provided by user:
            // "Linhagens Elfas: Você escolhe... (Elfo da Floresta: ...; Alto Elfo: ...)"
            
            for ($i = 1; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                if (empty($line)) continue;

                // KEY: Value
                if (str_contains($line, ':')) {
                    [$traitName, $traitDesc] = explode(':', $line, 2);
                    $traitName = trim($traitName);
                    $traitDesc = trim($traitDesc);

                    // Check for Subrace Definitions
                    // e.g., "Linhagens Elfas" or "Linhagens Gnômicas" or "Legado Ínfero"
                    if (str_contains($line, 'Linhagens') || str_contains($line, 'Legado') || str_contains($line, 'Ancestralidade Gigante')) {
                         // This is a subrace definition line.
                         // Check for parenthesis content which defines the subraces and their specific traits.
                         // e.g. "... (Elfo da Floresta: Passos Longos...; Alto Elfo: ...)"
                         
                         if (preg_match('/\((.*)\)/', $traitDesc, $matches)) {
                             $contentInside = $matches[1];
                             // Split by semicolon usually separates subraces? 
                             // Example: "Elfo da Floresta: ...; Alto Elfo: ..."
                             // OR "Abissal (Caos...): ...; Ctônico..."
                             // It's tricky.
                             
                             // Let's look at the structure more carefully.
                             // "Elfo da Floresta: Passos Longos e Artifício do Passageiro; Alto Elfo: Prestidigitação, Detectar Magia; Drow: Globos de Luz, Fogo Fátuo"
                             
                             $subBlocks = explode(';', $contentInside);
                             foreach ($subBlocks as $sb) {
                                 if (str_contains($sb, ':')) {
                                     [$srName, $srTraits] = explode(':', $sb, 2);
                                     $srName = trim($srName);
                                     // Remove extra text if any (e.g. "(Wood Elf)")
                                     // but simplistic approach first.
                                     $subracesData[] = [
                                         'name' => $srName,
                                         'traits' => [
                                             [
                                                 'name' => 'Traços de Linhagem',
                                                 'description' => trim($srTraits),
                                                 'code' => 'subrace_traits'
                                             ]
                                         ]
                                     ];
                                 }
                             }
                         }
                         
                         // Also add the main trait to the species as a "Choice" marker
                         $traits[] = [
                             'name' => $traitName,
                             'description' => $traitDesc,
                             'code' => mb_strtolower(str_replace(' ', '_', $traitName)),
                             'type' => 'subrace_choice' 
                         ];

                    } else {
                        // Regular Trait
                        $code = mb_strtolower(str_replace(' ', '_', $traitName));
                        $type = 'passive';
                        
                        $extra = [];
                        
                        // Infer type & details
                        if (stripos($traitDesc, 'você adquire') !== false && stripos($traitDesc, 'à sua escolha') !== false) {
                            $type = 'choice';
                            
                            // Detect Feat
                            if (stripos($traitDesc, 'talento') !== false) {
                                $extra['choice_type'] = 'feat';
                                if (stripos($traitDesc, 'origem') !== false) {
                                    $extra['pool'] = 'origin';
                                }
                            }
                            // Detect Skill
                            elseif (stripos($traitDesc, 'perícia') !== false) {
                                $extra['choice_type'] = 'skill';
                            }
                        }

                        if (stripos($traitName, 'Visão') !== false) {
                            $type = 'sense';
                        }
                        if (stripos($traitName, 'Resistência') !== false) {
                            $type = 'resistance';
                        }

                        $traits[] = array_merge([
                            'name' => $traitName,
                            'description' => $traitDesc,
                            'code' => $code,
                            'type' => $type
                        ], $extra);
                    }
                }
            }

            $species->setTraits($traits);
            
            // Handle Subraces
            if (!empty($subracesData)) {
                // Clear existing subraces? Or Update?
                // Safe strategy: Update if name match, Create if new.
                foreach ($subracesData as $srData) {
                    $subrace = $this->subraceRepository->findOneBy(['species' => $species, 'name' => $srData['name']]);
                    if (!$subrace) {
                         // Try fuzzy matching or just create
                         // "Elfo da Floresta" might map to "Wood Elf" if user has English data.
                         // But we are standardizing on PT.
                         $subrace = new Subrace();
                         $subrace->setSpecies($species);
                         $subrace->setName($srData['name']);
                         $this->entityManager->persist($subrace);
                    }
                    $subrace->setTraits($srData['traits']);
                }
            }
        }

        $this->entityManager->flush();
        $io->success('Import complete!');

        return Command::SUCCESS;
    }
}
