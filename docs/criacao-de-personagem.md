# Criação de Personagem — Dungeons & Dragons 2024
## Guia Completo Estruturado como Sistema

Este documento descreve **todo o processo de criação de personagem no D&D 2024**, organizado como se cada etapa fosse um **formulário de um sistema digital**, com campos, tipos e valores possíveis.

---
Cada passo é um formulário que deve ser preenchido para continuar para o próximo passo.
Cada um dos campos a serem preenchidos deve ser preenchido de acordo com as opções possíveis, verificando se por algum acaso, algums dos campos não é um select que o usuário deverá escolher dentre os itens cadastrados.

Para entender a mecânica de como o sistema funciona, veja o arquivo docs/livro do jogdor 2024.pdf.
Alguns dos dados são escolhidos pelo usuário, outros são calculados automaticamente com base em uma tabela. Entenda os campos calculados, qual a forma de calcular e quais as regras que devem ser seguidas e preveja as formulas de calculo.

## PASSO 1 — Conceito do Personagem

### Objetivo
Definir a identidade inicial do personagem.

### Campos
- **Nome do Personagem** (texto)
- **Conceito / Arquétipo** (texto livre)
- **Personalidade Geral** (texto)
- **Alinhamento Moral** deve ser uma das opções de alignment

---

## PASSO 2 — Espécie

### Objetivo
Definir características biológicas e culturais.

### Campo
- **Espécie** (Raças e Traços)

### Valores Possíveis
- Aasimar  
- Anão  
- Draconato  
- Elfo  
- Gnomo  
- Golias  
- Humano  
- Orc  
- Pequenino  
- Tiferino  

### Campos Derivados
- Traços raciais
- Velocidade
- Tipo de visão
- Habilidades especiais

---

## PASSO 3 — Antecedente

### Objetivo
Definir a vida do personagem antes da aventura.

### Campo
- **Antecedente** (seleção única)

### Valores Possíveis
- Acólito  
- Andarilho  
- Artesão  
- Artista  
- Charlatão  
- Criminoso  
- Eremita  
- Escriba  
- Fazendeiro  
- Guarda  
- Guia  
- Marinheiro  
- Mercador  
- Nobre  
- Sábio  
- Soldado  

### Campos Derivados
- Proficiências em perícias
- Proficiências em ferramentas
- Idiomas adicionais
- Equipamentos iniciais
- Talento de Origem

---

## PASSO 4 — Classe

### Objetivo
Definir o papel mecânico do personagem no jogo.

### Campo
- **Classe** (seleção única)

### Valores Possíveis
- Bárbaro  
- Bardo  
- Bruxo  
- Clérigo  
- Druida  
- Feiticeiro  
- Guardião  
- Guerreiro  
- Ladino  
- Mago  
- Monge  
- Paladino  

### Campos Derivados
- Dados de vida
- Proficiências em armas
- Proficiências em armaduras
- Salvaguardas proficientes
- Habilidades de classe (nível 1)

---

## PASSO 5 — Atributos

### Objetivo
Definir capacidades físicas e mentais.

### Campos Numéricos
- Força
- Destreza
- Constituição
- Inteligência
- Sabedoria
- Carisma

### Métodos Possíveis
- Valores padrão
- Compra por pontos
- Rolagem de dados

### Campos Derivados
- Modificadores de atributo
- Pontos de vida iniciais
- Classe de Armadura base
- CDs de habilidades

---

## PASSO 6 — Proficiências

### Objetivo
Definir treinamentos do personagem.

### Campos
- **Perícias** (múltipla escolha)
- **Armas** (automático pela classe)
- **Armaduras** (automático pela classe)
- **Ferramentas** (classe + antecedente)
- **Salvaguardas** (automático pela classe)

---

## PASSO 7 — Talentos

### Objetivo
Adicionar habilidades especiais.

### Campos
- **Talento de Origem** (obrigatório)
- **Talentos adicionais** (se aplicável)

### Categorias
- Talentos de Origem
- Talentos Gerais
- Talentos de Estilo de Luta
- Talentos de Dádiva Épica (níveis altos)

---

## PASSO 8 — Magias (Condicional)

### Objetivo
Definir capacidades mágicas.

### Campos
- Classe conjuradora
- Atributo de conjuração
- Truques conhecidos
- Magias conhecidas ou preparadas
- Slots de magia

> Este passo só se aplica a classes conjuradoras.

---

## PASSO 9 — Equipamento Inicial

### Objetivo
Definir recursos iniciais.

### Campos
- Armas iniciais
- Armaduras
- Equipamentos de aventura
- Moedas

### Origem dos Dados
- Classe
- Antecedente

---

## PASSO 10 — Estatísticas Finais

### Objetivo
Consolidar todos os valores do personagem.

### Campos Calculados
- Pontos de Vida
- Classe de Armadura
- Bônus de Proficiência
- Iniciativa
- Deslocamento
- CDs de salvaguarda
- Ataques

---

## PASSO 11 — Detalhes Narrativos

### Objetivo
Completar a identidade do personagem.

### Campos
- Aparência física
- Traços de personalidade
- Ideais
- Vínculos
- Defeitos
- História resumida

---

## PASSO 12 — Finalização

### Objetivo
Validar e salvar o personagem.

### Campos
- Nome final
- Nível inicial
- Campanha
- Jogador responsável

---

## Estrutura Geral (Resumo Técnico)

```text
Personagem
 ├─ Conceito
 ├─ Espécie
 ├─ Antecedente
 ├─ Classe
 ├─ Atributos
 ├─ Proficiências
 ├─ Talentos
 ├─ Magias (opcional)
 ├─ Equipamentos
 ├─ Estatísticas calculadas
 └─ Narrativa
