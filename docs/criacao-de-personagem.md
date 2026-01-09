# Documentação de Criação de Personagem — Dungeons & Dragons 2024

Este documento detalha o fluxo completo e as regras de negócio para o sistema de criação de personagens, baseado no Player's Handbook (2024). Ele serve como especificação para o desenvolvimento do backend (Entidades) e frontend (Fluxo de UI).

O processo de criação de personagem é composto por vários passos.

Cada passo será um formulário que o usuário preencherá e, ao finalizar cada passo, passao para o próximo passo.
Cada passso deve ter a ID do personagem que está sendo criado ou editado e o passo atual.

---

A criação de um personagem deve criar um registro na entidade `Character`. Todos os campos existentes na entidade devem ser verificados se devem ser removidos da entidade, caso não usado; acrecentado, caso ausente; ajustado, caso necessário.

O sistema deve ser um formulário com vários passos. Cada passo deve ser apresentado numa página diferente, com rotas diferentes.

Deve ser possível navegar entre os passos do formulário.

## Passo 1: Escolha uma Classe

O primeiro passo define o arquétipo mecânico principal.
Devem ser apresentadas as classes base do D&D 2024, pelo campo ClassDef.name.
Deve ser possível selecionar uma classe e avançar para o próximo passo.

A tela deve estar dividida em 2 partes
Do lado esquerdo devem ser listadas as classes, com logo e nome da Classe
Do lado direito deve ser mostrada a descrição da classe selecionada com Dado de Vida, Testes de Resistência, Ajuda na Criação,Pontos de Vida,Tabela da Classe, descriptionMd. Deve ser aplicada a formatação Markdown.

Deve haver um botão para avançar para o próximo passo.

## Passo 2: Escolha a subclasse
Deve aparecer, acima, de forma bem bonita e organizada, o nome e o logo da classe escolhida no passo anterior.
Similar ao layout do passo 01, A tela deve estar dividida em 2 partes
Do lado esquerdo devem ser listadas as subclasses da classe escolhida no passo anterior
Do lado direito deve ser mostrada a descrição da SUBCLASSE selecionada descriptionMd. Deve ser aplicada a formatação Markdown.
Deve haver um botão para avançar para o próximo passo e um botão para voltar para o passo anterior.

## Passo 3: Proficiência em Perícias 
No terceiro passo, o usuário deve selecionar as     .
A quantidade de Perícias que o usuário pode selecionar depende da classe escolhida no passo anterior.
Na classe que escolheu, tem um campo chamado Quantidade de Perícias Iniciais que define quantas perícias o usuário pode selecionar.
As opções possíveis estão na entidade class-skill, e o usuário deve selecionar as perícias que deseja, dentre as disponíveis, na quantidade definida.
A lista de pericias deve ser exibida em uma lista de checkboxes, com o nome e a descrição da perícia (Exemplos de Usos) .
Deve haver um botão para avançar para o próximo passo e um botão para voltar para o passo anterior.

## Passo 4: Proficiências em ferramentas
No quarto passo, o usuário deve selecionar as ferramentas.
A quantidade de ferramentas que o usuário pode selecionar depende da classe escolhida no passo anterior.
Na classe que escolheu, tem um campo chamado "Quantidade de Ferramentas Iniciais" que define quantas ferramentas o usuário pode selecionar.
As opções possíveis estão na entidade equipment.
O sistema deve ter uma lista de checkboxes, com o Tipo (PT), Categoria de Arma (PT), Tipo de Dano (PT) e a Descrição (PT).
Deve ter uma maneira de filtrar a lista de ferramentas, por tipo, categoria de arma, tipo de dano e descrição para poder ser selecionada. A quantidade de ferramentas selecionadas deve ser exibida abaixo da lista. e o máximo de ferramentas selecionadas deve ser a quantidade especificada na classe em "Quantidade de Ferramentas Iniciais" .
Deve haver um botão para avançar para o próximo passo e um botão para voltar para o passo anterior.


