# Guia de Estrutura: Monster `srcJson`

O campo `srcJson` na tabela `monster` armazena a estrutura de dados **normalizada** da criatura. Este JSON é considerado a "fonte de verdade" ("Source of Truth") para os atributos da entidade, garantindo que mesmo se colunas individuais forem alteradas, os dados originais brutos (porém padronizados para camelCase) permaneçam preservados.

## Estrutura do Objeto JSON

O objeto armazenado em `srcJson` utiliza chaves em **camelCase**. Abaixo estão as chaves principais e seus significados.

### 🛡️ Core Stats (Estatísticas Principais)

| Chave JSON (`srcJson`) | Tipo | Descrição |
| :--- | :--- | :--- |
| `name` | String | Nome da criatura. |
| `size` | String | Tamanho (Tiny, Medium, Large, etc). |
| `type` | String | Tipo (Aberration, Beast, etc). |
| `subtype` | String \| Null | Subtipo (ex: Shapechanger). |
| `group` | String \| Null | Grupo familiar (ex: Dragons). |
| `alignment` | String | Alinhamento (ex: Chaotic Evil). |
| `armorClass` | Int | Valor numérico da CA. |
| `armorDesc` | String | Descrição da armadura (ex: "natural armor"). |
| `hitPoints` | Int | Pontos de vida totais. |
| `hitDice` | String | Fórmula dos dados de vida (ex: `10d8+40`). |
| `speed` | Object | Deslocamentos (ex: `{"walk": 30, "fly": 60}`). |
| `challengeRating` | String | Nível de desafio (ex: "5" ou "1/4"). |
| `description` | String | Descrição completa da criatura |

### 🧠 Ability Scores (Atributos)

| Chave | Tipo | Descrição |
| :--- | :--- | :--- |
| `strength` | Int | Força. |
| `dexterity` | Int | Destreza. |
| `constitution` | Int | Constituição. |
| `intelligence` | Int | Inteligência. |
| `wisdom` | Int | Sabedoria. |
| `charisma` | Int | Carisma. |

**Saving Throws** (opcionais, null se não houver bônus específico):
`strengthSave`, `dexteritySave`, `constitutionSave`, `intelligenceSave`, `wisdomSave`, `charismaSave`.

### ⚔️ Combat & Skills (Combate e Perícias)

| Chave | Tipo | Descrição |
| :--- | :--- | :--- |
| `perception` | Int | Percepção passiva. |
| `skills` | Object | Dicionário de perícias (ex: `{"stealth": 5}`). |
| `senses` | String | Descrição textual dos sentidos. |
| `languages` | String | Idiomas conhecidos. |
| `damageImmunities` | String | Imunidades a dano. |
| `damageResistances` | String | Resistências a dano. |
| `damageVulnerabilities` | String | Vulnerabilidades a dano. |
| `conditionImmunities` | String | Imunidades a condições. |

### ⚡ Actions & Abilities (Ações e Habilidades)

Estas chaves contêm arrays de objetos definindo as capacidades da criatura.

| Chave | Conteúdo |
| :--- | :--- |
| `specialAbilities` | Lista de habilidades passivas. |
| `actions` | Ações principais (ataques). |
| `bonusActions` | Ações bônus. |
| `reactions` | Reações. |
| `legendaryActions` | Ações lendárias. |
| `legendaryDesc` | Descrição introdutória das ações lendárias. |
| `spellList` | Lista de magias (se conjurador). |

---

## 🔄 Mapeamento de Importação (Open5e -> `srcJson`)

O processo de importação (`app:rules:import`) utiliza o serviço `MonsterImporter` para ler dados externos (como da API Open5e) e convertê-los para o formato do nosso sistema.

A API Open5e fornece dados com chaves em **snake_case**. O importador normaliza isso para **camelCase** antes de salvar em `srcJson`.

### Tabela de Conversão

| Campo Open5e (snake_case) | Campo `srcJson` (camelCase) | Entidade Monster |
| :--- | :--- | :--- |
| `name` | `name` | `$monster->setName()` |
| `size` | `size` | `$monster->setSize()` |
| `type` | `type` | `$monster->setType()` |
| `subtype` | `subtype` | `$monster->setSubtype()` |
| `alignment` | `alignment` | `$monster->setAlignment()` |
| `armor_class` | `armorClass` | `$monster->setArmorClass()` |
| `armor_desc` | `armorDesc` | `$monster->setArmorDesc()` |
| `hit_points` | `hitPoints` | `$monster->setHitPoints()` |
| `hit_dice` | `hitDice` | `$monster->setHitDice()` |
| `speed` | `speed` (json) | `$monster->setSpeedJson()` |
| `strength` | `strength` | `$monster->setStrength()` |
| `dexterity` | `dexterity` | `$monster->setDexterity()` |
| `constitution` | `constitution` | `$monster->setConstitution()` |
| `intelligence` | `intelligence` | `$monster->setIntelligence()` |
| `wisdom` | `wisdom` | `$monster->setWisdom()` |
| `charisma` | `charisma` | `$monster->setCharisma()` |
| `strength_save` | `strengthSave` | `$monster->setStrengthSave()` |
| `skills` | `skills` (json) | `$monster->setSkillsJson()` |
| `senses` | `senses` | `$monster->setSenses()` |
| `languages` | `languages` | `$monster->setLanguages()` |
| `challenge_rating` | `challengeRating` | `$monster->setChallengeRating()` |
| `special_abilities` | `specialAbilities` | `$monster->setSpecialAbilities()` |
| `actions` | `actions` | `$monster->setActionsJson()` |
| `bonus_actions` | `bonusActions` | `$monster->setBonusActionsJson()` |
| `reactions` | `reactions` | `$monster->setReactionsJson()` |
| `legendary_actions` | `legendaryActions` | `$monster->setLegendaryActions()` |
| `img_main` | `imgMain` | `$monster->setImgMain()` |

### Nota sobre Campos Populados

Praticamente **todos** os campos da entidade `Monster` são populados a partir do `srcJson` (ou do payload normalizado que o gera). O `srcJson` serve como um backup estruturado completo, enquanto as colunas individuais da tabela permitem consultas SQL eficientes e indexação.
