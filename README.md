# Projeto D&D Character Sheet (Clonagem & Criação de Personagem)

Este projeto é um sistema completo para criação e gerenciamento de fichas de personagem de Dungeons & Dragons (2024), construído sobre **Symfony 7.2**, **Tailwind CSS** e **Alpine.js**.

O sistema foi desenhado para separar o conteúdo de regras (importado de fontes externas como Open5e/SRD) das instâncias de personagens criadas pelos usuários, garantindo integridade e atualizações seguras.

## 📚 Documentação

A documentação detalhada do projeto encontra-se na pasta `docs/`:

- [Blueprint do Sistema](docs/blueprint_clone_sistema.md): Arquitetura, stack técnica e guia de estilo.
- [Entidades & Arquitetura de Dados](docs/entidades.md): Modelo de dados, estratégia de importação e separação entre Regras vs. Personagens.
- [Guia de Criação de Personagem](docs/criacao-de-personagem.md): O fluxo passo-a-passo (Wizard) para criação de personagens.
- [Especificação da Ficha](docs/ficha-de-personagem.md): Detalhes visuais e funcionais da ficha de personagem.
- [Sistema de Importação](docs/import.md): Documentação técnica do comando `app:rules:import`.

## 🚀 Comandos do Sistema (CLI)

O projeto inclui diversos comandos customizados para facilitar a configuração, importação de dados e gestão do sistema. Abaixo a explicação de cada um:

### 1. Criar Usuário Admin
**Comando:** `app:admin-user`
**Uso:** `php bin/console app:admin-user [email] [password]`

Cria um novo usuário com permissões de administrador (`ROLE_ADMIN`) ou promove um existente. Essencial para o primeiro acesso ao painel administrativo.
- Se não forem passados argumentos, o comando solicitará interativamente.

### 2. Importar Regras (Core)
**Comando:** `app:rules:import`
**Uso:** `php bin/console app:rules:import --source=open5e --dataset=repo --entity=all`

Este é o comando principal para manter o banco de dados de regras atualizado. Ele suporta importação incremental, idempotência (não duplica dados) e múltiplas fontes.

**Opções Principais:**
- `--source`: Identificador da fonte (ex: `open5e`, `srd-5-2`).
- `--dataset`: Tipo de dataset (`repo`, `api`, `file`).
- `--entity`: Tipo de entidade a importar (`all`, `spells`, `classes`, etc.).
- `--mode`: Modo de importação (`incremental` ou `full`).
- `--chunk`: Tamanho do lote para gravação no banco (padrão 200).

Este comando popula tabelas como `ClassDef`, `Spell`, `Species`, `Background`, etc.

### 3. Semear Dados de Referência
**Comando:** `app:seed:reference-data`
**Uso:** `php bin/console app:seed:reference-data`

Popula o banco com dados estáticos e fundamentais que raramente mudam, mas são necessários para o funcionamento do sistema.
- **Alinhamentos**: (ex: Lawful Good, Chaotic Evil).
- **Idiomas**: (ex: Common, Elvish, Draconic) com seus scripts e falantes típicos.
- **Tabela de Evolução (Level Up)**: XP necessário e Bônus de Proficiência para os níveis 1 a 20.

### 4. Importar Bugigangas (Trinkets)
**Comando:** `app:import-trinkets`
**Uso:** `php bin/console app:import-trinkets`

Lê e importa a lista de bugigangas do arquivo local `docs/bugigangas.md`.
- Analisa o arquivo markdown.
- Associa as bugigangas à fonte de regras 'Livro do Jogador (PT-BR)'.
- Persiste os dados na tabela `Trinket`.

### 5. Semear Bugigangas (Alternativo)
**Comando:** `app:seed:trinkets`
**Uso:** `php bin/console app:seed:trinkets`

65: Uma alternativa ao comando de importação acima, que popula as bugigangas a partir de uma lista hardcoded (fixa no código) em vez de ler do arquivo markdown. Útil para inicialização rápida sem dependência de arquivos externos.
66: 
67: ### 6. Sistema de Backup
68: O sistema possui um mecanismo robusto de backup e restauração que suporta tanto MySQL quanto SQLite.
69: 
70: #### Fazer Backup
71: **Comando:** `app:database:backup`
72: **Uso:** `php bin/console app:database:backup`
73: 
74: Exporta todas as tabelas do banco de dados para um arquivo SQL na pasta `sql/backups`. O arquivo é nomeado com a data atual (ex: `backup_2025-12-23.sql`).
75: 
76: #### Restaurar Backup
77: **Comando:** `app:database:restore`
78: **Uso:** `php bin/console app:database:restore [arquivo]`
79: 
80: Restaura o banco de dados a partir de um arquivo de backup.
81: - O argumento `arquivo` pode ser o nome do arquivo em `sql/backups` ou um caminho absoluto.
82: - **Segurança:** O comando cria automaticamente um "backup de segurança" (estado atual) antes de iniciar a restauração.
83: - **Limpeza:** O banco de dados é limpo (drop/recreate no MySQL ou recriação do arquivo no SQLite) antes da importação para garantir integridade.
84: 
85: #### Testar Backup
86: **Comando:** `app:database:test-backup`
87: **Uso:** `php bin/console app:database:test-backup`
88: 
89: Executa um ciclo completo de teste e verificação:
90: 1. Gera um novo backup.
91: 2. Identifica o arquivo gerado.
92: 3. Executa a restauração usando esse arquivo.
93: Útil para garantir que o sistema de backup está funcionado corretamente (ex: crons, CI/CD).
94: 

## 🛠️ Instalação e Configuração

1. **Instalar Dependências:**
   ```bash
   composer install
   npm install
   ```

2. **Configurar Banco de Dados:**
   Ajuste o arquivo `.env.local` com suas credenciais de banco.
   ```bash
   # Exemplo
   DATABASE_URL="postgresql://db_user:db_password@127.0.0.1:5432/db_name?serverVersion=16&charset=utf8"
   ```

3. **Criar Banco e Schema:**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

4. **Compilar Assets:**
   ```bash
   npm run build
   ```

5. **Popular o Banco (Ordem Recomendada):**
   ```bash
   php bin/console app:seed:reference-data
   php bin/console app:admin-user admin@example.com senha123
   php bin/console app:rules:import --source=open5e ... (conforme necessidade)
   ```

## Local GPT (LLM) Integration

This project supports connecting to a local Large Language Model (like **Ollama**) to perform translations without relying on the paid OpenAI API.

### 1. Setup Local Server
The local GPT environment is located in `/Volumes/Dados/work/gpt-local`.

**Start the Server:**
```bash
cd /Volumes/Dados/work/gpt-local
./start_gpt.sh
```
Keep this terminal window open.

### 2. Manage Models
Use the helper script to download or list models:
```bash
cd /Volumes/Dados/work/gpt-local
# Download a model
./manage_models.sh pull llama3
./manage_models.sh pull mistral

# List installed models
./manage_models.sh list
```

### 3. Connect Project to Local GPT
Check your `.env.local` file and set:
```dotenv
OPENAI_BASE_URL=http://localhost:11434/v1
OPENAI_API_KEY=sk-dummy  # Required value, but ignored by local LLMs
```

### 4. Benchmarking Models
To compare speed and translation quality between different models, use the benchmark command:
```bash
php bin/console app:benchmark:models --models=llama3,mistral,gemma:2b
```
This command picks a **random spell** from the database and runs it through each model specified, displaying a comparison table.