## Passo 5: Truques Conhecidos
No quinto passo, o usuário deve selecionar os truques conhecidos.
Algumas classes não têm truques, nesse caso, o passo deve ser ignorado.
Para saber se a classe tem truques, deve-se verificar a entidade "classLevels" da classe escolhida no passo anterior filtrado pelo nivel do personagem (level) a principio no nivel 1. A quantidade de truques que o usuário pode selecionar está na propriedade "cantripsKnown" da entidade "classLevels", filtrada pela classe escolhida e o nivel do personagem, a principaio, 1.

A lista de truques que o usuário pode selecionar está na entidade "spell", filtrada pela classe escolhdida em spell.classes
e pelo tipo de spell, que deve ser level = 0
A lista de opçoes deve apresentar os campos: 
name (ou name_pt, se existir)
Escola de Magia (com ícone dela), Tempo de Conjuração, Alcance, Duração, Tipos de Componentes (V, S e/ou M)
se exige ritual (em ícone), Concentração (em ícone), descriptionMd (ou descriptionMdPt, se existir), higherLevelsMd (ou higherLevelsMdPt, se existir)

Precisa permitir que o usuário escolha a quantidade de truques que ele deseja.
Deve haver um botão para avançar para o próximo passo e um botão para voltar para o passo anterior.


## Passo 6: Magias Conhecidas
No sexto passo, o usuário deve selecionar as magias conhecidas.
Algumas classes não têm magias, nesse caso, o passo deve ser ignorado.
Para saber se a classe tem magias, deve-se verificar a entidade "classLevels" da classe escolhida no passo anterior filtrado pelo nivel do personagem (level) a principio no nivel 1. A quantidade de magias que o usuário pode selecionar está na propriedade "spellKnown" da entidade "classLevels", filtrada pela classe escolhida e o nivel do personagem, a principaio, 1.

A lista de magias que o usuário pode selecionar está na entidade "spell", filtrada pela classe escolhdida em spell.classes
e pelo tipo de spell, que deve ser level = 1
o level da spell deve ser encontrado em Tabela de Progressão.
como o personagem a que está sendo criado possui, a princípio, o nivel 1, deve-se verificar a entidade "classLevels" da classe escolhida no passo anterior filtrado pelo nivel do personagem (level) a principio no nivel 1.
A lista de opçoes deve apresentar os campos: 
name (ou name_pt, se existir)
Escola de Magia (com ícone dela), Tempo de Conjuração, Alcance, Duração, Tipos de Componentes (V, S e/ou M)
se exige ritual (em ícone), Concentração (em ícone), descriptionMd (ou descriptionMdPt, se existir), higherLevelsMd (ou higherLevelsMdPt, se existir)

Precisa permitir que o usuário escolha a quantidade de magias que ele deseja.
Deve haver um botão para avançar para o próximo passo e um botão para voltar para o passo anterior.

## Passo 7: Escolha a Espécie
No sétimo passo, o usuário deve selecionar a espécie do personagem.
Deve ser apresentada uma lista das espécies disponíveis (Entidade `Species`).
A tela deve manter o padrão de 2 colunas:
- Esquerda: Lista de Espécies com nome e ícone (se houver).
- Direita: Detalhes da espécie selecionada, exibindo:
    - Traços Raciais (Visão no Escuro, Deslocamento, etc.)
    - Descrição (`descriptionMd` ou descriptionMdPt)
    - Habilidades especiais

Ao selecionar, deve salvar a relação `species` na entidade `Character`.
Deve haver um botão para avançar para o próximo passo e um botão para voltar para o passo anterior.

