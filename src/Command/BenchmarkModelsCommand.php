<?php

namespace App\Command;

use App\Repository\SpellRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:benchmark:models',
    description: 'Benchmarks different local LLM models for translation speed and quality using a random Spell.',
)]
class BenchmarkModelsCommand extends Command
{
    private string $logDir;

    public function __construct(
        private HttpClientInterface $httpClient,
        private SpellRepository $spellRepository,
        private KernelInterface $kernel,
        private LoggerInterface $logger,
        private Filesystem $filesystem,
        #[Autowire(env: 'OPENAI_API_KEY')]
        private string $openAiApiKey,
        #[Autowire(value: '%env(default::OPENAI_BASE_URL)%')]
        private ?string $openAiBaseUrl = null,
    ) {
        parent::__construct();
        if (empty($this->openAiBaseUrl)) {
            $this->openAiBaseUrl = 'https://api.openai.com/v1';
        }
        $this->logDir = $this->kernel->getProjectDir() . '/var/log/benchmarks';
    }

    protected function configure(): void
    {
        $this
            ->addOption('models', null, InputOption::VALUE_REQUIRED, 'Comma-separated list of models to test', 'llama3,mistral')
            ->addOption('online', null, InputOption::VALUE_NONE, 'Force use of official OpenAI API endpoint')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('online')) {
            $this->openAiBaseUrl = 'https://api.openai.com/v1';
            // Default to GPT models if the user didn't specify otherwise
            if ($input->getOption('models') === 'llama3,mistral') {
                $input->setOption('models', 'gpt-4o,gpt-4o-mini');
            }
        }

        $models = explode(',', $input->getOption('models'));

        $io->title('Benchmarking LLM Models');
        $io->note(sprintf('Using API Base URL: %s', $this->openAiBaseUrl));

        // Create log directory
        if (!$this->filesystem->exists($this->logDir)) {
            $this->filesystem->mkdir($this->logDir);
        }

        // Get a random spell
        $count = $this->spellRepository->count([]);
        if ($count === 0) {
            $io->error('No spells found in the database to test with.');
            return Command::FAILURE;
        }
        $offset = rand(0, $count - 1);
        $spell = $this->spellRepository->findBy([], null, 1, $offset)[0] ?? null;

        if (!$spell) {
            $io->error('Failed to retrieve a random spell.');
            return Command::FAILURE;
        }

        $io->info(sprintf('Testing with random spell: "%s" (ID: %d)', $spell->getName(), $spell->getId()));

