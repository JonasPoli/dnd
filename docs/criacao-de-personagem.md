# Documentação de Criação de Personagem — Dungeons & Dragons 2024

Este documento detalha o fluxo completo e as regras de negócio para o sistema de criação de personagens, baseado no Player's Handbook (2024). Ele serve como especificação para o desenvolvimento do backend (Entidades) e frontend (Fluxo de UI).

O processo de criação de personagem é composto por 4 passos:
1. Escolha uma Classe
2. Determine a Origem
3. Escolha a Espécie
4. Valores de Atributo

Cada passo será um formulário que o usuário preencherá e, ao finalizar cada passo, passao para o próximo passo.
Cada passso deve ter a ID do personagem que está sendo criado ou editado e o passo atual.

---



## Passo 1: Escolha uma Classe

O primeiro passo define o arquétipo mecânico principal.

### Lista de Classes (Core)
O sistema deve suportar as 12 classes base.
*   **Bárbaro (Barbarian)**
*   **Bardo (Bard)**
*   **Bruxo (Warlock)**
*   **Clérigo (Cleric)**
*   **Druida (Druid)**
*   **Feiticeiro (Sorcerer)**
*   **Guardião (Ranger)**
*   **Guerreiro (Fighter)**
*   **Ladino (Rogue)**
*   **Mago (Wizard)**
*   **Monge (Monk)**
*   **Paladino (Paladin)**


Dependendo da classe escolhida, o sistema deve mostrar uma ajuda ao usuário sobre a classe escolhida.
Essa ajuda deve ser um texto explicativo que mostre os traços básicos da classe escolhida, listado abaixo e deve aparecer em todos os próximos passos.
Deve pegar este valor da propriedade `characterCreationHelp` do objeto `ClassDef`.




---

## Passo 2: Determine a Origem

A "Origem" é composta pela escolha do **Antecedente (Background)**. No D&D 2024, o Antecedente é a principal fonte de atributos e talentos iniciais.

### Entenda a Mudança de Atributos
Diferente de 2014 (onde a raça dava atributos), agora é o **Antecedente** que define o Aumento de Valor de Atributo.
*   **Regra:** Escolha 3 atributos listados no Antecedente.
    *   Opção A: +2 em um, +1 em outro.
    *   Opção B: +1 em três diferentes.
    *   *Limite:* Nenhum atributo pode passar de 20.

### Tabela de Antecedentes (Backgrounds)
Cada antecedente concede:
1.  **Aumentos de Atributo** (3 opções específicas).
2.  **Talento de Origem** (1 Talento específico).
3.  **Proficiências em Perícias** (2 fixas).
4.  **Proficiência em Ferramenta** (1 fixa).
5.  **Equipamento** (Kit temático + Ouro fixo ~50 PO).

*(Lista baseada no padrão 2024)*

| Antecedente | Atributos (Escolha 3) | Talento de Origem | Perícias | Ferramenta |
| :--- | :--- | :--- | :--- | :--- |
| **Acólito** | Sab, Int, Car | Iniciado Mágico (Clérigo) | Intuição, Religião | Kit de Caligrafia |
| **Andarilho (Wayfarer)** | Des, Sab, Car | Sortudo (Lucky) | Furtividade, Sobrevivência | Kit de Cartografia |
| **Artesão** | For, Des, Int | Artesão (Crafter) | Investigação, Persuasão | Ferramenta de Artesão |
| **Artista** | Des, Sab, Car | Músico (Musician) | Acrobacia, Atuação | Instrumento Musical |
| **Charlatão** | Des, Con, Car | Hábil (Skilled) | Enganação, Prestidigitação | Kit de Falsificação |
| **Criminoso** | Des, Con, Int | Alerta (Alert) | Furtividade, Prestidigitação | Kit de Ladrão |
| **Eremita** | Con, Sab, Car | Curandeiro (Healer) | Medicina, Religião | Kit de Herbalismo |
| **Escriba** | Int, Sab, Car | Hábil (Skilled) | História, Percepção | Kit de Caligrafia |
| **Fazendeiro** | For, Con, Sab | Robusto (Tough) | Adestrar Animais, Natureza | Ferramenta de Carpinteiro |
| **Guarda** | For, Int, Sab | Alerta (Alert) | Atletismo, Percepção | Jogos de Dados |
| **Guia** | Des, Con, Sab | Iniciado Mágico (Druida) | Furtividade, Sobrevivência | Kit de Cartografia |
| **Marinheiro** | For, Des, Sab | Brigão de Taverna | Natureza, Percepção | Ferramenta de Navegador |
| **Mercador** | Des, Int, Car | Sortudo (Lucky) | Intuição, Persuasão | Kit de Navegador |
| **Nobre** | For, Int, Car | Hábil (Skilled) | História, Persuasão | Jogo de Xadrez/Dragão |
| **Sábio** | Con, Int, Sab | Iniciado Mágico (Mago) | Arcanismo, História | Kit de Caligrafia |
| **Soldado** | For, Des, Con | Atacante Selvagem | Atletismo, Intimidação | Jogos (Cartas/Dados) |

