<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229033223 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feat DROP FOREIGN KEY FK_5A9B91CB6F972CB7');
        $this->addSql('DROP INDEX UNIQ_SOURCE_FEAT ON feat');
        $this->addSql('DROP INDEX IDX_5A9B91CB6F972CB7 ON feat');
        $this->addSql('ALTER TABLE feat DROP rules_source_id, DROP rule_slug');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feat ADD rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', ADD rule_slug VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE feat ADD CONSTRAINT FK_5A9B91CB6F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_SOURCE_FEAT ON feat (rules_source_id, rule_slug)');
        $this->addSql('CREATE INDEX IDX_5A9B91CB6F972CB7 ON feat (rules_source_id)');
    }
}
