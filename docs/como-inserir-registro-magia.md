# manual: Como Inserir Registros de Magia

Este guia detalha o processo técnico para inserir novas magias no banco de dados do projeto. O objetivo é manter a consistência dos dados, evitar duplicação e garantir que as funcionalidades do sistema (filtros, busca, exibição) funcionem corretamente.

## 1. Visão Geral do Script de Inserção

Utilizamos um script SQL (`spells_insert.sql`) que deve ser **idempotente**. Isso significa que você pode rodar o mesmo script várias vezes sem criar magias duplicadas.

O comando chave é `INSERT INTO ... ON DUPLICATE KEY UPDATE`.
- **Chave Única:** O sistema identifica a magia pelo campo `rule_slug`.
- **Comportamento:** Se o `rule_slug` já existe, o comando ATUALIZA os dados com os valores do script. Se não existe, INSERE um novo registro.

> [!IMPORTANT]
> Nunca altere o `rule_slug` de uma magia existente se a intenção for apenas corrigir um erro de digitação no nome ou descrição. O slug é o ID fixo.

## 2. Preparação do Ambiente

Antes de adicionar o bloco de insert da magia, certifique-se de que o script define as variáveis de **Fonte (Source)** e **Classes** no início.

```sql
-- Definir ID da Fonte (Source)
SET @source_id = (SELECT id FROM rules_source WHERE slug = 'ua-psi' LIMIT 1); 

-- Carregar IDs das Classes existentes
SET @bard = (SELECT id FROM class_def WHERE name = 'Bardo' LIMIT 1);
SET @wizard = (SELECT id FROM class_def WHERE name = 'Mago' LIMIT 1);
SET @sorcerer = (SELECT id FROM class_def WHERE name = 'Feiticeiro' LIMIT 1);
SET @warlock = (SELECT id FROM class_def WHERE name = 'Bruxo' LIMIT 1);
-- (Adicione outras classes conforme necessário)
```

## 3. Estrutura e Explicação dos Campos

Abaixo, detalhamos cada campo da tabela `spell` e os valores aceitos/padronizados.

### Campos de Identificação e Texto
| Campo | Tipo | Descrição | Exemplo |
| :--- | :--- | :--- | :--- |
| `is_active` | `BOOL` | Se a magia está ativa no sistema. Padrão `1`. | `1` |
| `name` | `VARCHAR` | Nome original em **Inglês**. | `'Fireball'` |
| `name_pt` | `VARCHAR` | Nome traduzido em **Português**. | `'Bola de Fogo'` |
| `rule_slug` | `VARCHAR` | **Identificador Único**. Use kebab-case em inglês. | `'fireball'` |
| `description_md` | `TEXT` | Descrição completa em Inglês (Markdown). | `'A bright streak flashes...'` |
| `description_md_pt`| `TEXT` | Descrição completa em Português (Markdown). | `'Um feixe brilhante...'` |

### Campos Técnicos e Enums (Valores Padronizados)

#### **`school` (Escola de Magia)**
> [!WARNING]
> Este campo usa um Enum e os valores no banco de dados estão em **Português**. Usar inglês causará erro.

Valores Válidos (Case Sensitive):
- `'Abjuração'`
- `'Adivinhação'`
- `'Conjuração'`
- `'Encantamento'`
- `'Evocação'`
- `'Ilusão'`
- `'Necromancia'`
- `'Transmutação'`

#### **`level` (Nível)**
Inteiro de `0` a `9`.
- `0`: Truque (Cantrip)
- `1` a `9`: Nível da magia.

#### **`casting_time` (Tempo de Conjuração)**
Texto livre. Padrão recomendado:
- `'1 ação'`, `'1 ação bônus'`, `'1 reação'`
- `'1 minuto'`, `'10 minutos'`, `'1 hora'`

#### **`spell_range` (Alcance)**
Texto livre. Padrão comum (metros):
- `'Pessoal'`, `'Toque'`, `'Vista'`, `'Ilimitado'`
- `'1,5 m'`, `'3 m'`, `'9 m'`, `'18 m'`, `'27 m'`, `'36 m'`, `'45 m'`

#### **`duration` (Duração)**
Texto livre. Indica se exige concentração.
Exemplos comuns:
- `'Instantânea'`
- `'Concentração, até 1 minuto'`
- `'1 minuto'`, `'1 hora'`, `'8 horas'`, `'24 horas'`
- `'Até ser dissipada'`

### Componentes e Booleanos

Estes campos deve estar sincronizados. O campo `components` é apenas visual curto (V, S, M), enquanto `material` contém a descrição do item, e os campos `is_*` são a lógica do sistema.

