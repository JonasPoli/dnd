<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229035740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment DROP FOREIGN KEY FK_D338D5836F972CB7');
        $this->addSql('DROP INDEX UNIQ_SOURCE_KEY ON equipment');
        $this->addSql('DROP INDEX IDX_D338D5836F972CB7 ON equipment');
        $this->addSql('ALTER TABLE equipment DROP rules_source_id, DROP rule_slug');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment ADD rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', ADD rule_slug VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_D338D5836F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_SOURCE_KEY ON equipment (rules_source_id, rule_slug)');
        $this->addSql('CREATE INDEX IDX_D338D5836F972CB7 ON equipment (rules_source_id)');
    }
}
