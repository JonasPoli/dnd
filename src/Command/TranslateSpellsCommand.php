<?php

namespace App\Command;

use App\Repository\SpellRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:translate:spells',
    description: 'Translates Spell name and description to Portuguese using OpenAI GPT (Paraphrased).',
)]
class TranslateSpellsCommand extends Command
{
    public function __construct(
        private SpellRepository $spellRepository,
        private EntityManagerInterface $entityManager,
        private HttpClientInterface $httpClient,
        #[Autowire(env: 'OPENAI_API_KEY')]
        private string $openAiApiKey,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Number of items to process', 1)
            ->addOption('model', null, InputOption::VALUE_OPTIONAL, 'OpenAI model to use', 'gpt-4o-mini')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = (int) $input->getOption('limit');
        $model = $input->getOption('model');

        if (empty($this->openAiApiKey)) {
            $io->error('OPENAI_API_KEY is not set in .env');
            return Command::FAILURE;
        }

        // Find spells where namePt is null
        $items = $this->spellRepository->createQueryBuilder('s')
            ->where('s.namePt IS NULL')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        if (empty($items)) {
            $io->success('No spells found needing translation (namePt is empty).');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Found %d spells to translate using model %s.', count($items), $model));
        $io->progressStart(count($items));

        $contextGuide = <<<GUIDE
# Guia de Tradução de Magias D&D 5e (PT-BR)

Você está traduzindo magias de RPG para Português do Brasil. O objetivo é criar um texto imersivo, fluido e natural para jogadores.

## Campos Importantes:
* **name**: Nome da magia. Use a glanagem oficial (ex: "Fireball" -> "Bola de Fogo", "Magic Missile" -> "Mísseis Mágicos").
* **descriptionMd**: A descrição principal da magia.
* **higherLevelsMd**: "Em Níveis Superiores". Como a magia escala.

## Diretrizes de Estilo:
1. **Parafrasear**: Evite traduções literais robóticas. Reescreva as frases para soarem naturais em português, mantendo o significado exato das regras.
2. **Terminologia**:
   - Attack Roll -> Jogada de Ataque
   - Saving Throw -> Teste de Resistência
   - Spell Slot -> Espaço de Magia
   - Caster -> Conjurador
   - Target -> Alvo
   - Range -> Alcance
   - Damage -> Dano
3. **Formatação**: O texto está em Markdown. Mantenha negritos (**text**) e itálicos (*text*) onde apropriado para destacar termos de regra.

GUIDE;

        foreach ($items as $item) {
            $io->text('Processing: ' . $item->getName());
            
            // Build source object for translation
            $sourceData = [
                'name' => $item->getName(),
                'descriptionMd' => $item->getDescriptionMd(),
                'higherLevelsMd' => $item->getHigherLevelsMd(),
            ];

            $prompts = [
                ['role' => 'system', 'content' => "You are a specialized D&D 5e translator and editor for Portuguese (Brazil).
                Your goal is to provide a fluent, natural, and immersive translation for Spells.

                GUIDELINES:
                1. **Paraphrase**: Avoid robotic output. Rephrase sentences to sound like a natural RPG book (Livro do Jogador).
                2. **Terminology**: Strict adherence to official D&D 5e PT-BR terminology.
                3. **Structure**: Return a JSON object with keys: `namePt`, `descriptionMdPt`, `higherLevelsMdPt`.
                4. **Values**: If a source field is null/empty, allow the translation to be null or empty string.

                CONTEXT:
                $contextGuide

                Return ONLY the valid JSON object."],
                ['role' => 'user', 'content' => sprintf("Source Spell:\n%s", json_encode($sourceData, JSON_PRETTY_PRINT))],
            ];

            $jsonPt = null;
            $attempts = 0;
            $maxRetries = 3;
            $validationError = null;

            while ($attempts < $maxRetries) {
                $attempts++;
                
                try {
                    $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->openAiApiKey,
                            'Content-Type' => 'application/json',
                        ],
                        'json' => [
                            'model' => $model,
                            'messages' => $prompts,
                            'temperature' => 0.3,
                            'response_format' => ['type' => 'json_object'],
                        ],
                        'timeout' => 120,
                    ]);

                    $data = $response->toArray();
                    $content = $data['choices'][0]['message']['content'] ?? null;
                    
                    if ($content) {
                        $decoded = json_decode($content, true);

                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            // Simple validation
                            if (isset($decoded['namePt']) || isset($decoded['descriptionMdPt'])) {
                                $jsonPt = $decoded;
                                break;
                            } else {
                                $validationError = "Missing required keys namePt or descriptionMdPt";
                            }
                        } else {
                            $validationError = "Invalid JSON syntax.";
                        }
                    } else {
                        $validationError = "Empty response from API.";
                    }

                } catch (\Exception $e) {
                    $validationError = "Request error: " . $e->getMessage();
                }
                
                usleep(500000); // 0.5s pause
            }

            if ($jsonPt) {
                $item->setNamePt($jsonPt['namePt'] ?? $item->getName());
                $item->setDescriptionMdPt($jsonPt['descriptionMdPt'] ?? null);
                $item->setHigherLevelsMdPt($jsonPt['higherLevelsMdPt'] ?? null);

                $this->entityManager->flush();

                if (count($items) === 1) {
                    $io->note(sprintf(
                        "Translated: %s -> %s",
                        $item->getName(),
                        $item->getNamePt()
                    ));
                }
            } else {
                $io->error(sprintf("Failed to translate spell '%s'. Last error: %s", $item->getName(), $validationError));
            }

            $io->progressAdvance();
            usleep(250000); 
        }

        $io->progressFinish();
        $io->success('Spell translation complete.');

        return Command::SUCCESS;
    }
}