        // Prepare the prompt context (Same as TranslateSpellsCommand)
        $contextGuide = <<<GUIDE
# Guia de Tradução de Magias D&D 5e (PT-BR)

Você está traduzindo magias de RPG para Português do Brasil. O objetivo é criar um texto imersivo, fluido e natural para jogadores.

## Campos Importantes:
* **name**: Nome da magia. Use a glanagem oficial (ex: "Fireball" -> "Bola de Fogo", "Magic Missile" -> "Mísseis Mágicos").
* **descriptionMd**: A descrição principal da magia.
* **higherLevelsMd**: "Em Níveis Superiores". Como a magia escala.

## Diretrizes de Estilo:
1. **Parafrasear**: Evite traduções literais robóticas. Reescreva as frases para soarem naturais em português, mantendo o significado exato das regras.
2. **Gramática**:
   - Nunca use "Um criatura". O correto é "Uma criatura".
   - "Animal" em D&D refere-se ao tipo "Besta" ou criaturas irracionais. Se for um ser genérico, use "Criatura".
   - "Manufacured" -> "Fabricado" ou "Manufaturado".
   - "Drawbridge pulley" -> "Roldana de ponte levadiça".

3. **Nomes Oficiais**:
   - **Criaturas**: Use SEMPRE o nome oficial do *Livro dos Monstros* (Monster Manual). Ex: "Ghoul" -> "Carniçal", "Owlbear" -> "Urso-Coruja".
   - **Magias**: Cite magias mencionadas no texto com o nome oficial do *Livro do Jogador* (Player's Handbook).

4. **Terminologia Obrigatória (Glossário Oficial)**:
   - **unwilling creature** -> "Criatura Relutante.
   - **Ability Check** -> "Teste de Habilidade" (NUNCA "Verificação").
   - **Attack Roll** -> "Jogada de Ataque".
   - **Bludgeoning** -> "Concussão" (NUNCA "Contundente").
   - **Caster** -> "Conjurador".
   - **Creature** -> "Criatura" (NUNCA "Monstro" ou "Animal" a menos que especificado).
   - **Damage** -> "Dano".
   - **DC (Difficulty Class)** -> "CD" (Classe de Dificuldade).
   - **Enclosed Space** -> "Espaço confinado".
   - **Fault Line** -> "Linha de Falha".
   - **Ghouls** -> "Carniçais".
   - **GM/DM** -> "Mestre" (NUNCA "Mestre de Jogo").
   - **Higher Levels** -> "Em Níveis Superiores" (Frase padrão: "Quando você conjurar esta magia usando um espaço de magia de Xº nível ou superior...").
   - **Insight Check** -> "Teste de Intuição" (NUNCA "Percepção" ou "Verificação").
   - **Melee Weapon Attack** -> "Ataque com Arma Corpo a Corpo" (ou "Jogada de ataque..." se referir ao teste).
   - **Range** -> "Alcance".
   - **Saving Throw** -> "Teste de Resistência".
   - **Skill** -> "Perícia" (quando envolve proficiência).
   - **Spell Save DC** -> "CD de resistência de magia".
   - **Spell Slot** -> "Espaço de Magia" (NUNCA "Câmara" ou "Slot").
   - **Target** (Substantivo) -> "Alvo".
   - **Target** (Verbo) -> "Escolher como alvo" ou "Conjurar em" (NUNCA "Alvejar" ou "Alvoar").
   - **Willing** -> "Voluntária" (ex: "Criatura voluntária").
   - **Wisdom Saving Throw** -> "Teste de Resistência de Sabedoria".

5. **Frases Comuns e Fluidez**:
   - "For the duration" -> "Durante a duração" ou "Pela duração da magia".
   - "A target must succeed on a..." -> "O alvo deve ser bem-sucedido em um..." (NUNCA "obter sucesso").
   - Evite repetir "criatura" desnecessariamente. Ex: "Grant to the creature" -> "Conceder a ela".
   - Armor Class: "AC equal to 12 + Dex mod" -> "CA igual a 12 + seu modificador de Destreza".

6. **Sistema Métrico**:
   - Converta medidas de D&D (pés/feet) para metros (Padrão: 5 ft = 1,5m).
   - 5 ft -> 1,5 metro.
   - 10 ft -> 3 metros.
   - 30 ft -> 9 metros.
   - 60 ft -> 18 metros.

7. **Formatação**: O texto está em Markdown. Mantenha negritos (**text**) e itálicos (*text*) onde apropriado.

GUIDE;

        // Build source object
        $sourceData = [
            'name' => $spell->getName(),
            'descriptionMd' => $spell->getDescriptionMd(),
            'higherLevelsMd' => $spell->getHigherLevelsMd(),
        ];

        $prompts = [
            [
                'role' => 'system',
                'content' => "You are a specialized expert translator for Dungeons & Dragons (D&D) 5th Edition content, focusing on the Brazilian Portuguese (PT-BR) market.
            Your task is to translate Spell descriptions from English to Portuguese (Brazil), maintaining the specific tone, vocabulary, and phrasing used in official D&D 5e Brazilian publications (like 'Livro do Jogador').

            CRITICAL INSTRUCTIONS:
            - **Context**: This is a database for a Tabletop RPG. The text must be immersive and rule-accurate.
            - **Language**: Use formal but accessible Brazilian Portuguese suitable for fantasy literature.
            - **Tone**: Epic, descriptive, and precise regarding game mechanics.

            GUIDELINES:
            1. **Paraphrase**: Do NOT translate word-for-word. Adapt sentences to sound natural in Portuguese while preserving the exact mechanical meaning.
            2. **Terminology**: strictly adhere to valid D&D 5e PT-BR terms (e.g., 'Saving Throw' -> 'Teste de Resistência', 'Spell Slot' -> 'Espaço de Magia').
            3. **Output Format**: Return ONLY a valid JSON object with keys: `namePt`, `descriptionMdPt`, `higherLevelsMdPt`.

            CONTEXT GUIDE:
            $contextGuide

            Return ONLY the valid JSON object."
            ],
            ['role' => 'user', 'content' => sprintf("Source Spell:\n%s", json_encode($sourceData, JSON_PRETTY_PRINT))],
        ];

        $results = [];
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'spell_id' => $spell->getId(),
            'spell_name' => $spell->getName(),
            'source_data' => $sourceData,
            'models' => []
        ];

