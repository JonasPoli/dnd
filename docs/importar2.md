Agora, você deve navegar pelo site:
https://open5e.com/
e localizar o menu lateral esquerdo.
Este menu dá acesso a várias páginas.

# Não existentes
Cada uma dessas páginas deve ser analisada e verificada se podem ser transformada numa entidade.

Algumas não fora transformadas em entidades e você deve fazer isso 
Por exemplo, https://open5e.com/characters/alignment possui a lista

```
Lawful good (LG) creatures can be counted on to do the right thing as expected by society. Gold dragons, paladins, and most dwarves are lawful good.

Neutral good (NG) folk do the best they can to help others according to their needs. Many celestials, some cloud giants, and most gnomes are neutral good.

Chaotic good (CG) creatures act as their conscience directs, with little regard for what others expect. Copper dragons, many elves, and unicorns are chaotic good.

Lawful neutral (LN) individuals act in accordance with law, tradition, or personal codes. Many monks and some wizards are lawful neutral.

Neutral (N) is the alignment of those who prefer to steer clear of moral questions and don't take sides, doing what seems best at the time. Lizardfolk, most druids, and many humans are neutral.

Chaotic neutral (CN) creatures follow their whims, holding their personal freedom above all else. Many barbarians and rogues, and some bards, are chaotic neutral.

Lawful evil (LE) creatures methodically take what they want, within the limits of a code of tradition, loyalty, or order. Devils, blue dragons, and hobgoblins are lawful evil.

Neutral evil (NE) is the alignment of those who do whatever they can get away with, without compassion or qualms. Many drow, some cloud giants, and goblins are neutral evil.

Chaotic evil (CE) creatures act with arbitrary violence, spurred by their greed, hatred, or bloodlust. Demons, red dragons, and orcs are chaotic evil.
```

o que pode ser entendido que temos uma entidade com 3 propriedades:
- name: Lawful good, Neutral good, Chaotic good, Lawful neutral, Neutral, Chaotic neutral, Lawful evil, Neutral evil, Chaotic evil
- description
- abbreviation: LG, NG, CG, LN, N, CN, LE, NE, CE

Você deve criar uma nova entidade Alignment que possui essas 3 propriedades e cadastrar todos estes registros.

Depois, em https://open5e.com/characters/languages

Temos:
```
Standard Languages (table)

Language	Typical Speakers	Script
Common	Humans	Common
Dwarvish	Dwarves	Dwarvish
Elvish	Elves	Elvish
Giant	Ogres, giants	Dwarvish
Gnomish	Gnomes	Dwarvish
Goblin	Goblinoids	Dwarvish
Halfling	Halflings	Common
Orc	Orcs	Dwarvish
Exotic Languages (table)
```

e
```
Language	Typical Speakers	Script
Abyssal	Demons	Infernal
Celestial	Celestials	Celestial
Draconic	Dragons, dragonborn	Draconic
Deep Speech	Aboleths, cloakers	-
Infernal	Devils	Infernal
Primordial	Elementals	Dwarvish
Sylvan	Fey creatures	Elvish
Undercommon	Underworld traders	Elvish
```

o que se conclui que temos uma entidade Language com 3 propriedades:
- name (Common, Dwarvish, Elvish, Giant, Gnomish, Goblin, Halfling, Orc)
- type (Standard, Exotic)
- Typical Speakers (Humans, Dwarves, Elves, Ogres, Giants, Gnomes, Goblinoids, Halflings, Orcs)
- script (Common, Dwarvish, Elvish, Dwarvish, Dwarvish, Dwarvish, Common, Dwarvish)

Depois, em https://open5e.com/characters/leveling-up
temos a tabela 'levelup` que possui:
```
Character Advancement (table)

Experience Points	Level	Proficiency Bonus
0	1	+2
300	2	+2
900	3	+2
2,700	4	+2
6,500	5	+3
14,000	6	+3
23,000	7	+3
34,000	8	+3
48,000	9	+4
64,000	10	+4
85,000	11	+4
100,000	12	+4
120,000	13	+5
140,000	14	+5
165,000	15	+5
195,000	16	+5
225,000	17	+6
265,000	18	+6
305,000	19	+6
355,000	20	+6
```
onde concluímos que deveremos ter uma entidade LevelUp com 3 propriedades:
- experiencePoints (0, 300, 900, 2700, 6500, 14000, 23000, 34000, 48000, 64000, 85000, 100000, 120000, 140000, 165000, 195000, 225000, 265000, 305000, 355000)
- level (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20)
- proficiencyBonus (+2, +2, +2, +2, +3, +3, +3, +3, +4, +4, +4, +4, +5, +5, +5, +5, +6, +6, +6, +6)

# Existentes

