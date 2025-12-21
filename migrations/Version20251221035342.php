<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221035342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `condition` (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', is_active TINYINT(1) DEFAULT 1 NOT NULL, rule_slug VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, description_md LONGTEXT DEFAULT NULL, INDEX IDX_BDD688436F972CB7 (rules_source_id), UNIQUE INDEX UNIQ_SOURCE_CONDITION (rules_source_id, rule_slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE feat (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', is_active TINYINT(1) DEFAULT 1 NOT NULL, rule_slug VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, prerequisite LONGTEXT DEFAULT NULL, description_md LONGTEXT DEFAULT NULL, INDEX IDX_5A9B91CB6F972CB7 (rules_source_id), UNIQUE INDEX UNIQ_SOURCE_FEAT (rules_source_id, rule_slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE magic_item (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', is_active TINYINT(1) DEFAULT 1 NOT NULL, rule_slug VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(100) DEFAULT NULL, rarity VARCHAR(100) DEFAULT NULL, requires_attunement VARCHAR(255) DEFAULT NULL, description_md LONGTEXT DEFAULT NULL, INDEX IDX_42F70D296F972CB7 (rules_source_id), UNIQUE INDEX UNIQ_SOURCE_MAGICITEM (rules_source_id, rule_slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rule_section (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', is_active TINYINT(1) DEFAULT 1 NOT NULL, rule_slug VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, content_md LONGTEXT DEFAULT NULL, INDEX IDX_DDE7435B6F972CB7 (rules_source_id), UNIQUE INDEX UNIQ_SOURCE_SECTION (rules_source_id, rule_slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `condition` ADD CONSTRAINT FK_BDD688436F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');
        $this->addSql('ALTER TABLE feat ADD CONSTRAINT FK_5A9B91CB6F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');
        $this->addSql('ALTER TABLE magic_item ADD CONSTRAINT FK_42F70D296F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');
        $this->addSql('ALTER TABLE rule_section ADD CONSTRAINT FK_DDE7435B6F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `condition` DROP FOREIGN KEY FK_BDD688436F972CB7');
        $this->addSql('ALTER TABLE feat DROP FOREIGN KEY FK_5A9B91CB6F972CB7');
        $this->addSql('ALTER TABLE magic_item DROP FOREIGN KEY FK_42F70D296F972CB7');
        $this->addSql('ALTER TABLE rule_section DROP FOREIGN KEY FK_DDE7435B6F972CB7');
        $this->addSql('DROP TABLE `condition`');
        $this->addSql('DROP TABLE feat');
        $this->addSql('DROP TABLE magic_item');
        $this->addSql('DROP TABLE rule_section');
    }
}
