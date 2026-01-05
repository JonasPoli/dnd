<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229023940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subrace DROP FOREIGN KEY FK_3DAC9246F972CB7');
        $this->addSql('DROP INDEX IDX_3DAC9246F972CB7 ON subrace');
        $this->addSql('DROP INDEX UNIQ_SPECIES_KEY ON subrace');
        $this->addSql('ALTER TABLE subrace DROP rules_source_id, DROP rule_slug, DROP asi_description, DROP asi, DROP traits');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subrace ADD rules_source_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD rule_slug VARCHAR(100) NOT NULL, ADD asi_description LONGTEXT DEFAULT NULL, ADD asi JSON DEFAULT NULL, ADD traits LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE subrace ADD CONSTRAINT FK_3DAC9246F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_3DAC9246F972CB7 ON subrace (rules_source_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_SPECIES_KEY ON subrace (species_id, rule_slug)');
    }
}
