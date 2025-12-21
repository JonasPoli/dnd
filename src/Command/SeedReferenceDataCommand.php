<?php

namespace App\Command;

use App\Entity\Alignment;
use App\Entity\Language;
use App\Entity\LevelUp;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed:reference-data',
    description: 'Seeds reference data (Alignment, Language, LevelUp)',
)]
class SeedReferenceDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Seed Alignments
        $io->section('Seeding Alignments');
        $this->seedAlignments();
        $io->success('9 alignments seeded');

        // Seed Languages
        $io->section('Seeding Languages');
        $this->seedLanguages();
        $io->success('16 languages seeded');

        // Seed Level Up
        $io->section('Seeding Level Up Table');
        $this->seedLevelUp();
        $io->success('20 levels seeded');

        $io->success('All reference data seeded successfully! Total: 45 records');

        return Command::SUCCESS;
    }

    private function seedAlignments(): void
    {
        $alignments = [
            ['LG', 'Lawful Good', 'Lawful good (LG) creatures can be counted on to do the right thing as expected by society. Gold dragons, paladins, and most dwarves are lawful good.'],
            ['NG', 'Neutral Good', 'Neutral good (NG) folk do the best they can to help others according to their needs. Many celestials, some cloud giants, and most gnomes are neutral good.'],
            ['CG', 'Chaotic Good', 'Chaotic good (CG) creatures act as their conscience directs, with little regard for what others expect. Copper dragons, many elves, and unicorns are chaotic good.'],
            ['LN', 'Lawful Neutral', 'Lawful neutral (LN) individuals act in accordance with law, tradition, or personal codes. Many monks and some wizards are lawful neutral.'],
            ['N', 'Neutral', 'Neutral (N) is the alignment of those who prefer to steer clear of moral questions and don\'t take sides, doing what seems best at the time. Lizardfolk, most druids, and many humans are neutral.'],
            ['CN', 'Chaotic Neutral', 'Chaotic neutral (CN) creatures follow their whims, holding their personal freedom above all else. Many barbarians and rogues, and some bards, are chaotic neutral.'],
            ['LE', 'Lawful Evil', 'Lawful evil (LE) creatures methodically take what they want, within the limits of a code of tradition, loyalty, or order. Devils, blue dragons, and hobgoblins are lawful evil.'],
            ['NE', 'Neutral Evil', 'Neutral evil (NE) is the alignment of those who do whatever they can get away with, without compassion or qualms. Many drow, some cloud giants, and goblins are neutral evil.'],
            ['CE', 'Chaotic Evil', 'Chaotic evil (CE) creatures act with arbitrary violence, spurred by their greed, hatred, or bloodlust. Demons, red dragons, and orcs are chaotic evil.'],
        ];

        foreach ($alignments as [$abbr, $name, $desc]) {
            $alignment = new Alignment();
            $alignment->setAbbreviation($abbr);
            $alignment->setName($name);
            $alignment->setDescription($desc);
            $this->entityManager->persist($alignment);
        }

        $this->entityManager->flush();
    }

    private function seedLanguages(): void
    {
        $languages = [
            // Standard Languages
            ['Common', 'Standard', 'Humans', 'Common'],
            ['Dwarvish', 'Standard', 'Dwarves', 'Dwarvish'],
            ['Elvish', 'Standard', 'Elves', 'Elvish'],
            ['Giant', 'Standard', 'Ogres, giants', 'Dwarvish'],
            ['Gnomish', 'Standard', 'Gnomes', 'Dwarvish'],
            ['Goblin', 'Standard', 'Goblinoids', 'Dwarvish'],
            ['Halfling', 'Standard', 'Halflings', 'Common'],
            ['Orc', 'Standard', 'Orcs', 'Dwarvish'],
            // Exotic Languages
            ['Abyssal', 'Exotic', 'Demons', 'Infernal'],
            ['Celestial', 'Exotic', 'Celestials', 'Celestial'],
            ['Draconic', 'Exotic', 'Dragons, dragonborn', 'Draconic'],
            ['Deep Speech', 'Exotic', 'Aboleths, cloakers', null],
            ['Infernal', 'Exotic', 'Devils', 'Infernal'],
            ['Primordial', 'Exotic', 'Elementals', 'Dwarvish'],
            ['Sylvan', 'Exotic', 'Fey creatures', 'Elvish'],
            ['Undercommon', 'Exotic', 'Underworld traders', 'Elvish'],
        ];

        foreach ($languages as [$name, $type, $speakers, $script]) {
            $language = new Language();
            $language->setLanguageKey(strtolower(str_replace(' ', '-', $name)));
            $language->setName($name);
            $language->setType($type);
            $language->setTypicalSpeakers($speakers);
            $language->setScript($script);
            $this->entityManager->persist($language);
        }

        $this->entityManager->flush();
    }

    private function seedLevelUp(): void
    {
        $levels = [
            [1, 0, 2],
            [2, 300, 2],
            [3, 900, 2],
            [4, 2700, 2],
            [5, 6500, 3],
            [6, 14000, 3],
            [7, 23000, 3],
            [8, 34000, 3],
            [9, 48000, 4],
            [10, 64000, 4],
            [11, 85000, 4],
            [12, 100000, 4],
            [13, 120000, 5],
            [14, 140000, 5],
            [15, 165000, 5],
            [16, 195000, 5],
            [17, 225000, 6],
            [18, 265000, 6],
            [19, 305000, 6],
            [20, 355000, 6],
        ];

        foreach ($levels as [$level, $xp, $bonus]) {
            $levelUp = new LevelUp();
            $levelUp->setLevel($level);
            $levelUp->setExperiencePoints($xp);
            $levelUp->setProficiencyBonus($bonus);
            $this->entityManager->persist($levelUp);
        }

        $this->entityManager->flush();
    }
}
