<?php

namespace App\Command;

use App\Entity\ClassDef;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed:class-help',
    description: 'Seeds character creation help text for classes',
)]
class SeedClassHelpCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $repo = $this->entityManager->getRepository(ClassDef::class);

        $helpTexts = [
            'Bárbaro' => "## Traços Básicos de Bárbaro\nAtributo Primário: Força\nDado de Ponto de Vida: D12 por nível de Bárbaro\nProficiência em Salvaguardas: Força e Constituição\nProficiência em Perícias: Escolha 2: Atletismo, Intimidação, Lidar com Animais, Natureza, Percepção ou Sobrevivência\nProficiências com Armas Treinamento com Armadura Armas Simples e Marciais\nArmaduras Leve e Média e Escudos\nEquipamento Inicial: Escolha A ou B: (A) 4 Machadinhas, Machado Grande, Kit de Aventureiro e 15 PO; ou (B) 75 PO",
            'Bardo' => "## Traços Básicos de Bardo\nAtributo Primário: Carisma\nDado de Vida: D8 por nível de Bardo\nProficiência em Salvaguardas: Destreza e Carisma\nProficiência sem Perícias: Escolha quaisquer 3 perícias\nProficiências com Armas: Armas Simples\nProficiências com Ferramentas: Escolha 3 instrumentos musicais\nTreinamento com Armadura: Armadura Leve\nEquipamento Inicial: Escolha A ou B: (A) Armadura de Couro, 2 Adagas, Instrumento Musical à sua escolha, Kit de Artista e 19 PO; ou (B) 90 PO",
            'Bruxo' => "## Traços Básicos de Bruxo\nAtributo Primário: Carisma\nDado de Vida: D8 por nível de Bruxo\nProficiência em Salvaguardas: Sabedoria e Carisma\nProficiências em Perícias: Escolha 2: Arcanismo, Enganação, História, Intimidação, Investigação, Natureza ou Religião.\nProficiências com Armas: Armas Simples\nTreinamento com Armaduras: Armadura Leve\nEquipamento Inicial: Escolha A ou B: (A) Armadura de Couro, Foice, 2 Adagas, Foco Arcano (orbe), Livro (conhecimento oculto), Kit de Erudito e 15 PO; ou (B) 100 PO",
            'Clérigo' => "## Traços Básicos do Clérigo\nAtributo Primário: Sabedoria\nDado de Ponto de Vida: D8 por nível de Clérigo\nProficiências em Salvaguarda: Sabedoria e Carisma\nProficiências em Perícias: Escolha 2: História, Intuição, Medicina, Persuasão ou Religião.\nProficiências com Armas: Armas Simples\nTreinamento com Armadura: Armaduras Leve e Média e Escudos\nEquipamento Inicial: Escolha A ou B: Cota de Malha Parcial, Escudo, Maça, Símbolo Sagrado, Kit de Sacerdote e 7 PO; ou (B) 110 PO",
            'Druida' => "## Traços Básicos de Druida\nAtributo Primário: Inteligência\nDado de Ponto de Vida: D8 por nível de Druida\nProficiência em Salvaguarda: Inteligência e Sabedoria\nProficiência em Perícias: Escolha 2: Arcanismo, Lidar com Animais, Intuição, Medicina, Natureza, Percepção, Religião ou Sobrevivência.\nProficiências com Armas: Armas Simples\nProficiências com Kit de Herbalismo\nFerramentas\nTreinamento com Armadura: Armadura Leve e Escudos\nEquipamento Inicial: Escolha A ou B: (A) Armadura de Couro, Escudo, Foice, Foco Druídico (Cajado), Kit de Explorador, Kit de Herbalismo, 9 PO; ou (B) 50 PO",
            'Feiticeiro' => "## Traços Básicos de Feiticeiro\nAtributo Primário: Carisma\nDado de Ponto de Vida: D6 por nível de Feiticeiro\nProficiência em Salvaguardas: Constituição e Carisma\nProficiências em Perícias: Escolha 2: Arcanismo, Enganação, Intimidação, Intuição, Persuasão ou Religião.\nProficiências com Armas: Armas Simples\nEquipamento Inicial: Escolha A ou B: (A) Lança, 2 Adagas, Foco Arcano (cristal), Kit de Explorador de Masmorras e 28 PO; ou (B) 50 PO",
            'Patrulheiro' => "## Traços Básicos de Guardião\nAtributo Primário: Destreza e Sabedoria\nDado de Ponto de Vida: D10 por nível de Guardião\nProficiência em Salvaguarda: Força e Destreza\nProficiências em Perícias: Escolha 3: Atletismo, Furtividade, Intuição, Investigação, Lidar com Animais, Natureza, Percepção ou Sobrevivência.\nProficiências com Armas: Treinamento com Armadura Armas Simples e Marciais\nProficiências com Armaduras: Armaduras Leves, Médias e Escudos\nEquipamento Inicial: Escolha A ou B: (A) armadura de Couro Batido, Cimitarra, Espada Curta, Arco Longo, 20 Flechas, Aljava, Foco Druídico (ramo de visco), Kit de Aventureiro e 7 PO; ou (B) 150 PO", // Ranger is often translated as Patrulheiro or Guardião. Using Patrulheiro as key guess, or searching by slug.
            'Guerreiro' => "## Traços Básicos de Guerreiro\nAtributo Primário: Força ou Destreza\nDado de Ponto de Vida: D10 por nível de Guerreiro\nProficiência em Salvaguardas: Força e Constituição\nProficiências em Perícias: Escolha 2: Acrobacia, Atletismo, História, Intimidação, Intuição, Lidar com Animais, Percepção, Persuasão ou Sobrevivência.\nProficiências com Armas: Treinamento com Armadura: armas Simples e Marciais\nProficiências com Armaduras: Armaduras Leves, Médias e Pesadas e Escudos\nEquipamento Inicial: Escolha A, B ou C: (A) Cota de Malha, Espada Grande, Mangual, 8 Azagaias, Kit de Explorador de Masmorras e 4 PO; (B) Armadura de Couro Batido, Cimitarra, Espada Curta, Arco Longo, 20 Flechas, Aljava, Kit de Explorador de Masmorras e 11 PO; ou (C) 155 PO",
            'Ladino' => "## Traços Básicos de Ladino\nAtributo Primário: Destreza\nDado de Vida: D8 por nível de Ladino\nProficiência em Salvaguarda: Destreza e Inteligência\nProficiência em Perícias: Escolha 4: Acrobacia, Atletismo, Enganação, Furtividade, Intimidação, Intuição, Investigação, Percepção, Persuasão ou Prestidigitação.\nProficiências com Armas Armas Simples e Armas Marciais que tem a propriedade Acuidade ou Leve\nFerramentas de Ladrão\nProficiências com Ferramentas\nTreinamento com Armadura Armadura Leve\nEquipamento Inicial: Escolha A ou B: (A) Armadura de Couro, 2 Adagas, Espada Curta, Arco Curto, 20 Flechas, Aljava, Ferramentas de Ladrão, Kit de Assaltante e 8 PO; ou (B) 100 PO",
            'Mago' => "## Traços Básicos de Mago\nAtributo Primário: Inteligência\nDado de Ponto de Vida: D6 por nível de Mago\nProficiência em Salvaguarda: Inteligência e Sabedoria\nProficiências em Perícias: Escolha 2: Arcanismo, História, Intuição, Investigação, Medicina, Natureza ou Religião.\nProficiências com Armas Armas Simples\nEquipamento Inicial Escolha A ou B: (A) 2 Adagas, Foco Arcano (Cajado), Kit de Erudito, Livro de Magias, Túnica e 5 PO; ou (B) 55 PO",
            'Monge' => "## Traços Básicos de Monge\nAtributo Primário: Destreza e Sabedoria\nDado de Vida: D8 por nível de Monge\nProficiência em Salvaguardas: Força e Destreza\nProficiência em Perícias: Escolha 2: Acrobacia, Atletismo, Furtividade, História, Intuição ou Religião.\nProficiências com Armas Armas Simples e Marciais que têm a propriedade Leve\nProficiências com Ferramentas Escolha um tipo de Ferramentas de Artesão ou Instrumento Musical (veja o capítulo 6)\nTreinamento com Armadura nenhum\nEquipamento Inicial Escolha A ou B: (A) Lança, 5 Adagas, Ferramentas de Artesão ou Instrumento Musical escolhido para a proficiência com ferramenta acima, Kit de Aventureiro e 11 PO; ou (B) 50 PO",
            'Paladino' => "## Traços Básicos de Paladino\nAtributo Primário: Força e Carisma\nDado de Ponto de Vida: D10 por nível de Paladino\nProficiência em Salvaguarda: Sabedoria e Carisma\nProficiência em Perícias: Escolha 2: Atletismo, Intimidação, Intuição, Medicina, Persuasão ou Religião.\nProficiências com Armas: Treinamento com Armadura: Armas Simples e Marciais\nArmaduras Leves, Médias, Pesadas e Escudos\nEquipamento Inicial: Escolha A ou B: (A) Cota de Malha, Escudo, Espada Longa, 6 Azagaias, Símbolo Sagrado, Kit de Sacerdote e 9 PO; ou (B) 150 PO",
        ];

        // Also map probable slugs just in case
        $slugMap = [
            'barbarian' => 'Bárbaro',
            'bard' => 'Bardo',
            'warlock' => 'Bruxo',
            'cleric' => 'Clérigo',
            'druid' => 'Druida',
            'sorcerer' => 'Feiticeiro',
            'ranger' => 'Patrulheiro',
            'fighter' => 'Guerreiro',
            'rogue' => 'Ladino',
            'wizard' => 'Mago',
            'monk' => 'Monge',
            'paladin' => 'Paladino',
        ];

        foreach ($slugMap as $slug => $key) {
             if (isset($helpTexts[$key])) {
                 $helpTexts[$slug] = $helpTexts[$key];
             }
        }

        $allClasses = $repo->findAll();
        $count = 0;

        foreach ($allClasses as $classDef) {
            $name = $classDef->getName();
            $slug = $classDef->getRuleSlug();
            
            $text = null;
            if (isset($helpTexts[$name])) {
                $text = $helpTexts[$name];
            } elseif (isset($helpTexts[$slug])) {
                $text = $helpTexts[$slug];
            } elseif ($name === 'Guardião') { // Handle Guardião explicitly if mapped to Patrulheiro or Ranger
                  $text = $helpTexts['Patrulheiro'];
            }

            if ($text) {
                $classDef->setCharacterCreationHelp($text);
                $this->entityManager->persist($classDef);
                $count++;
            }
        }

        $this->entityManager->flush();
        $io->success("Updated help text for $count classes.");

        return Command::SUCCESS;
    }
}
