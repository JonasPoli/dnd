<?php

namespace App\Command;

use App\Entity\ClassDef;
use App\Entity\Attribute;
use App\Entity\Equipment;
use App\Enum\ArmorTraining;
use App\Enum\WeaponProficiency;
use App\Repository\ClassDefRepository;
use App\Repository\AttributeRepository;
use App\Repository\EquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-class-defs',
    description: 'Updates ClassDef entities with D&D 2024 rules data',
)]
class UpdateClassDefsCommand extends Command
{
    public function __construct(
        private ClassDefRepository $classDefRepository,
        private AttributeRepository $attributeRepository,
        private EquipmentRepository $equipmentRepository,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $classesData = [
            'Bárbaro' => [
                'primary' => ['Força'],
                'hitDie' => 12,
                'saves' => ['Força', 'Constituição'],
                'skillsCount' => 2,
                'weapons' => WeaponProficiency::SIMPLE_MARTIAL,
                'armor' => ArmorTraining::LIGHT_MEDIUM_SHIELDS,
                'equipment' => [
                    'A' => '4 Machadinhas, Machado Grande, Kit de Aventureiro e 15 PO',
                    'B' => '75 PO'
                ],
                // "Ferramentas" column is empty/varies? Prompt says: "(A)..."
                // No specific tool mentioned in table for Bárbaro.
            ],
            'Bardo' => [
                'primary' => ['Carisma'],
                'hitDie' => 8,
                'saves' => ['Destreza', 'Carisma'],
                'skillsCount' => 3,
                'weapons' => WeaponProficiency::SIMPLE,
                'armor' => ArmorTraining::LIGHT,
                'toolsCount' => 3, // "3 Instrumentos Musicais"
            ],
            'Bruxo' => [
                'primary' => ['Carisma'],
                'hitDie' => 8,
                'saves' => ['Sabedoria', 'Carisma'],
                'skillsCount' => 2,
                'weapons' => WeaponProficiency::SIMPLE,
                'armor' => ArmorTraining::LIGHT,
            ],
            'Clérigo' => [
                'primary' => ['Sabedoria'],
                'hitDie' => 8,
                'saves' => ['Sabedoria', 'Carisma'],
                'skillsCount' => 2,
                'weapons' => WeaponProficiency::SIMPLE,
                'armor' => ArmorTraining::LIGHT_MEDIUM_SHIELDS,
            ],
            'Druida' => [
                'primary' => ['Sabedoria'],
                'hitDie' => 8,
                'saves' => ['Inteligência', 'Sabedoria'],
                'skillsCount' => 2,
                'weapons' => WeaponProficiency::SIMPLE,
                'armor' => ArmorTraining::LIGHT_SHIELDS,
                'fixedTool' => 'Kit de Herbalismo',
            ],
            'Feiticeiro' => [
                'primary' => ['Carisma'],
                'hitDie' => 6,
                'saves' => ['Constituição', 'Carisma'],
                'skillsCount' => 2,
                'weapons' => WeaponProficiency::SIMPLE,
                'armor' => ArmorTraining::NONE,
            ],
            'Guerreiro' => [
                'primary' => ['Força', 'Destreza'],
                'hitDie' => 10,
                'saves' => ['Força', 'Constituição'],
                'skillsCount' => 2,
                'weapons' => WeaponProficiency::SIMPLE_MARTIAL,
                'armor' => ArmorTraining::ALL_SHIELDS,
            ],
            'Guardião' => [
                'primary' => ['Destreza', 'Sabedoria'],
                'hitDie' => 10,
                'saves' => ['Força', 'Destreza'],
                'skillsCount' => 3,
                'weapons' => WeaponProficiency::SIMPLE_MARTIAL,
                'armor' => ArmorTraining::LIGHT_MEDIUM_SHIELDS,
            ],
            'Ladino' => [
                'primary' => ['Destreza'],
                'hitDie' => 8,
                'saves' => ['Destreza', 'Inteligência'],
                'skillsCount' => 4,
                'weapons' => WeaponProficiency::SIMPLE_MARTIAL_FINESSE_LIGHT,
                'armor' => ArmorTraining::LIGHT,
                'fixedTool' => 'Ferramentas de Ladrão',
            ],
            'Mago' => [
                'primary' => ['Inteligência'],
                'hitDie' => 6,
                'saves' => ['Inteligência', 'Sabedoria'],
                'skillsCount' => 2,
                'weapons' => WeaponProficiency::SIMPLE,
                'armor' => ArmorTraining::NONE,
            ],
            'Monge' => [
                'primary' => ['Destreza', 'Sabedoria'],
                'hitDie' => 8,
                'saves' => ['Força', 'Destreza'],
                'skillsCount' => 2,
                'weapons' => WeaponProficiency::SIMPLE_MARTIAL_LIGHT,
                'armor' => ArmorTraining::NONE,
                'toolsCount' => 1, // "1 de Artesão ou Instrumento"
            ],
            'Paladino' => [
                'primary' => ['Força', 'Carisma'],
                'hitDie' => 10,
                'saves' => ['Sabedoria', 'Carisma'],
                'skillsCount' => 2,
                'weapons' => WeaponProficiency::SIMPLE_MARTIAL,
                'armor' => ArmorTraining::ALL_SHIELDS,
            ],
        ];

        foreach ($classesData as $className => $data) {
            $classDef = $this->classDefRepository->findOneBy(['name' => $className]);
            
            if (!$classDef) {
                $io->warning("Class '$className' not found in database.");
                continue;
            }

            $io->section("Updating $className...");

            // Hit Die
            $classDef->setHitDie($data['hitDie']);

            // Skills Count
            $classDef->setInitialSkillsCount($data['skillsCount']);

            // Tools Count
            if (isset($data['toolsCount'])) {
                $classDef->setInitialToolsCount($data['toolsCount']);
            } else {
                $classDef->setInitialToolsCount(0);
            }

            // Proficiencies
            $classDef->setWeaponProficiencies($data['weapons']);
            $classDef->setArmorTraining($data['armor']);

            // Primary Abilities
            if (isset($data['primary'][0])) {
                $attr = $this->attributeRepository->findOneBy(['name' => $data['primary'][0]]);
                if ($attr) $classDef->setPrimaryAbility1($attr);
            }
            if (isset($data['primary'][1])) {
                $attr = $this->attributeRepository->findOneBy(['name' => $data['primary'][1]]);
                if ($attr) $classDef->setPrimaryAbility2($attr);
            } else {
                $classDef->setPrimaryAbility2(null);
            }

            // Saving Throws
            if (isset($data['saves'][0])) {
                $attr = $this->attributeRepository->findOneBy(['name' => $data['saves'][0]]);
                if ($attr) $classDef->setSavingThrow1($attr);
            }
            if (isset($data['saves'][1])) {
                $attr = $this->attributeRepository->findOneBy(['name' => $data['saves'][1]]);
                if ($attr) $classDef->setSavingThrow2($attr);
            }

            // Fixed Tools
            if (isset($data['fixedTool'])) {
                $tool = $this->equipmentRepository->findOneBy(['namePt' => $data['fixedTool']]);
                if (!$tool) {
                    $tool = $this->equipmentRepository->findOneBy(['name' => $data['fixedTool']]);
                }
                
                if ($tool) {
                    $classDef->setToolProficiency1($tool);
                } else {
                    $io->warning("Tool '{$data['fixedTool']}' not found for $className.");
                }
            } else {
                $classDef->setToolProficiency1(null);
            }
            $classDef->setToolProficiency2(null); // Assuming no class has 2 *fixed* tools in this simplified update

            $this->entityManager->persist($classDef);
        }

        $this->entityManager->flush();
        $io->success('All classes updated successfully!');

        return Command::SUCCESS;
    }
}
