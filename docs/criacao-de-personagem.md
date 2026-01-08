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