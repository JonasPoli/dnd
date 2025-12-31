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

        // Find monsters where srcJsonPt is null but srcJson is not null.
        $items = $this->monsterRepository->createQueryBuilder('m')
            ->where('m.srcJsonPt IS NULL')
            ->andWhere('m.srcJson IS NOT NULL')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        if (empty($items)) {
            $io->success('No monsters found needing srcJson translation (srcJsonPt is empty).');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Found %d monsters to translate using model %s.', count($items), $model));
        $io->progressStart(count($items));

        $fieldGuide = <<<GUIDE
# Guia de Campos: API de Monstros D&D 5e

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
| **speed** | Velocidade | Deslocamento em pés (ft). Inclui `walk` (andar), `swim` (nadar), `fly` (voar) e `burrow` (escavar). |

---

## 🧠 Atributos de Habilidade (Ability Scores)

Estes são os seis valores fundamentais que definem o que a criatura é capaz de fazer:

* **Strength (STR):** Força física e atletismo.
* **Dexterity (DEX):** Agilidade, reflexos e equilíbrio.
* **Constitution (CON):** Resistência, saúde e vigor.
* **Intelligence (INT):** Memória, raciocínio e conhecimento.
* **Wisdom (WIS):** Percepção, intuição e sobrevivência.
* **Charisma (CHA):** Força de personalidade e magnetismo social.

> **Saves (ex: wisdomSave):** São os bônus de "Testes de Resistência". Se estiver `null`, o monstro usa apenas o modificador padrão do atributo.

---

## 🔍 Percepção e Perícias

* **senses:** Sentidos especiais como *darkvision* (visão no escuro), *truesight* (visão verdadeira) ou *tremorsense* (sentir vibrações no chão).
* **passivePerception:** O valor de percepção "automático" do monstro quando não está procurando ativamente.
* **skills:** Perícias onde o monstro tem treinamento (ex: `stealth` para furtividade, `athletics` para atletismo).
* **languages:** Idiomas que a criatura fala ou entende.

---

## ⚔️ Combate e Ações

* **actions:** Lista de ataques ou habilidades ativas que o monstro usa no turno dele.
    * *Multiattack:* Capacidade de atacar mais de uma vez por turno.
    * *Attack Bonus:* O valor somado ao dado (d20) para ver se o ataque acerta.
    * *Damage Dice:* O dano causado (ex: `2d6 + 5`).
* **specialAbilities:** Habilidades passivas ou características únicas (ex: *Amphibious* para respirar na água).
* **reactions:** Ações que o monstro pode fazer fora do seu turno em resposta a algo.
* **legendaryActions:** Ações especiais que criaturas muito poderosas fazem ao final do turno dos jogadores.

---

## 🌡️ Resistências e Vulnerabilidades

* **damageImmunities:** Tipos de dano que **não afetam** o monstro (ex: `cold` - frio).
* **damageResistances:** Tipos de dano que o monstro recebe apenas pela **metade**.
* **damageVulnerabilities:** Tipos de dano que o monstro recebe em **dobro**.
* **conditionImmunities:** Estados que o monstro não pode sofrer (ex: `blinded` - cego, `charmed` - enfeitiçado).

---

## 📖 Informações Adicionais

* **description:** Texto narrativo (Lore) que descreve a aparência e o comportamento da criatura.
* **pageNo:** Número da página no livro de origem.
* **environments:** Áreas onde o monstro é comumente encontrado (florestas, cavernas, etc).
* **imgMain:** Link para a imagem da criatura (se disponível).
GUIDE;

        foreach ($items as $item) {
            $io->text('Processing: ' . $item->getName());
            
            $srcJson = $item->getSrcJson();
            if (!$srcJson) {
                $io->warning('No srcJson found for monster: ' . $item->getName() . '. Skipping.');
                continue;
            }

            // Check if another monster with the same name already has a translation
            $existingTranslation = $this->monsterRepository->createQueryBuilder('m')
                ->select('m.id')
                ->where('m.name = :name')
                ->andWhere('m.srcJsonPt IS NOT NULL')
                ->setParameter('name', $item->getName())
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if ($existingTranslation) {
                $io->warning(sprintf('Skipping "%s" (ID: %d) because a translation already exists for this name (ID: %d).', $item->getName(), $item->getId(), $existingTranslation['id']));
                $io->progressAdvance();
                continue;
            }

            $prompts = [
                ['role' => 'system', 'content' => "You are a professional translator for Dungeons & Dragons (D&D) 5e. 
                Your task is to translate the JSON content from English to Portuguese (Brazil).
                
                RULES:
                1. Keep the JSON structure EXACTLY the same. Do not change keys.
                2. Translate only the VALUES of the fields.
                3. Use standard D&D 5e terminology (Pt-BR).
                4. Refer to the Field Guide below for context on specific fields.
                
                $fieldGuide

                Return ONLY the valid JSON object."],
                ['role' => 'user', 'content' => sprintf("Source JSON:\n%s", json_encode($srcJson, JSON_PRETTY_PRINT))],
            ];

            $jsonPt = null;
            $attempts = 0;
            $maxRetries = 3;
            $validationError = null;

            while ($attempts < $maxRetries) {
                $attempts++;
                
                // If retrying, append formatting instruction
                $currentPrompts = $prompts;
                if ($attempts > 1 && $validationError) {
                    $io->warning(sprintf("Attempt %d/%d failed validation for '%s'. Retrying with feedback...", $attempts - 1, $maxRetries, $item->getName()));
                    $currentPrompts[] = [
                        'role' => 'user', 
                        'content' => "Your previous translation failed validation:\n$validationError\n\nCRITICAL: You MUST preserve ALL keys from the source JSON. Do not drop 'attack_bonus', 'damage_dice', or any other fields. Return the COMPLETE object structure."
                    ];
                }

                try {
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
                        'timeout' => 120,
                    ]);

                    $data = $response->toArray();
                    $content = $data['choices'][0]['message']['content'] ?? null;
                    
                    if ($content) {
                        $decoded = json_decode($content, true);

                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            // Validate structure
                            $errorMsg = null;
                            if ($this->validateStructure($srcJson, $decoded, $errorMsg)) {
                                // Success!
                                $jsonPt = $decoded;
                                break; // Exit retry loop
                            } else {
                                $validationError = $errorMsg;
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
                
                usleep(500000); // Wait 0.5s before retry
            }

            if ($jsonPt) {
                $item->setSrcJsonPt($jsonPt);
                $this->entityManager->flush();

                if (count($items) === 1) {
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

        $io->progressFinish();
        $io->success('Translation of srcJson to srcJsonPt complete.');

        return Command::SUCCESS;
    }

    private function validateStructure(array $source, array $dest, &$errorMsg = null): bool
    {
        $sourceKeys = array_keys($source);
        $destKeys = array_keys($dest);

        // Sort keys to ensure order doesn't matter for key existence check
        sort($sourceKeys);
        sort($destKeys);

        if ($sourceKeys !== $destKeys) {
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
                // If it's an indexed array (list), we might want to check length or just ignore structure deep inside if it varies.
                // But for strict structure, usually we check if assoc arrays match keys.
                // If both are lists (numeric keys), we generally don't enforce same length for translation often (text length varies),
                // BUT for lists of objects (like actions), we might want to ensure structure of items matches?
                // The prompt says "same structure", usually implies same hierarchy of objects.
                
                // Let's check if it is associative array vs sequential list
                $isSourceAssoc = array_keys($value) !== range(0, count($value) - 1);
                $isDestAssoc = array_keys($dest[$key]) !== range(0, count($dest[$key]) - 1);

                if ($isSourceAssoc !== $isDestAssoc) {
                    $errorMsg = "Array type mismatch for key '$key' (Associative vs List)";
                    return false;
                }

                if ($isSourceAssoc) {
                    if (!$this->validateStructure($value, $dest[$key], $subError)) {
                        $errorMsg = "In key '$key': $subError";
                        return false;
                    }
                } else {
                    // It's a list. We can't strictly enforce length if the user wants to add/remove things, 
                    // but for translation usually length should be same (1:1 translation).
                    // Let's check length for lists of things like actions, skills.
                    if (count($value) !== count($dest[$key])) {
                        $errorMsg = "List length mismatch for key '$key'. Source: " . count($value) . ", Dest: " . count($dest[$key]);
                        return false;
                    }

                    // Check structure of first item if it exists, assuming homogeneous list?
                    // Or iterate all. Let's iterate all to be safe.
                    foreach ($value as $idx => $item) {
                        if (is_array($item) && isset($dest[$key][$idx]) && is_array($dest[$key][$idx])) {
                             if (!$this->validateStructure($item, $dest[$key][$idx], $subError)) {
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
}
