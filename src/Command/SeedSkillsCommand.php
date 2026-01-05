<?php

namespace App\Command;

use App\Entity\Attribute;
use App\Entity\Skill;
use App\Repository\AttributeRepository;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:seed:skills',
    description: 'Seeds skills from CSV file',
)]
class SeedSkillsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AttributeRepository $attributeRepository,
        private SkillRepository $skillRepository,
        private ParameterBagInterface $params
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $this->params->get('kernel.project_dir') . '/docs/csv/pericia.csv';

        if (!file_exists($filePath)) {
            $io->error("File not found: $filePath");
            return Command::FAILURE;
        }

        $io->section('Seeding Skills');

        if (($handle = fopen($filePath, "r")) !== FALSE) {
            // Skip header row
            $header = fgetcsv($handle);

            $count = 0;
            while (($data = fgetcsv($handle)) !== FALSE) {
                // CSV Structure: Perícia, Atributo, Exemplos de Usos
                // Index: 0, 1, 2
                $name = trim($data[0]);
                $attributeName = trim($data[1]);
                $description = trim($data[2]);

                if (empty($name) || empty($attributeName)) {
                    continue;
                }

                // Find Attribute
                $attribute = $this->attributeRepository->findOneBy(['name' => $attributeName]);
                if (!$attribute) {
                    $io->warning("Attribute '$attributeName' not found for skill '$name'. Skipping.");
                    continue;
                }

                // Generate slug key
                $key = strtolower($this->slugify($name));

                // Find or Create Skill
                $skill = $this->skillRepository->findOneBy(['key' => $key]);
                if (!$skill) {
                    $skill = new Skill();
                    $skill->setKey($key);
                }

                $skill->setName($name);
                $skill->setAttribute($attribute);
                $skill->setDescription($description);

                $this->entityManager->persist($skill);
                $count++;
            }
            fclose($handle);

            $this->entityManager->flush();
            $io->success("Seeded/Updated $count skills successfully.");
        } else {
            $io->error("Could not open file: $filePath");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function slugify(string $text): string
    {
        // Simple slugify for "Perícia" -> "pericia", "Lidar com Animais" -> "lidar-com-animais"
        // remove accents
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '-', $text);
        // trim
        $text = trim($text, '-');
        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
