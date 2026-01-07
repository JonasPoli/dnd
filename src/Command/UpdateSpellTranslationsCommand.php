<?php

namespace App\Command;

use App\Entity\Spell;
use App\Repository\SpellRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:spells:update-translations',
    description: 'Updates spell entities with Portuguese translations from hardcoded data.',
)]
class UpdateSpellTranslationsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SpellRepository $spellRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Updating Spell Translations (PT-BR)');

        $translations = $this->getTranslations();
        $updatedCount = 0;

        foreach ($translations as $englishName => $data) {
            $spells = $this->spellRepository->findBy(['name' => $englishName]);

            if (empty($spells)) {
                $io->warning(sprintf('Spell not found: "%s"', $englishName));
                continue;
            }

            foreach ($spells as $spell) {
                $spell->setNamePt($data['name']);
                $spell->setDescriptionMdPt($data['description']);
                
                if (isset($data['higher_levels'])) {
                    $spell->setHigherLevelsMdPt($data['higher_levels']);
                }

                $io->text(sprintf('Updated "%s" -> "%s" (%s)', $englishName, $data['name'], $spell->getRuleSlug()));
                $updatedCount++;
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf('Finished. Updated %d spell records.', $updatedCount));

        return Command::SUCCESS;
    }

    private function getTranslations(): array
    {
        return [
            'Calm Emotions' => [
                'name' => 'Acalmar Emoções',
                'description' => 'Criaturas humanoides em uma esfera de 6m de raio devem passar em salvaguarda de Carisma ou ter efeitos de medo/encantamento suprimidos, ou tornarem-se indiferentes a hostilidades.',
                'higher_levels' => null,
            ],
            'Spare the Dying' => [
                'name' => 'Acudir os Moribundos',
                'description' => 'Estabiliza uma criatura com 0 pontos de vida. O alcance aumenta nos níveis 5, 11 e 17.',
                'higher_levels' => null,
            ],
            'Alarm' => [
                'name' => 'Alarme',
                'description' => 'Protege uma área contra intrusos, emitindo um alerta mental (se estiver a 1,5km) ou sonoro (audível a 18m).',
                'higher_levels' => null,
            ],
            'Planar Ally' => [
                'name' => 'Aliado Extraplanar',
                'description' => 'Roga pela ajuda de uma entidade que envia um Celestial, Elemental ou Ínfero. Requer pagamento em ouro ou serviços conforme a duração e perigo da tarefa.',
                'higher_levels' => null,
            ],
            'Swift Quiver' => [
                'name' => 'Aljava Veloz',
                'description' => 'Permite realizar dois ataques adicionais com munição mágica infinita usando ação bônus em cada turno.',
                'higher_levels' => null,
            ],
            'Alter Self' => [
                'name' => 'Alterar-se',
                'description' => 'Escolha entre Adaptação Aquática, Armas Naturais ou Mudar Aparência. É possível trocar de opção usando uma ação.',
                'higher_levels' => null,
            ],
        ];
    }
}
