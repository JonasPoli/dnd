# Sistema de backup
Este sistema deve ter um sistema de backup para o banco de dados em um command.

## Fazer backup
O backup deve ser feito exportando todas as tabelas do banco de dados para um arquivo sql.
O arquivo deve ter o nome backup_YYYY-MM-DD.sql e ficar na pasta /sql/backups
Esta parta deve estar no gitignore

## Restaurar backup
O backup deve ser restaurado importando o arquivo sql para o banco de dados.

O command deve ter um argumento para o arquivo sql
A rotina de voltar o banco de dados deve, antes de restaurar um backup, fazer um backup do banco de dados atual, depois limpar o banco de dados e restaurar o backup.

## Testes
Depois de criar o command, você deve criar um mecanismo de testar o backup e restaurar o backup.