        foreach ($models as $model) {
            $model = trim($model);
            $io->section("Testing Model: $model");

            $endpoint = rtrim($this->openAiBaseUrl, '/') . '/chat/completions';

            $startTime = microtime(true);
            $modelLog = ['model' => $model];
            $status = 'UNKNOWN'; // Default status

            try {
                $response = $this->httpClient->request('POST', $endpoint, [
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
                    'timeout' => 180,
                ]);

                $statusCode = $response->getStatusCode();
                $data = $response->toArray(false);

                $endTime = microtime(true);
                $duration = $endTime - $startTime;

                $content = $data['choices'][0]['message']['content'] ?? null;
                $modelLog['raw_response'] = $content;
                $modelLog['duration_seconds'] = $duration;

                if ($statusCode === 200) {
                    // Try to decode normally
                    $json = json_decode($content ?? '', true);

                    // If failed, try to extract from markdown code blocks (common in smaller models)
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $json = $this->extractJson($content);
                    }

                    if ($json) {
                        // Normalize keys if model returned English keys by mistake
                        $namePt = $json['namePt'] ?? $json['name'] ?? null;
                        $descPt = $json['descriptionMdPt'] ?? $json['descriptionMd'] ?? null;

                        if ($namePt && $descPt) {
                            $modelLog['parsed_json'] = $json;
                            $snippet = sprintf("%s: %s", $namePt, substr($descPt, 0, 60) . '...');
                            $status = 'SUCCESS';
                            $io->success("Completed in " . number_format($duration, 2) . "s");
                        } else {
                            $snippet = "Missing keys: " . substr($content, 0, 50);
                            $status = 'INVALID_STRUCTURE';
                            $io->warning("Valid JSON but missing expected keys.");
                        }
                    } else {
                        $snippet = "Invalid JSON: " . substr($content, 0, 50);
                        $status = 'INVALID_JSON';
                        $io->warning("Valid response but invalid JSON format.");
                    }
                } else {
                    $snippet = "HTTP $statusCode";
                    $status = 'ERROR';
                    $io->error("HTTP Error $statusCode");
                    $modelLog['http_status'] = $statusCode;
                }

            } catch (\Exception $e) {
                $endTime = microtime(true);
                $duration = $endTime - $startTime;
                $status = 'EXCEPTION';
                $snippet = $e->getMessage();
                $modelLog['error'] = $e->getMessage();
                $io->error("Exception: " . $e->getMessage());
            }

            $modelLog['status'] = $status;
            $results[] = [
                $model,
                number_format($duration, 2) . 's',
                $status,
                $snippet
            ];
            $logData['models'][] = $modelLog;
        }

        // Save log file
        $filename = sprintf('benchmark_%s.json', date('Y-m-d_H-i-s'));
        $filePath = $this->logDir . '/' . $filename;

        try {
            $this->filesystem->dumpFile($filePath, json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $io->success(sprintf("Full report saved to: %s", $filePath));
        } catch (\Exception $e) {
            $io->error(sprintf("Failed to save report: %s", $e->getMessage()));
        }

        $io->title('Benchmark Results for Spell: ' . $spell->getName());
        $table = new Table($output);
        $table
            ->setHeaders(['Model', 'Time', 'Status', 'Output Snippet'])
            ->setRows($results);
        $table->render();

        return Command::SUCCESS;
    }

    /**
     * Extracts a JSON object from a string, handling cases where it might be wrapped in markdown code blocks.
     *
     * @param string|null $content The string potentially containing JSON.
     * @return array|null The decoded JSON array, or null if no valid JSON is found.
     */
    private function extractJson(?string $content): ?array
    {
        if (empty($content)) {
            return null;
        }

        // Try to decode directly
        $json = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        // If direct decode fails, try to extract from markdown code block
        if (preg_match('/```(?:json)?\s*(.*?)\s*```/s', $content, $matches)) {
            $json = json_decode($matches[1], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        return null;
    }
}
