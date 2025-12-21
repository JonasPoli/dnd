<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221035624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rule_condition (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', is_active TINYINT(1) DEFAULT 1 NOT NULL, rule_slug VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, description_md LONGTEXT DEFAULT NULL, INDEX IDX_627A9B636F972CB7 (rules_source_id), UNIQUE INDEX UNIQ_SOURCE_CONDITION (rules_source_id, rule_slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rule_condition ADD CONSTRAINT FK_627A9B636F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');
        $this->addSql('ALTER TABLE `condition` DROP FOREIGN KEY FK_BDD688436F972CB7');
        $this->addSql('DROP TABLE `condition`');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `condition` (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', is_active TINYINT(1) DEFAULT 1 NOT NULL, rule_slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description_md LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_BDD688436F972CB7 (rules_source_id), UNIQUE INDEX UNIQ_SOURCE_CONDITION (rules_source_id, rule_slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE `condition` ADD CONSTRAINT FK_BDD688436F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE rule_condition DROP FOREIGN KEY FK_627A9B636F972CB7');
        $this->addSql('DROP TABLE rule_condition');
    }
}
