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

Uma alternativa ao comando de importação acima, que popula as bugigangas a partir de uma lista hardcoded (fixa no código) em vez de ler do arquivo markdown. Útil para inicialização rápida sem dependência de arquivos externos.

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
