<?php

namespace App\Command;

use App\Repository\MonsterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TimeoutExceptionInterface;

#[AsCommand(
    name: 'app:translate:monsters',
    description: 'Translates Monster name and description to Portuguese using OpenAI GPT.',
)]
class TranslateMonstersCommand extends Command
{
    public function __construct(
        private MonsterRepository $monsterRepository,
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
            ->addOption('test-connection', null, InputOption::VALUE_NONE, 'Run a quick connection test to OpenAI API')
            ->addOption('batch-size', null, InputOption::VALUE_OPTIONAL, 'Number of items to process per batch', 10)
            ->addOption('timeout', null, InputOption::VALUE_OPTIONAL, 'Request timeout in seconds', 120)
            ->addOption('fields', null, InputOption::VALUE_OPTIONAL, 'Comma‑separated list of JSON fields to translate (empty = all)')
            ->addOption('id', null, InputOption::VALUE_OPTIONAL, 'Filter by specific Monster ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = (int) $input->getOption('limit');
        $model = $input->getOption('model');
        $batchSize = (int) $input->getOption('batch-size');
        $requestTimeout = (int) $input->getOption('timeout');
        $fieldsOption = $input->getOption('fields');
        $fieldsFilter = null;
        if ($fieldsOption) {
            $fieldsFilter = array_map('trim', explode(',', $fieldsOption));
        }

        if (empty($this->openAiApiKey)) {
            $io->error('OPENAI_API_KEY is not set in .env');
            return Command::FAILURE;
        }

        // Test Connection Mode
        if ($input->getOption('test-connection')) {
            $io->section('Testing OpenAI Connection...');
            $startTime = microtime(true);
            try {
                $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->openAiApiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [['role' => 'user', 'content' => 'Ping']],
                        'max_tokens' => 5,
                    ],
                    'timeout' => 10,
                ]);
                $statusCode = $response->getStatusCode();
                $duration = microtime(true) - $startTime;
                if ($statusCode === 200) {
                    $io->success(sprintf('Connection successful! Response time: %.2fs', $duration));
                } else {
                    $io->error(sprintf('API responded with status code %d. Duration: %.2fs', $statusCode, $duration));
                }
            } catch (\Throwable $e) {
                $duration = microtime(true) - $startTime;
                $io->error(sprintf('Connection failed after %.2fs.', $duration));
                $io->note('Exception: ' . get_class($e));
                $io->note('Message: ' . $e->getMessage());
                if (str_contains(strtolower($e->getMessage()), 'timeout') || $e instanceof TimeoutExceptionInterface) {
                    $io->warning('Diagnosis: The request timed out. This suggests a network issue or the API is very slow.');
                } elseif (str_contains(strtolower($e->getMessage()), 'host') || str_contains(strtolower($e->getMessage()), 'resolve')) {
                    $io->warning('Diagnosis: DNS or Network resolution failed. Check your internet connection.');
                }
            }
            return Command::SUCCESS;
        }

        // Determine total items to process
        $idFilter = $input->getOption('id') ? (int) $input->getOption('id') : null;

        $qb = $this->monsterRepository->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.srcJson IS NOT NULL');

        if ($idFilter) {
            $qb->andWhere('m.id = :idFilter')
                ->setParameter('idFilter', $idFilter);
        } else {
            $qb->andWhere('m.srcJsonPt IS NULL');
        }

        $totalItems = (int) $qb->getQuery()->getSingleScalarResult();
        if ($totalItems === 0) {
            $io->success('No monsters found needing translation.');
            return Command::SUCCESS;
        }

        $maxToProcess = (int) $input->getOption('limit');
        // If an ID filter is set, we process 1 item regardless of limit (unless limit is 0, which is weird)
        if ($idFilter) {
            $maxToProcess = 1;
        }

        $io->title(sprintf('Translate Monsters (Found %d, Limit %d%s)', $totalItems, $maxToProcess, $idFilter ? ", ID: $idFilter" : ''));

        $processed = 0;
        $io->progressStart($maxToProcess);

        while ($processed < $maxToProcess) {
            $currentBatchSize = min($batchSize, $maxToProcess - $processed);

            $queryBuilder = $this->monsterRepository->createQueryBuilder('m')
                ->where('m.srcJson IS NOT NULL');

            if ($idFilter) {
                $queryBuilder->andWhere('m.id = :idFilter')
                    ->setParameter('idFilter', $idFilter);
            } else {
                $queryBuilder->andWhere('m.srcJsonPt IS NULL');
            }

            $items = $queryBuilder
                ->orderBy('m.challengeRating', 'ASC')
                ->addOrderBy('m.imgMain', 'DESC')
                ->setMaxResults($currentBatchSize)
                ->getQuery()
                ->getResult();

            if (empty($items)) {
                break; // No more items to process
            }

            foreach ($items as $item) {
                $io->text('Processing: ' . $item->getName());
                $srcJson = $item->getSrcJson();
                if (!$srcJson) {
                    $io->warning('No srcJson found for monster: ' . $item->getName() . '. Skipping.');
                    $io->progressAdvance();
                    continue;
                }
                // Sanitize "False" strings
                $srcJson = $this->sanitizeSourceData($srcJson);
                // Optional field filtering
                if ($fieldsFilter) {
                    $srcJson = array_intersect_key($srcJson, array_flip($fieldsFilter));
                }

                // Check for existing translation
                $existing = $this->monsterRepository->createQueryBuilder('m')
                    ->select('m.srcJsonPt', 'm.id')
                    ->where('m.name = :name')
                    ->andWhere('m.srcJsonPt IS NOT NULL')
                    ->setParameter('name', $item->getName())
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();
                $jsonPt = null;
                if ($existing) {
                    $hasFalso = false;
                    array_walk_recursive($existing['srcJsonPt'], function ($value) use (&$hasFalso) {
                        if (is_string($value) && strtolower($value) === 'falso') {
                            $hasFalso = true;
                        }
                    });

                    if ($hasFalso) {
                        $io->warning(sprintf('Existing translation for "%s" (ID: %d) contains "Falso". Re-translating...', $item->getName(), $item->getId()));
                        $jsonPt = null;
                    } else {
                        $io->info(sprintf('Reusing existing translation for "%s" (ID: %d) from monster ID: %d.', $item->getName(), $item->getId(), $existing['id']));
                        $jsonPt = $existing['srcJsonPt'];
                    }
                }

                if (!$jsonPt) {
                    $fieldGuide = <<<GUIDE
# Guia de Campos: API de Monstros D& D 5e

Esta estrutura de dados contém todas as estatísticas necessárias para rodar um encontro de combate ou interpretar uma criatura no sistema Dungeons & Dragons.
---
## 🛡️ Atributos Principais (Core Stats)
| Campo | Tradução/Significado | Descrição |
| :--- | :--- | :--- |
| **name** | Nome | O nome da criatura (ex: A-mi-kuk). |
| **size** | Tamanho | Categoria física (Tiny, Small, Medium, Large, Huge, Gargantuan). |
| **type** | Tipo | Categoria biológica/mágica (Aberration, Monstrosity, Celestial, etc). |
| **alignment** | Alinhamento | Tendência moral e ética (ex: Chaotic Evil, Lawful Good, Unaligned). |
| **cr / challengeRating** | Nível de Desafio | Indica a força do monstro. Um CR 7 é um desafio apropriado para 4 aventureiros de nível 7. |
| **armorClass (AC)** | Classe de Armadura | O valor necessário para um ataque acertar o monstro. |
| **armorDesc** | Descrição da Armadura | Fonte da defesa (ex: "natural armor"). |
| **hitPoints (HP)** | Pontos de Vida | A saúde da criatura. |
| **hitDice** | Dados de Vida | A fórmula usada para calcular o HP (ex: `10d12+50`). |
| **speed** | Velocidade | Deslocamento em pés (ft). Inclui `walk`, `swim`, `fly` e `burrow`. |
---
## 🧠 Atributos de Habilidade (Ability Scores)
... (rest of guide omitted for brevity)
GUIDE;

                    $prompts = [
                        [
                            'role' => 'system',
                            'content' => "You are a specialized D& D 5e translator and editor for Portuguese (Brazil).\nYour goal is to provide a fluent, natural, and immersive translation, paraphrasing where necessary to convey the true meaning and feel of the text, rather than a literal word‑for‑word translation.\n\nGUIDELINES:\n1. **Paraphrase**: Avoid robotic or Google Translate‑style output. Rephrase sentences to sound like a native RPG book.\n2. **Terminology**: Strict adherence to official D& D 5e PT‑BR terminology.\n3. **Structure**: Keep the JSON structure EXACTLY identical to the source. Do not add or remove keys.\n4. **Values**: Translate all text values. Keep numeric or code values as is unless it's a descriptive text field.\n\nCONTEXT (Field Guide):\n$fieldGuide\n\nReturn ONLY the valid JSON object."
                        ],
                        ['role' => 'user', 'content' => sprintf("Source JSON:\n%s", json_encode($srcJson, JSON_PRETTY_PRINT))],
                    ];

                    $attempts = 0;
                    $maxRetries = 3;
                    $validationError = null;
                    while ($attempts < $maxRetries) {
                        $attempts++;
                        $currentPrompts = $prompts;
                        if ($attempts > 1 && $validationError) {
                            $io->warning(sprintf("Attempt %d/%d failed validation for '%s'. Retrying with feedback...", $attempts - 1, $maxRetries, $item->getName()));
                            $currentPrompts[] = [
                                'role' => 'user',
                                'content' => "Your previous translation failed validation:\n$validationError\n\nCRITICAL: You MUST preserve ALL keys from the source JSON. Do not drop 'attack_bonus', 'damage_dice', or any other fields. Return the COMPLETE object structure."
                            ];
                        }
                        try {
                            $startTime = microtime(true);
                            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                                'headers' => [
                                    'Authorization' => 'Bearer ' . $this->openAiApiKey,
                                    'Content-Type' => 'application/json',
                                ],
                                'json' => [
                                    'model' => $model,
                                    'messages' => $currentPrompts,
                                    'temperature' => 0.2,
                                    'response_format' => ['type' => 'json_object'],
                                ],
                                'timeout' => $requestTimeout,
                            ]);
                            $data = $response->toArray();
                            $content = $data['choices'][0]['message']['content'] ?? null;
                            if ($content) {
                                $decoded = json_decode($content, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    $errorMsg = null;
                                    if ($this->validateStructure($srcJson, $decoded, $errorMsg)) {
                                        $jsonPt = $decoded;
                                        break;
                                    } else {
                                        $validationError = $errorMsg;
                                    }
                                } else {
                                    $validationError = 'Invalid JSON syntax.';
                                }
                            } else {
                                $validationError = 'Empty response from API.';
                            }
                        } catch (\Exception $e) {
                            $duration = microtime(true) - $startTime;
                            $isTimeout = str_contains(strtolower($e->getMessage()), 'timeout') || $e instanceof TimeoutExceptionInterface;
                            $validationError = sprintf(
                                "Request error (%s) after %.2fs: %s",
                                $isTimeout ? 'TIMEOUT' : 'EXCEPTION',
                                $duration,
                                $e->getMessage()
                            );
                            if ($isTimeout) {
                                $io->warning("Request timed out after {$duration}s. (Attempt $attempts)");
                            }
                        }
                        usleep(500000);
                    }
                }

                if ($jsonPt) {
                    $item->setSrcJsonPt($jsonPt);

                    // Set source description
                    $item->setDescriptionMd($srcJson['description'] ?? null);

                    // Set translated basic fields
                    $item->setNamePt($jsonPt['name'] ?? null);
                    $item->setSizePt($jsonPt['size'] ?? null);
                    $item->setTypePt($jsonPt['type'] ?? null);
                    $item->setSubtypePt($jsonPt['subtype'] ?? null);
                    $item->setGroupPt($jsonPt['group'] ?? null);
                    $item->setAlignmentPt($jsonPt['alignment'] ?? null);
                    $item->setArmorDescPt($jsonPt['armorDesc'] ?? null);

                    // Set translated description
                    $item->setDescriptionMdPt($jsonPt['description'] ?? null);

                    // Update additional text fields from translated JSON
                    if (isset($jsonPt['senses'])) {
                        $item->setSenses($jsonPt['senses']);
                    }
                    if (isset($jsonPt['languages'])) {
                        $item->setLanguages($jsonPt['languages']);
                    }
                    if (isset($jsonPt['damageImmunities'])) {
                        $item->setDamageImmunities($jsonPt['damageImmunities']);
                    }
                    if (isset($jsonPt['damageResistances'])) {
                        $item->setDamageResistances($jsonPt['damageResistances']);
                    }
                    if (isset($jsonPt['damageVulnerabilities'])) {
                        $item->setDamageVulnerabilities($jsonPt['damageVulnerabilities']);
                    }
                    if (isset($jsonPt['conditionImmunities'])) {
                        $item->setConditionImmunities($jsonPt['conditionImmunities']);
                    }
                    if (isset($jsonPt['legendaryDesc'])) {
                        $item->setLegendaryDesc($jsonPt['legendaryDesc']);
                    }

                    // Update JSON array fields from translated JSON
                    if (isset($jsonPt['speed'])) {
                        // Converte velocidade de pés para metros (ft -> m)
                        $speedMetros = $this->convertSpeedToMeters($jsonPt['speed']);
                        $item->setSpeedJson($speedMetros);
                    }
                    if (isset($jsonPt['skills'])) {
                        $item->setSkillsJson($jsonPt['skills']);
                    }
                    if (isset($jsonPt['specialAbilities'])) {
                        $item->setSpecialAbilitiesJson($jsonPt['specialAbilities']);
                    }
                    if (isset($jsonPt['actions'])) {
                        $item->setActionsJson($jsonPt['actions']);
                    }
                    if (isset($jsonPt['bonusActions'])) {
                        $item->setBonusActionsJson($jsonPt['bonusActions']);
                    }
                    if (isset($jsonPt['reactions'])) {
                        $item->setReactionsJson($jsonPt['reactions']);
                    }
                    if (isset($jsonPt['legendaryActions'])) {
                        $item->setLegendaryActionsJson($jsonPt['legendaryActions']);
                    }
                    if (isset($jsonPt['spellList'])) {
                        $item->setSpellList($jsonPt['spellList']);
                    }
                    if (isset($jsonPt['environments'])) {
                        $item->setEnvironments($jsonPt['environments']);
                    }

                    $this->entityManager->flush();
                    if ($maxToProcess === 1) {
                        $io->note(sprintf(
                            "ID: %d\nName: %s\nTranslation Preview:\n%s",
                            $item->getId(),
                            $item->getName(),
                            substr(json_encode($jsonPt, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 0, 500) . '...'
                        ));
                    }
                } else {
                    $io->error(sprintf("Failed to translate monster '%s' after %d attempts. Last error: %s", $item->getName(), $maxRetries, $validationError));
                }
                $io->progressAdvance();
                usleep(250000);
            }
            // Clear EntityManager to free memory between batches
            $this->entityManager->clear();
            $processed += $currentBatchSize;
        }

        $io->progressFinish();
        $io->success('Translation of srcJson to srcJsonPt complete.');
        return Command::SUCCESS;
    }

    private function validateStructure(array $source, array $dest, &$errorMsg = null, string $contextKey = ''): bool
    {
        $sourceKeys = array_keys($source);
        $destKeys = array_keys($dest);
        sort($sourceKeys);
        sort($destKeys);
        if ($sourceKeys !== $destKeys) {
            if (in_array($contextKey, ['skills', 'senses'], true)) {
                if (count($source) !== count($dest)) {
                    $errorMsg = "Count mismatch in '$contextKey' (Source: " . count($source) . ", Dest: " . count($dest) . ")";
                    return false;
                }
                return true;
            }
            $missingInDest = array_diff($sourceKeys, $destKeys);
            $missingInSource = array_diff($destKeys, $sourceKeys);
            $errorMsg = 'Key mismatch.';
            if (!empty($missingInDest)) {
                $errorMsg .= ' Missing in Dest: ' . implode(', ', $missingInDest);
            }
            if (!empty($missingInSource)) {
                $errorMsg .= ' Extra in Dest: ' . implode(', ', $missingInSource);
            }
            return false;
        }
        foreach ($source as $key => $value) {
            if (is_array($value) && is_array($dest[$key])) {
                $isSourceAssoc = array_keys($value) !== range(0, count($value) - 1);
                $isDestAssoc = array_keys($dest[$key]) !== range(0, count($dest[$key]) - 1);
                if ($isSourceAssoc !== $isDestAssoc) {
                    $errorMsg = "Array type mismatch for key '$key' (Associative vs List)";
                    return false;
                }
                if ($isSourceAssoc) {
                    if (!$this->validateStructure($value, $dest[$key], $subError, $key)) {
                        $errorMsg = "In key '$key': $subError";
                        return false;
                    }
                } else {
                    if (count($value) !== count($dest[$key])) {
                        $errorMsg = "List length mismatch for key '$key'. Source: " . count($value) . ", Dest: " . count($dest[$key]);
                        return false;
                    }
                    foreach ($value as $idx => $item) {
                        if (is_array($item) && isset($dest[$key][$idx]) && is_array($dest[$key][$idx])) {
                            if (!$this->validateStructure($item, $dest[$key][$idx], $subError, $key)) {
                                $errorMsg = "In key '$key'[$idx]: $subError";
                                return false;
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    private function sanitizeSourceData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sanitizeSourceData($value);
            } elseif (is_string($value) && strtolower($value) === 'false') {
                $data[$key] = null;
            }
        }
        return $data;
    }

    private function convertSpeedToMeters(array $speed): array
    {
        $converted = [];
        foreach ($speed as $key => $value) {
            // Se for booleano (ex: "hover": true), mantém
            if (is_bool($value)) {
                $converted[$key] = $value;
                continue;
            }

            // Remove 'ft.' ou outros textos, tenta pegar o número
            // Ex: "30 ft." -> 30
            $number = (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

            if ($number > 0) {
                // 1 ft = 0.3048 metros. D&D 5e usa aproximação 5ft = 1.5m (fator 0.3)
                $metros = $number * 0.3;

                // Formata: se for inteiro (ex: 9.0), exibe 9. Se for quebrado (ex: 1.5), exibe 1.5
                $formatted = (float) $metros; // Cast remove zeros extras de decimal

                // Se a chave for "walk", pode ser salvo apenas como número ou string com 'm'
                // O pedido diz "convertendo de pés para metros".
                // Vou manter o padrão do JSON original (valores numéricos ou strings numéricas) mas convertidos.
                $converted[$key] = $formatted;
            } else {
                // Se não conseguiu converter (ex: texto sem número), mantém original
                $converted[$key] = $value;
            }
        }
        return $converted;
    }
}