| Campo (Lógico) | Valor | Descrição | Componente Visual Correlato |
| :--- | :--- | :--- | :--- |
| `is_verbal` | `1`/`0` | Requer componente verbal? | Em `components` tem 'V' |
| `is_somatic` | `1`/`0` | Requer componente somático? | Em `components` tem 'S' |
| `is_material` | `1`/`0` | Requer material? | Em `components` tem 'M' |
| `is_concentration`| `1`/`0` | Exige concentração? | Em `duration` tem 'Concentração' |
| `is_ritual` | `1`/`0` | Pode ser ritual? | (Tag R) |

**Colunas de Texto de Componentes:**
- `components`: Apenas as siglas. Ex: `'V, S'`, `'V, S, M'`, `'M'`.
- `material`: Descrição textual do material componente. Ex: `'uma pequena esfera de guano de morcego e enxofre'`. Se não houver material, use `NULL` ou `''`.

### Campos Especiais: Níveis Superiores
Se a magia tem um efeito adicional em níveis superiores, **não coloque isso na descrição principal**. Use os campos dedicados:
- `higher_levels_md`: Texto em Inglês ("At Higher Levels...").
- `higher_levels_md_pt`: Texto em Português ("Em Níveis Superiores...").
*Se não houver, use `NULL`.*

---

## 4. Checklist de Erros Comuns e Soluções

Antes de rodar o comando, verifique:

1.  **Erro: `Data truncated for column 'school'`**
    *   **Causa:** Você usou o nome da escola em Inglês (ex: 'Evocation') ou minúsculo.
    *   **Solução:** Use o Enum em Português com inicial Maiúscula (ex: `'Evocação'`).

2.  **Erro: `Duplicate entry '...' for key 'spell.UNIQ_...'`**
    *   **Causa:** O script não está usando `ON DUPLICATE KEY UPDATE` ou você está tentando inserir um `rule_slug` novo que conflita com outro índice único (como source + slug).
    *   **Solução:** Sempre use a sintaxe `INSERT ... ON DUPLICATE KEY UPDATE`.

3.  **Magia perde as Classes após rodar o script**
    *   **Causa:** O script padrão faz `DELETE FROM spell_class_def` antes de inserir.
    *   **Solução:** No bloco `INSERT INTO spell_class_def`, você deve listar **TODAS** as classes que têm acesso àquela magia. Se você esquecer uma, ela será removida da magia.

4.  **Texto "Em Níveis Superiores" duplicado na descrição**
    *   **Solução:** Certifique-se de mover esse texto para `higher_levels_md`/`pt` e removê-lo de `description_md`/`pt`.

---

## 5. Exemplo Completo Atualizado

```sql
INSERT INTO spell (
    is_active, 
    name, name_pt, 
    level, school, 
    casting_time, spell_range, 
    components, material, -- Note o campo material separado
    is_verbal, is_somatic, is_material, 
    is_concentration, is_ritual, 
    duration, 
    description_md, description_md_pt, 
    higher_levels_md, higher_levels_md_pt, 
    rule_slug, rules_source_id
)
VALUES (
    1, 
    'Fireball', 'Bola de Fogo', 
    3, 'Evocação',                -- ENUM PT
    '1 ação', '45 metros', 
    'V, S, M',                    -- Apenas siglas
    'uma bola de guano de morcego e enxofre', -- Descrição do material
    1, 1, 1,                      -- Flags V, S, M
    0, 0,                         -- Conc, Ritual
    'Instantânea',
    'A bright streak flashes...', 
    'Um feixe brilhante sai...',       
    'The damage increases by 1d6...', 
    'O dano aumenta em 1d6...',         
    'fireball', @source_id
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name), name_pt = VALUES(name_pt), 
    level = VALUES(level), school = VALUES(school),
    casting_time = VALUES(casting_time), spell_range = VALUES(spell_range), 
    components = VALUES(components), material = VALUES(material),
    is_verbal = VALUES(is_verbal), is_somatic = VALUES(is_somatic), is_material = VALUES(is_material), 
    is_concentration = VALUES(is_concentration), is_ritual = VALUES(is_ritual),
    duration = VALUES(duration), 
    description_md = VALUES(description_md), description_md_pt = VALUES(description_md_pt),
    higher_levels_md = VALUES(higher_levels_md), higher_levels_md_pt = VALUES(higher_levels_md_pt),
    is_active = VALUES(is_active), id = LAST_INSERT_ID(id);

SET @spell_id = LAST_INSERT_ID();

DELETE FROM spell_class_def WHERE spell_id = @spell_id;
-- ATENÇÃO: Liste TODAS as classes aqui
INSERT INTO spell_class_def (spell_id, class_def_id) VALUES (@spell_id, @sorcerer), (@spell_id, @wizard);
```

## 6. Execução

```bash
mysql -h 127.0.0.1 -P 3306 -u root -pwab12345678 dnd < spells_insert.sql
```
