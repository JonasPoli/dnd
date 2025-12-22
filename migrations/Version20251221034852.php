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
        $this->addSql('CREATE TABLE rules_source (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', slug VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, license VARCHAR(255) DEFAULT NULL, version_label VARCHAR(50) DEFAULT NULL, origin_url LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_RULES_SOURCE_SLUG (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        $this->addSql('CREATE TABLE monster (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', is_active TINYINT(1) DEFAULT 1 NOT NULL, rule_slug VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, size VARCHAR(50) DEFAULT NULL, type VARCHAR(100) DEFAULT NULL, alignment VARCHAR(100) DEFAULT NULL, challenge_rating VARCHAR(10) DEFAULT NULL, armor_class INT DEFAULT NULL, hit_points INT DEFAULT NULL, hit_dice VARCHAR(50) DEFAULT NULL, strength INT DEFAULT NULL, dexterity INT DEFAULT NULL, constitution INT DEFAULT NULL, intelligence INT DEFAULT NULL, wisdom INT DEFAULT NULL, charisma INT DEFAULT NULL, speed_json JSON DEFAULT NULL, skills_json JSON DEFAULT NULL, senses VARCHAR(255) DEFAULT NULL, languages VARCHAR(255) DEFAULT NULL, special_abilities_json JSON DEFAULT NULL, actions_json JSON DEFAULT NULL, legendary_actions_json JSON DEFAULT NULL, description_md LONGTEXT DEFAULT NULL, src_json JSON DEFAULT NULL, INDEX IDX_MONSTER_SOURCE (rules_source_id), UNIQUE INDEX UNIQ_SOURCE_MONSTER (rules_source_id, rule_slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE monster ADD CONSTRAINT FK_MONSTER_SOURCE FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');

        $this->addSql('CREATE TABLE spell (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', is_active TINYINT(1) DEFAULT 1 NOT NULL, rule_slug VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, level INT NOT NULL, school VARCHAR(100) NOT NULL, casting_time VARCHAR(255) NOT NULL, spell_range VARCHAR(100) NOT NULL, components_json JSON DEFAULT NULL, duration VARCHAR(100) DEFAULT NULL, description_md LONGTEXT NOT NULL, higher_levels_md LONGTEXT DEFAULT NULL, INDEX IDX_SPELL_SOURCE (rules_source_id), UNIQUE INDEX UNIQ_SOURCE_SPELL (rules_source_id, rule_slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE spell ADD CONSTRAINT FK_SPELL_SOURCE FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');

        $this->addSql('CREATE TABLE species (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', is_active TINYINT(1) DEFAULT 1 NOT NULL, rule_slug VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, size VARCHAR(255) NOT NULL, speed_walk INT NOT NULL, description_md LONGTEXT DEFAULT NULL, INDEX IDX_SPECIES_SOURCE (rules_source_id), UNIQUE INDEX UNIQ_SOURCE_SPECIES (rules_source_id, rule_slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE species ADD CONSTRAINT FK_SPECIES_SOURCE FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');

        $this->addSql('CREATE TABLE language (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(100) NOT NULL, script VARCHAR(100) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, `key` VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_LANGUAGE_KEY (`key`), INDEX IDX_LANGUAGE_SOURCE (rules_source_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE language ADD CONSTRAINT FK_LANGUAGE_SOURCE FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');

        $this->addSql('CREATE TABLE background (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', is_active TINYINT(1) DEFAULT 1 NOT NULL, rule_slug VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, description_md LONGTEXT DEFAULT NULL, grants_json JSON DEFAULT NULL, INDEX IDX_BACKGROUND_SOURCE (rules_source_id), UNIQUE INDEX UNIQ_SOURCE_BACKGROUND (rules_source_id, rule_slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE background ADD CONSTRAINT FK_BACKGROUND_SOURCE FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');

        $this->addSql('CREATE TABLE equipment (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', is_active TINYINT(1) DEFAULT 1 NOT NULL, rule_slug VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(50) NOT NULL, cost_gp NUMERIC(10, 2) DEFAULT NULL, weight_lb NUMERIC(10, 2) DEFAULT NULL, properties_json JSON DEFAULT NULL, description_md LONGTEXT DEFAULT NULL, INDEX IDX_EQUIPMENT_SOURCE (rules_source_id), UNIQUE INDEX UNIQ_SOURCE_EQUIPMENT (rules_source_id, rule_slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE equipment ADD CONSTRAINT FK_EQUIPMENT_SOURCE FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');

        $this->addSql('CREATE TABLE class_def (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', is_active TINYINT(1) DEFAULT 1 NOT NULL, rule_slug VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, hit_die INT NOT NULL, description_md LONGTEXT DEFAULT NULL, primary_abilities JSON DEFAULT NULL, saving_throw_proficiencies JSON DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_CLASSDEF_SOURCE (rules_source_id), UNIQUE INDEX UNIQ_SOURCE_CLASSDEF (rules_source_id, rule_slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE class_def ADD CONSTRAINT FK_CLASSDEF_SOURCE FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE monster DROP FOREIGN KEY FK_MONSTER_SOURCE');
        $this->addSql('ALTER TABLE spell DROP FOREIGN KEY FK_SPELL_SOURCE');
        $this->addSql('ALTER TABLE species DROP FOREIGN KEY FK_SPECIES_SOURCE');
        $this->addSql('ALTER TABLE language DROP FOREIGN KEY FK_LANGUAGE_SOURCE');
        $this->addSql('ALTER TABLE background DROP FOREIGN KEY FK_BACKGROUND_SOURCE');
        $this->addSql('ALTER TABLE equipment DROP FOREIGN KEY FK_EQUIPMENT_SOURCE');
        $this->addSql('ALTER TABLE class_def DROP FOREIGN KEY FK_CLASSDEF_SOURCE');
        
        $this->addSql('DROP TABLE monster');
        $this->addSql('DROP TABLE spell');
        $this->addSql('DROP TABLE species');
        $this->addSql('DROP TABLE language');
        $this->addSql('DROP TABLE background');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('DROP TABLE class_def');
        $this->addSql('DROP TABLE rules_source');
    }
}