## Passo 8: Escolha o Antecedente (Background)
No oitavo passo, o usuário escolhe a origem do personagem.
No D&D 2024, o Antecedente é crucial pois define os aumentos de atributo e o talento inicial.
Deve ser apresentada a lista de Antecedentes (Entidade `Background`).
Layout de 2 colunas:
- Esquerda: Lista de Antecedentes.
- Direita: Detalhes mostrando:
    - Bônus de Atributos (Ex: +2 Força, +1 Const)
    - Talento Inicial (Feat)
    - Perícias Concedidas
    - Ferramentas Concedidas
    - Equipamento Inicial
    - Descrição (`descriptionMd` ou narrativa)

Ao selecionar, deve salvar a relação `background` na entidade `Character`.
*Nota: Se o Antecedente conceder equipamentos ou perícias fixas, estes devem ser adicionados automaticamente ou validados nos passos anteriores/posteriores se houver conflito.*
Deve haver um botão para avançar para o próximo passo e um botão para voltar para o passo anterior.

## Passo 9: Valores de Atributos
No nono passo, o usuário define os valores base de Força, Destreza, Constituição, Inteligência, Sabedoria e Carisma.
O sistema deve preencher os valores base de acordo com a classe escolhida no passo anterior, de acordo com cada item da tabela abaixo:
| Classe | For. | Des. | Con. | Int. | Sab. | Car. |
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
deve ser uma entidade acssoria do personagem, com a entity attribute e o valor


## Passo 10: Idiomas
O sistema já deve começar com o idioma comum (common) selecionado 
1. Lógica do Painel de Seleção
No D&D 2024, a regra geral é: Você conhece o Comum e mais um idioma à sua escolha.

O painel deve ser dividido em duas seções visuais:

Idiomas Fixos (Read-only):

Comum: Já vem pré-selecionado e travado para todos os personagens.

