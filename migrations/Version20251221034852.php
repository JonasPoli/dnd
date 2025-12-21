<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221034852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE monster (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', is_active TINYINT(1) DEFAULT 1 NOT NULL, rule_slug VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, size VARCHAR(50) DEFAULT NULL, type VARCHAR(100) DEFAULT NULL, alignment VARCHAR(100) DEFAULT NULL, challenge_rating VARCHAR(10) DEFAULT NULL, armor_class INT DEFAULT NULL, hit_points INT DEFAULT NULL, hit_dice VARCHAR(50) DEFAULT NULL, strength INT DEFAULT NULL, dexterity INT DEFAULT NULL, constitution INT DEFAULT NULL, intelligence INT DEFAULT NULL, wisdom INT DEFAULT NULL, charisma INT DEFAULT NULL, speed_json JSON DEFAULT NULL, skills_json JSON DEFAULT NULL, senses VARCHAR(255) DEFAULT NULL, languages VARCHAR(255) DEFAULT NULL, special_abilities_json JSON DEFAULT NULL, actions_json JSON DEFAULT NULL, legendary_actions_json JSON DEFAULT NULL, description_md LONGTEXT DEFAULT NULL, src_json JSON DEFAULT NULL, INDEX IDX_245EC6F46F972CB7 (rules_source_id), UNIQUE INDEX UNIQ_SOURCE_MONSTER (rules_source_id, rule_slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE monster ADD CONSTRAINT FK_245EC6F46F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE monster DROP FOREIGN KEY FK_245EC6F46F972CB7');
        $this->addSql('DROP TABLE monster');
    }
}