Caso fique com alguma dúvida sobre o funcionamento da tabela, ou da regra, leia o livro de regras em /Volumes/Dados/work/dnd/docs/livro do jogdor 2024.pdf e tire suas dúvidas.

A tabela de monstros, note que esta já foi convertida em entidade. neste caso, você deve verificar se a estrutura da entidade está correta.
Cada um dos monstros possui segue um padrão que pode ser encontrado em https://open5e.com/monsters/a-mi-kuk

Tabela monster (campos “diretos”)
Identidade e origem do conteúdo

id (PK, bigint/uuid)

name (varchar)

slug (varchar, unique) (ex: adult-red-dragon, tarrasque_bf)

document_slug (varchar) (ex: SRD vs Tome of Beasts etc.) 
Open5e

document_title (varchar, opcional)

source (varchar, opcional) (algumas bases chamam de “source”)

page (varchar/int, opcional) (muitos SRDs trazem “page”/referência)

srd (bool, opcional) (se o item é SRD/OGL)

is_homebrew (bool, opcional)

created_at, updated_at (datetime) (no seu banco)

external_updated_at (datetime, opcional) (controle de reimport)

external_hash (char(64), opcional) (hash do JSON pra detectar mudança)

external_url (varchar) (url do Open5e / ou endpoint do API)

image_url (varchar, opcional)

Classificação “descritiva”

size (enum/varchar) (Tiny/Small/Medium/Large/Huge/Gargantuan)

type (varchar) (dragon, fiend, undead…)

subtype (varchar, nullable)

alignment (varchar) (texto pronto: “chaotic evil”, “unaligned”, etc.)

challenge_rating (varchar) (muitas fontes usam “1/2”, “30”… como string)

xp (int, opcional)

Defesa e vida

armor_class (int)

armor_desc (varchar/text, opcional) (ex: “natural armor”)

hit_points (int)

hit_dice (varchar) (ex: “19d12+76”)

damage_vulnerabilities (text) (às vezes vem como string separada por vírgula)

damage_resistances (text)

damage_immunities (text)

condition_immunities (text)

Deslocamento e sentidos

speed (json) (ex: {walk: "40 ft.", fly:"80 ft.", swim:"40 ft."})

senses (varchar/text) (ex: “blindsight 60 ft., darkvision 120 ft., passive Perception 24”)

passive_perception (int, opcional)

languages (varchar/text)

Atributos e proficiências

strength (int)

dexterity (int)

constitution (int)

intelligence (int)

wisdom (int)

charisma (int)

saving_throws (varchar/text) (ex: “Dex +7, Con +12, Wis +9, Cha +10”)

skills (varchar/text) (ex: “Perception +14, Stealth +7”)

proficiency_bonus (int, opcional) (se você quiser calcular/exibir facilmente)

Texto “lore”

description (longtext, opcional) (alguns monstros têm um parágrafo de lore)

legendary_desc (text, opcional) (texto que explica “pode fazer 3 ações lendárias…”)

lair_actions (longtext, opcional) (se existir nesse conjunto de dados)

regional_effects (longtext, opcional) (se existir)

Tabelas relacionadas (as listas do stat block)

Você citou Actions como tabela separada — perfeito. Eu recomendaria um modelo único de “entries” com um kind, ou 4 tabelas separadas (ações, reações, etc.). O mais flexível:

monster_feature (genérica) ✅ (minha sugestão)

id (PK)

monster_id (FK → monster.id, index)

kind (enum)

trait (special abilities / traits)

action

bonus_action

reaction

legendary_action

mythic_action (se aparecer em algumas coleções)

name (varchar) (título)

description (longtext) (corpo do texto)

attack_bonus (int, nullable) (se vier separado em algumas fontes)

damage_dice (varchar, nullable) (idem)

sort_order (int) (pra manter a ordem do bloco)

usage (json, nullable) (ex: recarga “5–6”, “3/day”, etc., se vier estruturado)

Isso cobre exatamente o que você pediu: Actions vira outra tabela com título e descrição, e você reutiliza o mesmo formato para Reactions/Legendary/Special Abilities.

Observações importantes pro seu caso (importação e consistência)

Guarde o slug + document_slug como chave natural

Porque pode existir “mesmo nome” em documentos diferentes.

O Open5e incentiva filtrar por documento (document__slug) 
Open5e
.

Os campos “listas” do Open5e muitas vezes vêm como string

skills, saving_throws, resistências etc. podem vir já “formatados”.

Você pode começar guardando como text e depois (se quiser) normalizar para tabelas (monster_skill, monster_save, etc.).

Controle de reimport

No seu DB: external_hash (hash do JSON bruto ou normalizado).

Se o hash não mudou, você pula atualização e não regrava monster_feature.