Idiomas de Classe (Condicional): Se o jogador selecionar Druida, o sistema injeta "Druídico" automaticamente. Se selecionar Ladino, injeta "Gíria de Ladrão" (Thieves' Cant).

Slots de Escolha (Dropdowns):

Slot do Antecedente: A maioria dos antecedentes concede 1 escolha.

Filtro Padrão: O dropdown deve carregar, por padrão, apenas os idiomas onde type = 'Padrão' (aqueles da sua tabela atualizada: Anão, Élfico, Sinais, etc.).

Toggle "Mostrar Exóticos": Inclua um checkbox ou botão "Permitir idiomas raros" que, se o Mestre permitir, recarrega o dropdown incluindo type = 'Exótico' (Abissal, Dracônico, etc.). Nota: Dracônico agora é Padrão na sua atualização.

2. Validação de Backend (Symfony)
Ao salvar a ficha, seu sistema deve validar:

Não Duplicação: O idioma escolhido no dropdown não pode ser igual ao idioma fixo (Comum) nem igual a outro idioma que o personagem já possua.

Limite de Slots: Verificar se o número de idiomas submetidos bate com a regra 1 (Comum) + 1 (Antecedente) + N (Classe/Talentos).


## Passo 11: Detalhes Finais
Esta fase final é crucial, pois transforma os atributos brutos em números que o jogador usará diretamente no combate e na exploração. Como você solicitou, preparei a estrutura da tela e as lógicas de cálculo integradas ao Passo 11.

---

### Estrutura da Tela: Passo 11 - Detalhes Finais

A tela deve ser dividida em duas colunas ou seções claras: **Cálculos Automáticos (Revisão)** e **Dados Pessoais (Inputs)**.

#### 1. Seção de Números e Atributos (Preenchimento Automático)

O sistema deve calcular estes valores com base na Classe, Raça e Atributos escolhidos nos passos anteriores. O usuário apenas visualiza ou confirma.

| Campo | Tipo de Dado | Lógica de Cálculo |
| --- | --- | --- |
| **Pontos de Vida (PV)** | Numérico (Read-only) | Base da Classe + Modificador de Constituição. |
| **Classe de Armadura (CA)** | Numérico (Read-only) | 10 + Mod. de Destreza (ajustar se houver armadura no inventário). |
| **Iniciativa** | Numérico (Read-only) | Igual ao Modificador de Destreza. |
| **Percepção Passiva** | Numérico (Read-only) | 10 + Mod. Sabedoria (+ Bônus Proficiência se tiver a perícia). |
| **Salvaguardas** | Lista/Numérico | Mod. do Atributo (+ Bônus Proficiência nas salvaguardas da classe). |
| **Perícias** | Lista/Numérico | Mod. do Atributo (+ Bônus Proficiência se for treinado). |
| **Dados de Vida** | Texto (Ex: 1d10) | Definido pela Classe (Nível 1 sempre tem 1 dado). |

---

#### 2. Formulário de Detalhes (Entrada do Usuário)

Aqui estão os campos que o usuário deve interagir para finalizar o personagem.

##### **Identidade e Estética**

* **Nome do Personagem:** `Input (Text)` - Obrigatório.
* **Alinhamento:** `Select (Dropdown)` - Carregado da tabela `alignment` que traduzimos.
* **Imagem do Personagem:** `File Input` - Campo para upload de arquivo (JPG/PNG).
* **Gênero:** `Input (Text)` ou `Select`.

##### **História e Personalidade (Textareas)**

Para as perguntas de lore, o ideal é agrupar em blocos para não sobrecarregar o usuário:

* **Aparência e Personalidade:** `Textarea` - Foco no físico e comportamento.
* **Vínculos e Relacionamentos:** `Textarea` - (Quem importa? Amigo de infância? Paixão?).
* **Origem e Motivação:** `Textarea` - (Quem te criou? O que te inspira a aventurar? Medos?).
* **Organizações:** `Input (Text)` - Guildas ou religiões.

---

#### 3. Lógica de Conjuração (Se aplicável)

Se a classe escolhida tiver a característica **Conjuração**, o sistema deve exibir:

* **CD de Resistência:** 8 + Mod. Atributo de Conjuração + Bônus de Proficiência.
* **Bônus de Ataque Mágico:** Mod. Atributo de Conjuração + Bônus de Proficiência.

---

#### Implementação em Markdown (Para Documentação/Frontend)

Abaixo, o código formatado para o seu sistema:

```markdown
### Tela: Detalhes Finais e Cálculos

#### 1. Painel de Status (Somente Leitura)
- **Vida Máxima:** [Cálculo: Vida Inicial Classe + Mod. CON]
- **CA:** [Cálculo: 10 + Mod. DES]
- **Iniciativa:** [Cálculo: Mod. DES]
- **Percepção Passiva:** [Cálculo: 10 + Mod. SAB + (Proficiência em Percepção?)]
- **Bônus de Proficiência:** +2 (Fixo para Nível 1)

#### 2. Formulário de Identidade
| Campo | Tipo | Origem/Regra |
| :--- | :--- | :--- |
| Nome do Personagem | Input | Usuário |
| Alinhamento | Dropdown | Tabela `alignment` |
| Imagem | Upload | Arquivo local |
| Lore / Notas | Textarea | Usuário |

#### 3. Perguntas de Background (Textareas Individuais ou Unificadas)
- Descreva sua aparência e personalidade.
- Quais são seus maiores medos e motivações?
- Detalhes da sua infância e relacionamentos (Amigos, família, pets).

#### 4. Ações de Finalização
- [Botão: Salvar e Gerar Ficha] -> Valida campos nulos -> `status = 'completo'` -> Redirect para `character_show`.

```

### Exemplo de Cálculo Automático (Pseudo-código)

Se o usuário é um **Guerreiro** com **Constituição 16** (Modificador +3):

* `PV_Max = 10 (Guerreiro) + 3 (Mod. CON) = 13`.
* `Iniciativa = Mod. DES`.
* `Salvaguardas Proficientes = Força e Constituição` (Adiciona +2 de proficiência nestes dois).

---

**Gostaria que eu montasse o script SQL para inserir as colunas de Lore e Imagem na tabela de personagens ou prefere focar na lógica de validação do formulário?**