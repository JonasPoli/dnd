<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229030616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE species DROP FOREIGN KEY FK_A50FF7126F972CB7');
        $this->addSql('DROP INDEX IDX_A50FF7126F972CB7 ON species');
        $this->addSql('ALTER TABLE species DROP rules_source_id, DROP asi');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE species ADD rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', ADD asi JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE species ADD CONSTRAINT FK_A50FF7126F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_A50FF7126F972CB7 ON species (rules_source_id)');
    }
}