---


## Escolha os atributos
# Conjunto Padrão por Classe

| Classe | Força | Destresa | Constituição | Inteligência | Sabedoria | Carisma |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| Bárbaro | 15 | 13 | 14 | 10 | 12 | 8 |
| Bardo | 8 | 14 | 12 | 13 | 10 | 15 |
| Bruxo | 8 | 14 | 13 | 12 | 10 | 15 |
| Clérigo | 14 | 8 | 13 | 10 | 15 | 12 |
| Druida | 8 | 12 | 14 | 13 | 15 | 10 |
| Feiticeiro | 10 | 13 | 14 | 8 | 12 | 15 |
| Guardião | 12 | 15 | 13 | 8 | 14 | 10 |
| Guerreiro | 15 | 14 | 13 | 8 | 10 | 12 |
| Ladino | 12 | 15 | 13 | 14 | 10 | 8 |
| Mago | 8 | 12 | 13 | 15 | 14 | 10 |
| Monge | 12 | 15 | 13 | 10 | 14 | 8 |
| Paladino | 15 | 10 | 13 | 8 | 12 | 14 |


## Passo 3: Escolha a Espécie

A Espécie define traços biológicos como Visão no Escuro, resistências e habilidades inatas.

### Lista de Espécies
1.  **Aasimar**: Celestial, cura pelas mãos, transformação luminosa.
2.  **Anão (Dwarf)**: Resistência a veneno, visão no escuro, robustez.
3.  **Draconato (Dragonborn)**: Sopro de dragão, resistência a dano.
4.  **Elfo (Elf)**:
    *   *Linhagens (Subespécies):*
        *   **Alto Elfo (High Elf)**: Truque de mago extra, teleporte curto.
        *   **Drow**: Visão no escuro superior, magias drow.
        *   **Elfo Silvestre (Wood Elf)**: Velocidade extra, magias druídicas.
5.  **Gnomo (Gnome)**: Astúcia gnômica (vantagem em saves mentais).
6.  **Golias (Goliath)**: Tamanho grande, redução de dano.
7.  **Humano (Human)**:
    *   Ganham 1 **Talento de Origem** extra à escolha.
    *   Ganham 1 **Habilidade Heroica** (Inspiração ao descansar).
8.  **Orc**: Ação bônus para correr (Disparada), resistência implacável.
9.  **Pequenino (Halfling)**: Sortudo (reroll 1), furtividade, agilidade.
10. **Tiferino (Tiefling)**:
    *   *Linhagens:* Abissal, Chtônico, Infernal (variam as magias e resistências).

---

## Passo 4: Valores de Atributo

O sistema deve apresentar a tabela de "Conjunto Padrão" recomendada por classe, ou permitir a personalização.

### Tabela de Recomendação (Standard Array)
Valores base para distribuir: **15, 14, 13, 12, 10, 8**.

| Classe | Força | Des | Con | Int | Sab | Car |
| :--- | :-: | :-: | :-: | :-: | :-: | :-: |
| Bárbaro | 15 | 13 | 14 | 10 | 12 | 8 |
| Bardo | 8 | 14 | 12 | 13 | 10 | 15 |
| Bruxo | 8 | 14 | 13 | 12 | 10 | 15 |
| Clérigo | 14 | 8 | 13 | 10 | 15 | 12 |
| Druida | 8 | 12 | 14 | 13 | 15 | 10 |
| Feiticeiro | 10 | 13 | 14 | 8 | 12 | 15 |
| Guardião | 12 | 15 | 13 | 8 | 14 | 10 |
| Guerreiro | 15 | 14 | 13 | 8 | 10 | 12 |
| Ladino | 12 | 15 | 13 | 14 | 10 | 8 |
| Mago | 8 | 12 | 13 | 15 | 14 | 10 |
| Monge | 12 | 15 | 13 | 10 | 14 | 8 |
| Paladino | 15 | 10 | 13 | 8 | 12 | 14 |

### Modificadores
Após somar os bônus do Antecedente, calcule os modificadores:
*   10-11: +0
*   12-13: +1
*   14-15: +2
*   16-17: +3 (Máximo inicial normalmente é +3 ou +4 com antecedente)

