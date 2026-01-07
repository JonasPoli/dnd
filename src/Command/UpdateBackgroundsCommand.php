<?php

namespace App\Command;

use App\Entity\Attribute;
use App\Entity\Background;
use App\Entity\Equipment;
use App\Entity\Feat;
use App\Entity\Skill;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:backgrounds:update-data',
    description: 'Populates Backgrounds with D&D 2024 data',
)]
class UpdateBackgroundsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $data = [
            [
                'name' => 'Acólito',
                'attributes' => ['Inteligência', 'Sabedoria', 'Carisma'],
                'feat' => 'Iniciado em Magia', // (Clérigo)
                'skills' => ['Intuição', 'Religião'],
                'tool' => 'Suprimentos de Calígrafo',
                'equipment' => ['Suprimentos de Calígrafo', 'Livro', 'Símbolo Sagrado', 'Pergaminho', 'Túnica', 'Algibeira'], // Approximate items
            ],
            [
                'name' => 'Andarilho',
                'attributes' => ['Destreza', 'Sabedoria', 'Carisma'],
                'feat' => 'Sortudo',
                'skills' => ['Furtividade', 'Intuição'],
                'tool' => 'Ferramentas de Ladrão',
                'equipment' => ['Adaga', 'Ferramentas de Ladrão', 'Kit de Jogos', 'Algibeira', 'Roupas de Viagem', 'Saco de Dormir'],
            ],
            [
                'name' => 'Artesão',
                'attributes' => ['Força', 'Destreza', 'Inteligência'],
                'feat' => 'Artifista', // Check mapping
                'skills' => ['Investigação', 'Persuasão'],
                'tool' => 'Ferramentas de Artesão',
                'equipment' => ['Ferramentas de Artesão', 'Algibeira', 'Roupas de Viagem'],
            ],
            [
                'name' => 'Artista',
                'attributes' => ['Força', 'Destreza', 'Carisma'],
                'feat' => 'Músico',
                'skills' => ['Acrobacia', 'Atuação'],
                'tool' => 'Instrumento Musical',
                'equipment' => ['Instrumento Musical', 'Espelho', 'Fantasia', 'Perfume', 'Roupas de Viagem'],
            ],
            [
                'name' => 'Charlatão',
                'attributes' => ['Destreza', 'Constituição', 'Carisma'],
                'feat' => 'Habilidoso',
                'skills' => ['Enganação', 'Prestidigitação'],
                'tool' => 'Kit de Falsificação',
                'equipment' => ['Kit de Falsificação', 'Fantasia', 'Roupas Finas'],
            ],
            [
                'name' => 'Criminoso',
                'attributes' => ['Destreza', 'Constituição', 'Inteligência'],
                'feat' => 'Alerta',
                'skills' => ['Furtividade', 'Prestidigitação'],
                'tool' => 'Ferramentas de Ladrão',
                'equipment' => ['Adaga', 'Ferramentas de Ladrão', 'Algibeira', 'Pé de Cabra', 'Roupas de Viagem'],
            ],
            [
                'name' => 'Eremita',
                'attributes' => ['Constituição', 'Sabedoria', 'Carisma'],
                'feat' => 'Curandeiro',
                'skills' => ['Medicina', 'Religião'],
                'tool' => 'Kit de Herbalismo',
                'equipment' => ['Cajado', 'Kit de Herbalismo', 'Lâmpada', 'Livro', 'Óleo', 'Roupas de Viagem', 'Saco de Dormir'],
            ],
            [
                'name' => 'Escriba',
                'attributes' => ['Destreza', 'Inteligência', 'Sabedoria'],
                'feat' => 'Habilidoso',
                'skills' => ['Investigação', 'Percepção'],
                'tool' => 'Suprimentos de Calígrafo',
                'equipment' => ['Suprimentos de Calígrafo', 'Lâmpada', 'Óleo', 'Pergaminho', 'Roupas Finas'],
            ],
            [
                'name' => 'Fazendeiro',
                'attributes' => ['Força', 'Constituição', 'Sabedoria'],
                'feat' => 'Vigoroso',
                'skills' => ['Lidar com Animais', 'Natureza'],
                'tool' => 'Ferramentas de Carpinteiro',
                'equipment' => ['Foice', 'Ferramentas de Carpinteiro', 'Kit de Curandeiro', 'Balde de Ferro', 'Pá'],
            ],
            [
                'name' => 'Guarda',
                'attributes' => ['Força', 'Inteligência', 'Sabedoria'],
                'feat' => 'Alerta',
                'skills' => ['Atletismo', 'Percepção'],
                'tool' => 'Kit de Jogos',
                'equipment' => ['Lança', 'Besta Leve', 'Virote', 'Kit de Jogos', 'Aljava', 'Grilhões', 'Lanterna Coberta', 'Roupas de Viagem'],
            ],
            [
                'name' => 'Guia',
                'attributes' => ['Destreza', 'Constituição', 'Sabedoria'],
                'feat' => 'Iniciado em Magia', // (Druida)
                'skills' => ['Furtividade', 'Sobrevivência'],
                'tool' => 'Ferramentas de Cartógrafo',
                'equipment' => ['Arco Curto', 'Flecha', 'Ferramentas de Cartógrafo', 'Aljava', 'Roupas de Viagem', 'Saco de Dormir', 'Tenda'],
            ],
            [
                'name' => 'Marinheiro',
                'attributes' => ['Força', 'Destreza', 'Sabedoria'],
                'feat' => 'Valentão de Taverna',
                'skills' => ['Acrobacia', 'Percepção'],
                'tool' => 'Ferramentas de Navegador',
                'equipment' => ['Adaga', 'Ferramentas de Navegador', 'Corda', 'Roupas de Viagem'],
            ],
            [
                'name' => 'Mercador',
                'attributes' => ['Constituição', 'Inteligência', 'Carisma'],
                'feat' => 'Sortudo',
                'skills' => ['Lidar com Animais', 'Persuasão'],
                'tool' => 'Ferramentas de Navegador',
                'equipment' => ['Ferramentas de Navegador', 'Algibeira', 'Roupas de Viagem'],
            ],
            [
                'name' => 'Nobre',
                'attributes' => ['Força', 'Inteligência', 'Carisma'],
                'feat' => 'Habilidoso',
                'skills' => ['História', 'Persuasão'],
                'tool' => 'Kit de Jogos',
                'equipment' => ['Kit de Jogos', 'Perfume', 'Roupas Finas'],
            ],
            [
                'name' => 'Sábio',
                'attributes' => ['Constituição', 'Inteligência', 'Sabedoria'],
                'feat' => 'Iniciado em Magia', // (Mago)
                'skills' => ['Arcanismo', 'História'],
                'tool' => 'Suprimentos de Calígrafo',
                'equipment' => ['Cajado', 'Suprimentos de Calígrafo', 'Livro', 'Pergaminho', 'Túnica'],
            ],
            [
                'name' => 'Soldado',
                'attributes' => ['Força', 'Destreza', 'Constituição'],
                'feat' => 'Atacante Selvagem',
                'skills' => ['Atletismo', 'Intimidação'],
                'tool' => 'Kit de Jogos',
                'equipment' => ['Lança', 'Arco Curto', 'Flecha', 'Kit de Curandeiro', 'Kit de Jogos', 'Aljava', 'Roupas de Viagem'],
            ],
        ];

        $backgroundRepo = $this->entityManager->getRepository(Background::class);
        $attributeRepo = $this->entityManager->getRepository(Attribute::class);
        $featRepo = $this->entityManager->getRepository(Feat::class);
        $skillRepo = $this->entityManager->getRepository(Skill::class);
        $equipmentRepo = $this->entityManager->getRepository(Equipment::class);

        foreach ($data as $row) {
            $io->section("Updating Background: {$row['name']}");

            $bg = $backgroundRepo->findOneBy(['name' => $row['name']]);
            if (!$bg) {
                $bg = new Background();
                $bg->setName($row['name']);
                $this->entityManager->persist($bg);
                $io->text("New background created.");
            }

            // Attributes
            foreach ($row['attributes'] as $i => $attrName) {
                $attr = $attributeRepo->findOneBy(['name' => $attrName]);
                if ($attr) {
                    $setter = "setAttribute" . ($i + 1);
                    $bg->$setter($attr);
                    $io->text("Set Attribute " . ($i+1) . ": $attrName");
                } else {
                    $io->warning("Attribute not found: $attrName");
                }
            }

            // Feat
            $feat = $featRepo->findOneBy(['name' => $row['feat']]);
            if ($feat) {
                $bg->setFeat($feat);
                $io->text("Set Feat: {$row['feat']}");
            } else {
                $io->warning("Feat not found: {$row['feat']}");
            }

            // Skills
            foreach ($row['skills'] as $i => $skillName) {
                $skill = $skillRepo->findOneBy(['name' => $skillName]);
                if ($skill) {
                    $setter = "setSkill" . ($i + 1);
                    $bg->$setter($skill);
                    $io->text("Set Skill " . ($i+1) . ": $skillName");
                } else {
                    $io->warning("Skill not found: $skillName");
                }
            }

            // Tool
            if ($row['tool']) {
                $toolName = $this->resolveEquipmentName($row['tool']);
                $tool = $this->findEquipment($equipmentRepo, $toolName);
                
                if ($tool) {
                    $bg->setToolProficiency($tool);
                    $io->text("Set Tool: {$row['tool']} (Matched: {$tool->getNamePt()})");
                } else {
                    $io->warning("Tool not found: {$row['tool']} (Tried: $toolName)");
                }
            }

            // Equipment
            $bg->getStartingEquipment()->clear();
            foreach ($row['equipment'] as $eqName) {
                 $searchName = $this->resolveEquipmentName($eqName);
                 $eq = $this->findEquipment($equipmentRepo, $searchName);
                 
                 if ($eq) {
                     $bg->addStartingEquipment($eq);
                     $io->text("Added Equipment: $eqName (Matched: {$eq->getNamePt()})");
                 } else {
                     $io->warning("Equipment not found: $eqName (Tried: $searchName)");
                 }
            }
        }

        $this->entityManager->flush();
        $io->success('Backgrounds updated successfully!');

        return Command::SUCCESS;
    }

    private function resolveEquipmentName(string $name): string
    {
        $map = [
            'Suprimentos de Calígrafo' => 'Suprimentos de Caligrafia',
            'Kit de Curandeiro' => 'Kit de Primeiros Socorros',
            'Kit de Jogos' => 'Jogo dos Três Dragões', // Fallback
            'Balde de Ferro' => 'Balde',
            'Pá' => 'Pá', 
            // Add more as discovered
        ];

        // Clean basic quantity
        $clean = preg_replace('/^\d+\s+/', '', $name);
        
        // Remove plural 's' at end if mostly single word?
        // 'Virotes' -> 'Virote'
        if (str_ends_with($clean, 's') && !str_ends_with($clean, 'ss')) {
             // Basic plural handler, not perfect for 'Lápis' etc.
             // $clean = substr($clean, 0, -1);
        }

        return $map[$clean] ?? $clean;
    }

    private function findEquipment($repo, string $name): ?Equipment
    {
        // 1. Exact Match
        $eq = $repo->findOneBy(['namePt' => $name]);
        if ($eq) return $eq;
        
        $eq = $repo->findOneBy(['name' => $name]);
        if ($eq) return $eq;

        // 2. Singular check (Virotes -> Virote)
        if (str_ends_with($name, 's')) {
             $singular = substr($name, 0, -1);
             $eq = $repo->findOneBy(['namePt' => $singular]);
             if ($eq) return $eq;
        }

        // 3. Like Match (Avoid if short word like 'Pá')
        if (mb_strlen($name) > 3) {
            return $repo->createQueryBuilder('e')
                ->where('e.namePt LIKE :name OR e.name LIKE :name')
                ->setParameter('name', '%' . $name . '%')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        }

        return null;
    }
}
