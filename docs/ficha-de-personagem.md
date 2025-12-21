# Ficha de Personagem — Dungeons & Dragons 2024  
**Especificação Técnica e Visual Completa**

Este documento descreve **minuciosamente** a estrutura, o funcionamento e a apresentação visual da **Ficha Oficial de Personagem D&D 2024 (PT-BR)**, com o objetivo de permitir sua **recriação fiel em formato digital** (web/app), preservando tanto a **mecânica de jogo** quanto a **experiência visual e conceitual** da ficha física.

---

## 1. Cabeçalho — Identidade do Personagem

### Função
Identificar o personagem e estabelecer o vínculo entre **narrativa** e **regras**.

### Campos
- Nome do Personagem  
- Origem (Background / Origem narrativa)  
- Espécie  
- Classe  
- Subclasse  
- Nível  
- Experiência (EXP)

### Apresentação Visual
- Disposição horizontal no topo da ficha  
- Campos retangulares com linhas para preenchimento  
- O campo **Nível** destacado visualmente (círculo)  

---

## 2. Bloco Central — Combate e Sobrevivência

### 2.1 Classe de Armadura (CA)

**Campos**
- Classe de Armadura  
- Escudo (indicador visual / checkbox)

**Função**
- Define a dificuldade para ser atingido por ataques.

---

### 2.2 Pontos de Vida

**Campos**
- Pontos de Vida Máximos  
- Pontos de Vida Atuais  
- Pontos de Vida Temporários  
- Dados de Vida:
  - Total disponível  
  - Dados gastos  

**Função**
- Controle de dano, descanso curto e descanso longo.

---

### 2.3 Testes de Resistência à Morte

**Campos**
- Sucessos (3 marcadores)  
- Falhas (3 marcadores)

**Função**
- Estado crítico quando o personagem chega a 0 PV.

---

### Apresentação Visual
- Blocos destacados  
- Uso de símbolos gráficos (diamantes/estrelas)  
- Campos numéricos grandes e de fácil leitura  

---

## 3. Atributos e Salvaguardas

### Atributos Principais (6)

- Força  
- Destreza  
- Constituição  
- Inteligência  
- Sabedoria  
- Carisma  

Para **cada atributo**:
- Valor  
- Modificador  
- Salvaguarda (indicador de proficiência)

---

### Perícias (Skills)

Cada perícia contém:
- Nome  
- Checkbox de proficiência  
- Associação implícita com um atributo  

**Lista Completa**
- Atletismo  
- Acrobacia  
- Prestidigitação  
- Furtividade  
- Arcanismo  
- História  
- Investigação  
- Natureza  
- Religião  
- Lidar com Animais  
- Intuição  
- Medicina  
- Percepção  
- Sobrevivência  
- Enganação  
- Intimidação  
- Atuação  
- Persuasão  

### Apresentação Visual
- Coluna lateral  
- Atributos em “cartões” individuais  
- Perícias agrupadas por atributo  

---

## 4. Estatísticas Derivadas

### Campos
- Bônus de Proficiência  
- Iniciativa  
- Velocidade  
- Tamanho  
- Percepção Passiva  

### Função
- Estatísticas calculadas a partir de atributos, classe e proficiência  
- Usadas constantemente durante o jogo  

---

## 5. Armas & Truques de Dano

### Estrutura em Tabela

Cada linha representa uma opção de ataque.

**Colunas**
- Nome  
- Bônus de Ataque / CD  
- Dano & Tipo  
- Anotações  

### Função
- Registrar ataques físicos e mágicos  
- Centralizar dados usados em combate  

### Apresentação Visual
- Tabela ampla  
- Estilo de planilha  
- Espaço para múltiplas entradas  

---

## 6. Características Especiais

### 6.1 Características de Classe
- Habilidades da classe  
- Recursos especiais (ex.: Fúria, Canalizar Divindade)

### 6.2 Características Raciais
- Traços da espécie  
- Sentidos, resistências, habilidades inatas  

### 6.3 Talentos
- Feats adquiridos ao longo da progressão  

### Apresentação Visual
- Grandes caixas de texto  
- Fundo quadriculado  
- Espaço livre para escrita detalhada  

---

## 7. Inspiração Heróica

### Campo
- Indicador visual (estrela)

### Função
- Recurso narrativo e mecânico  
- Permite vantagem em testes  

---

## 8. Equipamento, Treino & Proficiências

### Proficiências
- Armaduras:
  - Leve  
  - Média  
  - Pesada  
  - Escudos  
- Armas  
- Ferramentas  

### Equipamento
- Lista textual livre de itens carregados  

---

## 9. Magia (Página 2)

### 9.1 Conjuração

**Campos**
- Atributo de Conjuração  
- Modificador de Conjuração  
- CD de Resistência de Magia  
- Bônus de Ataque de Magia  

---

### 9.2 Espaços de Magia

Para cada nível de magia (1 a 9):
- Total de espaços  
- Espaços gastos  

### Apresentação Visual
- Marcadores em forma de diamante  
- Agrupamento por nível de magia  

---

### 9.3 Truques & Magias Preparadas

**Tabela com colunas**
- Nível  
- Nome  
- Tempo de Conjuração  
- Alcance  
- Concentração / Ritual  
- Material Necessário  
- Anotações  

### Função
- Registro completo das magias conhecidas e preparadas  

---

## 10. Narrativa e Interpretação

### História & Personalidade
- Texto livre  
- Inclui:
  - Traços de personalidade  
  - Ideais  
  - Vínculos  
  - Defeitos  

### Aparência
- Descrição física do personagem  

### Idiomas
- Lista textual de idiomas conhecidos  

---

## 11. Itens Mágicos & Moedas

### Sintonização de Itens Mágicos
- Até 3 espaços de sintonização  

### Moedas
- CP — Cobre  
- PP — Prata  
- PE — Electrum  
- PO — Ouro  
- PL — Platina  

---

## Considerações Técnicas Importantes

- A ficha representa um **estado completo e jogável do personagem**  
- Deve suportar:
  - Valores base  
  - Valores derivados  
  - Recursos consumíveis  
- Combina:
  - Campos altamente estruturados  
  - Campos livres e narrativos  
- Deve permitir geração de:
  - Visual web  
  - PDF  
  - “Livro do personagem” narrativo  

**Esta ficha é simultaneamente um formulário, um painel de controle e um resumo das regras aplicáveis ao personagem.**
