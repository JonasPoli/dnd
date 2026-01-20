<?php

namespace App\Command;

use App\Entity\Species;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate_species_traits',
    description: 'Extracts racial traits from descriptionMd to the new traits field',
)]
class MigrateSpeciesTraitsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $speciesRepo = $this->entityManager->getRepository(Species::class);
        $allSpecies = $speciesRepo->findAll();

        $count = 0;
        foreach ($allSpecies as $species) {
            $desc = $species->getDescriptionMd();
            if (!$desc) continue;

            // Pattern: "### Traços de [Race]"
            // We look for "### Traços de " and take everything after it until the next "# " or end of string.
            // Note: Since "### Traços de " usually expects the race name, we can regex for `### Traços de .*`
            
            // Regex explanation:
            // ### Traços de .*?(\n|\r\n)   -> Match header line
            // ([\s\S]*?)                   -> Match content non-greedily
            // (?=^#|\z)                    -> Lookahead for next header starting with # or End of String. 
            // Multiline mode needed for ^ anchor? 
            
            // Simpler approach: find position of "### Traços de"
            $marker = "### Traços de";
            $pos = strpos($desc, $marker);
            
            if ($pos !== false) {
                // Find end of this line (skip the header itself)
                $endOfHeader = strpos($desc, "\n", $pos);
                if ($endOfHeader === false) {
                     // Header is the last line? Unlikely for content but possible.
                     $content = ""; 
                } else {
                    $startOfContent = $endOfHeader + 1;
                    $rest = substr($desc, $startOfContent);
                    
                    // Find next header (line starting with #)
                    // We can use regex to find next ^#
                    if (preg_match('/^#/m', $rest, $matches, PREG_OFFSET_CAPTURE)) {
                        $endOfContent = $matches[0][1]; // Offset of match
                        $traitsContent = substr($rest, 0, $endOfContent);
                    } else {
                        // No distinct next header found, take all
                        $traitsContent = $rest;
                    }

                    $traitsContent = trim($traitsContent);
                    
                    if (!empty($traitsContent)) {
                        $species->setTraits($traitsContent);
                        $count++;
                        $io->writeln("Updated: " . $species->getName());
                    }
                }
            }
        }

        $this->entityManager->flush();

        $io->success("Migration complete. Updated $count species.");

        return Command::SUCCESS;
    }
}
