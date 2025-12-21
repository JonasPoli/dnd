<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221054558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE subrace (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', species_id INT NOT NULL, rule_slug VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, description_md LONGTEXT DEFAULT NULL, asi_description LONGTEXT DEFAULT NULL, asi JSON DEFAULT NULL, traits LONGTEXT DEFAULT NULL, INDEX IDX_3DAC9246F972CB7 (rules_source_id), INDEX IDX_3DAC924B2A1D860 (species_id), UNIQUE INDEX UNIQ_SPECIES_KEY (species_id, rule_slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE subrace ADD CONSTRAINT FK_3DAC9246F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');
        $this->addSql('ALTER TABLE subrace ADD CONSTRAINT FK_3DAC924B2A1D860 FOREIGN KEY (species_id) REFERENCES species (id)');
        $this->addSql('ALTER TABLE species ADD asi_description LONGTEXT DEFAULT NULL, ADD asi JSON DEFAULT NULL, ADD age LONGTEXT DEFAULT NULL, ADD alignment LONGTEXT DEFAULT NULL, ADD speed_description LONGTEXT DEFAULT NULL, ADD languages LONGTEXT DEFAULT NULL, ADD vision LONGTEXT DEFAULT NULL, ADD traits LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subrace DROP FOREIGN KEY FK_3DAC9246F972CB7');
        $this->addSql('ALTER TABLE subrace DROP FOREIGN KEY FK_3DAC924B2A1D860');
        $this->addSql('DROP TABLE subrace');
        $this->addSql('ALTER TABLE species DROP asi_description, DROP asi, DROP age, DROP alignment, DROP speed_description, DROP languages, DROP vision, DROP traits');
    }
}