---

## Passo 5: Proficiências e Idiomas

### Idiomas Padrão
O sistema deve oferecer a seguinte lista para seleção:
*   **Abissal (Abyssal)**
*   **Anão (Dwarvish)**
*   **Celestial**
*   **Comum (Common)**
*   **Dialeto Ladino (Thieves' Cant)** - *Restrito*
*   **Dracônico (Draconic)**
*   **Druídico (Druidic)** - *Restrito*
*   **Élfico (Elvish)**
*   **Fala Profunda (Deep Speech)**
*   **Gigante (Giant)**
*   **Gnomico (Gnomish)**
*   **Goblin**
*   **Halfling**
*   **Infernal**
*   **Orc**
*   **Primordial**
*   **Silvestre (Sylvan)**
*   **Subcomum (Undercommon)**

*Lógica:* Apresentar um seletor para o "Idioma Escolhido" e verificar se a Espécie adiciona mais slots.

---

## Passo 6: Talentos (Feats)

O sistema deve apresentar os talentos disponíveis por categoria.

### Talentos de Origem (Nível 1)
Disponíveis via Antecedente ou Humano.
1.  **Alerta (Alert):** Bônus na Iniciativa, não pode ser surpreendido.
2.  **Artesão (Crafter):** Proficiência em ferramentas, desconta em compras.
3.  **Atacante Selvagem (Savage Attacker):** Vantagem em rolagens de dano de arma.
4.  **Brigão de Taverna (Tavern Brawler):** Dano desarmado aprimorado, empurrar.
5.  **Curandeiro (Healer):** Cura com Kit de Herbalismo ou reroll de cura mágica.
6.  **Duro na Queda (Tough):** +2 PV por nível.
7.  **Hábil (Skilled):** Proficiência em 3 perícias ou ferramentas.
8.  **Iniciado Mágico (Magic Initiate):** 2 Truques e 1 Magia de Nível 1 (Clérigo, Druida ou Mago).
9.  **Músico (Musician):** Inspiração Heroica ao descansar.
10. **Sortudo (Lucky):** Pontos de Sorte para vantagem.

### Talentos de Estilo de Luta (Fighting Styles)
Disponíveis para Guerreiros, Paladinos e Guardiões (nível 1 ou 2).
1.  **Arquearia (Archery):** +2 no ataque com armas à distância.
2.  **Defesa (Defense):** +1 na CA quando usando armadura.
3.  **Duelo (Dueling):** +2 no dano com uma arma de uma mão.
4.  **Combate com Duas Armas (Two-Weapon Fighting):** Adiciona atributo no dano da segunda arma.
5.  **Combate com Armas Grandes (Great Weapon Fighting):** Reroll de dano 1 ou 2.
6.  **Proteção (Protection):** Desvantagem no ataque inimigo contra aliado.

### Outras Categorias
*   **Talentos Gerais:** Proficiências, Especializações, Atributos +1. (Nível 4+)
*   **Dádivas Épicas:** Poderes de quase-divindade. (Nível 19+)

---

## Passo 7: Equipamento Inicial

### Bugiganga (Trinket)
*   **Ação:** O sistema deve sortear 1 item de uma lista de 100 (tabela de Trinkets).
*   **Interação:** Mostrar o item sorteado e um campo de texto: *"Como você conseguiu isso? Melhore a descrição."*

### Compra Inicial
O jogador tem duas opções:
1.  **Equipamento Inicial (Padrão):**
    *   **Da Classe:** Armas e armaduras iniciais.
    *   **Do Antecedente:** Ouro (~50 PO) + Itens de sabor (roupas, ferramentas).
2.  **Ouro Inicial (Wealth):**
    *   Apenas recebe o Ouro da Classe + Ouro do Antecedente e compra tudo item a item.

*Regra de Ouro por Classe (referência):*
*   Bardo, Clérigo, Guerreiro, Paladino, Guardião: ~150 PO
*   Ladino, Bruxo, Mago, Druida: ~110-120 PO
*   Bárbaro, Monge, Feiticeiro: ~50-70 PO

---

## Passo 8: Detalhes Pessoais

Finalize com perguntas narrativas obrigatórias para "alma" do personagem.

1.  **Quem criou você?** (Família, mentor, orfanato?)
2.  **Quem foi seu amigo mais querido na infância?**
3.  **Você cresceu com um animal de estimação?**
4.  **Você se apaixonou?** Se sim, por quem?
5.  **Organizações?** Guildas, religiões? Ainda é membro?
6.  **Inspiração para aventura?** O que te fez sair de casa?

Salvar estas respostas no campo `biography` ou `notes` do personagem.
