<?php

namespace App\Command;

use App\Entity\RulesSource;
use App\Entity\Trinket;
use App\Repository\RulesSourceRepository;
use App\Repository\TrinketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-trinkets',
    description: 'Import trinkets from docs/bugigangas.md',
)]
class ImportTrinketsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RulesSourceRepository $rulesSourceRepository,
        private TrinketRepository $trinketRepository,
        private string $projectDir
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $this->projectDir . '/docs/bugigangas.md';

        if (!file_exists($filePath)) {
            $io->error("File not found: $filePath");
            return Command::FAILURE;
        }

        // 1. Get or Create RulesSource
        $sourceSlug = 'phb_br'; // Player's Handbook (Brazilian Portuguese)
        $rulesSource = $this->rulesSourceRepository->findOneBy(['slug' => $sourceSlug]);

        if (!$rulesSource) {
            $rulesSource = new RulesSource();
            $rulesSource->setSlug($sourceSlug);
            $rulesSource->setName('Livro do Jogador (PT-BR)');
            $this->entityManager->persist($rulesSource);
            $this->entityManager->flush();
            $io->note("Created new RulesSource: $sourceSlug");
        }

        // 2. Parse File
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $trinkets = [];
        $currentRoll = null;
        $currentText = '';

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip headers or unrelated lines
            if (stripos($line, 'Bugigangas') !== false || empty($line)) {
                continue;
            }

            // Match "01 Description..." or "100 Description..."
            if (preg_match('/^(\d+)\s+(.+)$/', $line, $matches)) {

                // Save previous if exists
                if ($currentRoll !== null) {
                    $trinkets[$currentRoll] = $currentText;
                }

                $currentRoll = (int) $matches[1];
                // Handle "00" as 100
                if ($matches[1] === '00') {
                    $currentRoll = 100;
                }
                $currentText = trim($matches[2]);
            } else {
                // Continuation of previous line
                if ($currentRoll !== null) {
                    $currentText .= ' ' . $line;
                }
            }
        }
        // Save last one
        if ($currentRoll !== null) {
            $trinkets[$currentRoll] = $currentText;
        }

        // 3. Persist to Database
        $count = 0;
        foreach ($trinkets as $roll => $text) {
            $trinket = $this->trinketRepository->findOneBy([
                'rollKey' => $roll,
                'rulesSource' => $rulesSource
            ]);

            if (!$trinket) {
                $trinket = new Trinket();
                $trinket->setRollKey($roll);
                $trinket->setRulesSource($rulesSource);
            }

            $trinket->setTextMd($text);
            $this->entityManager->persist($trinket);
            $count++;
        }

        $this->entityManager->flush();

        $io->success("Imported/Updated $count trinkets successfully.");

        return Command::SUCCESS;
    }
}
