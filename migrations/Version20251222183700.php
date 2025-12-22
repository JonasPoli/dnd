<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251222183700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ability (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(10) NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_35CFEE3C8A90ABA9 (`key`), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `character` (id INT AUTO_INCREMENT NOT NULL, class_def_id INT NOT NULL, subclass_def_id INT DEFAULT NULL, species_id INT NOT NULL, background_id INT NOT NULL, name VARCHAR(255) NOT NULL, level INT NOT NULL, alignment VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_937AB0343B64E297 (class_def_id), INDEX IDX_937AB034802025F7 (subclass_def_id), INDEX IDX_937AB034B2A1D860 (species_id), INDEX IDX_937AB034C93D69EA (background_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE character_ability_score (id INT AUTO_INCREMENT NOT NULL, character_id INT NOT NULL, ability_key VARCHAR(10) NOT NULL, score INT NOT NULL, INDEX IDX_64C201F71136BE75 (character_id), UNIQUE INDEX CHARACTER_ABILITY_UNIQ (character_id, ability_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE character_choice (id INT AUTO_INCREMENT NOT NULL, character_id INT NOT NULL, step_key VARCHAR(100) NOT NULL, choice_key VARCHAR(100) NOT NULL, value_json JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_62E999011136BE75 (character_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE character_feature (id INT AUTO_INCREMENT NOT NULL, character_id INT NOT NULL, feature_id INT NOT NULL, gained_at_level INT NOT NULL, INDEX IDX_7672F55B1136BE75 (character_id), INDEX IDX_7672F55B60E4B879 (feature_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE character_item (id INT AUTO_INCREMENT NOT NULL, character_id INT NOT NULL, equipment_id INT NOT NULL, qty INT NOT NULL, notes LONGTEXT DEFAULT NULL, INDEX IDX_8E731861136BE75 (character_id), INDEX IDX_8E73186517FE9FE (equipment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE character_proficiency (id INT AUTO_INCREMENT NOT NULL, character_id INT NOT NULL, type VARCHAR(50) NOT NULL, ref_key VARCHAR(100) NOT NULL, source_text VARCHAR(100) DEFAULT NULL, INDEX IDX_DBF98A461136BE75 (character_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE character_spell (id INT AUTO_INCREMENT NOT NULL, character_id INT NOT NULL, spell_id INT NOT NULL, learned_at_level INT NOT NULL, prepared TINYINT(1) NOT NULL, INDEX IDX_2EFC2AEF1136BE75 (character_id), INDEX IDX_2EFC2AEF479EC90D (spell_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE class_level (id INT AUTO_INCREMENT NOT NULL, class_def_id INT NOT NULL, level INT NOT NULL, proficiency_bonus INT NOT NULL, spell_slots_json JSON DEFAULT NULL, notes_md LONGTEXT DEFAULT NULL, INDEX IDX_7C3DED253B64E297 (class_def_id), UNIQUE INDEX UNIQ_CLASS_LEVEL (class_def_id, level), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE external_reference (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', entity_type VARCHAR(100) NOT NULL, external_id VARCHAR(255) NOT NULL, local_entity_id INT NOT NULL, content_hash VARCHAR(64) NOT NULL, last_imported_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', first_seen_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(20) NOT NULL, INDEX IDX_8AF8E6076F972CB7 (rules_source_id), UNIQUE INDEX UNIQ_SOURCE_TYPE_EXTID (rules_source_id, entity_type, external_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE feature (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', owner_type VARCHAR(50) NOT NULL, owner_id INT DEFAULT NULL, `key` VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, level_required INT DEFAULT NULL, description_md LONGTEXT NOT NULL, grants_json JSON DEFAULT NULL, INDEX IDX_1FD775666F972CB7 (rules_source_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE import_run (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', source VARCHAR(100) NOT NULL, mode VARCHAR(50) NOT NULL, started_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', finished_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', options_json JSON DEFAULT NULL, status VARCHAR(20) NOT NULL, INDEX IDX_C41B04406F972CB7 (rules_source_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE import_run_seen (id INT AUTO_INCREMENT NOT NULL, import_run_id INT NOT NULL, entity_type VARCHAR(100) NOT NULL, external_id VARCHAR(255) NOT NULL, INDEX IDX_9103EBC6F8D244DC (import_run_id), UNIQUE INDEX UNIQ_RUN_TYPE_EXTID (import_run_id, entity_type, external_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE skill (id INT AUTO_INCREMENT NOT NULL, ability_id INT NOT NULL, `key` VARCHAR(50) NOT NULL, name VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_5E3DE4778A90ABA9 (`key`), INDEX IDX_5E3DE4778016D8B2 (ability_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE spell_class_def (spell_id INT NOT NULL, class_def_id INT NOT NULL, INDEX IDX_9A8C1CBA479EC90D (spell_id), INDEX IDX_9A8C1CBA3B64E297 (class_def_id), PRIMARY KEY(spell_id, class_def_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subclass_def (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', class_def_id INT NOT NULL, rule_slug VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, available_from_level INT NOT NULL, description_md LONGTEXT DEFAULT NULL, INDEX IDX_EE2232A46F972CB7 (rules_source_id), INDEX IDX_EE2232A43B64E297 (class_def_id), UNIQUE INDEX UNIQ_CLASS_KEY (class_def_id, rule_slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trinket (id INT AUTO_INCREMENT NOT NULL, rules_source_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', roll_key INT NOT NULL, text_md LONGTEXT NOT NULL, INDEX IDX_871A1C866F972CB7 (rules_source_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `character` ADD CONSTRAINT FK_937AB0343B64E297 FOREIGN KEY (class_def_id) REFERENCES class_def (id)');
        $this->addSql('ALTER TABLE `character` ADD CONSTRAINT FK_937AB034802025F7 FOREIGN KEY (subclass_def_id) REFERENCES subclass_def (id)');
        $this->addSql('ALTER TABLE `character` ADD CONSTRAINT FK_937AB034B2A1D860 FOREIGN KEY (species_id) REFERENCES species (id)');
        $this->addSql('ALTER TABLE `character` ADD CONSTRAINT FK_937AB034C93D69EA FOREIGN KEY (background_id) REFERENCES background (id)');
        $this->addSql('ALTER TABLE character_ability_score ADD CONSTRAINT FK_64C201F71136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id)');
        $this->addSql('ALTER TABLE character_choice ADD CONSTRAINT FK_62E999011136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id)');
        $this->addSql('ALTER TABLE character_feature ADD CONSTRAINT FK_7672F55B1136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id)');
        $this->addSql('ALTER TABLE character_feature ADD CONSTRAINT FK_7672F55B60E4B879 FOREIGN KEY (feature_id) REFERENCES feature (id)');
        $this->addSql('ALTER TABLE character_item ADD CONSTRAINT FK_8E731861136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id)');
        $this->addSql('ALTER TABLE character_item ADD CONSTRAINT FK_8E73186517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id)');
        $this->addSql('ALTER TABLE character_proficiency ADD CONSTRAINT FK_DBF98A461136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id)');
        $this->addSql('ALTER TABLE character_spell ADD CONSTRAINT FK_2EFC2AEF1136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id)');
        $this->addSql('ALTER TABLE character_spell ADD CONSTRAINT FK_2EFC2AEF479EC90D FOREIGN KEY (spell_id) REFERENCES spell (id)');
        $this->addSql('ALTER TABLE class_level ADD CONSTRAINT FK_7C3DED253B64E297 FOREIGN KEY (class_def_id) REFERENCES class_def (id)');
        $this->addSql('ALTER TABLE external_reference ADD CONSTRAINT FK_8AF8E6076F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');
        $this->addSql('ALTER TABLE feature ADD CONSTRAINT FK_1FD775666F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');
        $this->addSql('ALTER TABLE import_run ADD CONSTRAINT FK_C41B04406F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');
        $this->addSql('ALTER TABLE import_run_seen ADD CONSTRAINT FK_9103EBC6F8D244DC FOREIGN KEY (import_run_id) REFERENCES import_run (id)');
        $this->addSql('ALTER TABLE skill ADD CONSTRAINT FK_5E3DE4778016D8B2 FOREIGN KEY (ability_id) REFERENCES ability (id)');
        $this->addSql('ALTER TABLE spell_class_def ADD CONSTRAINT FK_9A8C1CBA479EC90D FOREIGN KEY (spell_id) REFERENCES spell (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE spell_class_def ADD CONSTRAINT FK_9A8C1CBA3B64E297 FOREIGN KEY (class_def_id) REFERENCES class_def (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE subclass_def ADD CONSTRAINT FK_EE2232A46F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');
        $this->addSql('ALTER TABLE subclass_def ADD CONSTRAINT FK_EE2232A43B64E297 FOREIGN KEY (class_def_id) REFERENCES class_def (id)');
        $this->addSql('ALTER TABLE trinket ADD CONSTRAINT FK_871A1C866F972CB7 FOREIGN KEY (rules_source_id) REFERENCES rules_source (id)');
        $this->addSql('ALTER TABLE background RENAME INDEX idx_background_source TO IDX_BC68B4506F972CB7');
        $this->addSql('ALTER TABLE background RENAME INDEX uniq_source_background TO UNIQ_SOURCE_KEY');
        $this->addSql('ALTER TABLE class_def RENAME INDEX idx_classdef_source TO IDX_7A4D999D6F972CB7');
        $this->addSql('ALTER TABLE class_def RENAME INDEX uniq_source_classdef TO UNIQ_SOURCE_KEY');
        $this->addSql('ALTER TABLE equipment RENAME INDEX idx_equipment_source TO IDX_D338D5836F972CB7');
        $this->addSql('ALTER TABLE equipment RENAME INDEX uniq_source_equipment TO UNIQ_SOURCE_KEY');
        $this->addSql('ALTER TABLE language RENAME INDEX idx_language_source TO IDX_D4DB71B56F972CB7');
        $this->addSql('ALTER TABLE monster RENAME INDEX idx_monster_source TO IDX_245EC6F46F972CB7');
        $this->addSql('ALTER TABLE rules_source RENAME INDEX uniq_rules_source_slug TO UNIQ_F8F4D65D989D9B62');
        $this->addSql('ALTER TABLE species RENAME INDEX idx_species_source TO IDX_A50FF7126F972CB7');
        $this->addSql('ALTER TABLE species RENAME INDEX uniq_source_species TO UNIQ_SOURCE_KEY');
        $this->addSql('ALTER TABLE spell RENAME INDEX idx_spell_source TO IDX_D03FCD8D6F972CB7');
        $this->addSql('ALTER TABLE spell RENAME INDEX uniq_source_spell TO UNIQ_SOURCE_KEY');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `character` DROP FOREIGN KEY FK_937AB0343B64E297');
        $this->addSql('ALTER TABLE `character` DROP FOREIGN KEY FK_937AB034802025F7');
        $this->addSql('ALTER TABLE `character` DROP FOREIGN KEY FK_937AB034B2A1D860');
        $this->addSql('ALTER TABLE `character` DROP FOREIGN KEY FK_937AB034C93D69EA');
        $this->addSql('ALTER TABLE character_ability_score DROP FOREIGN KEY FK_64C201F71136BE75');
        $this->addSql('ALTER TABLE character_choice DROP FOREIGN KEY FK_62E999011136BE75');
        $this->addSql('ALTER TABLE character_feature DROP FOREIGN KEY FK_7672F55B1136BE75');
        $this->addSql('ALTER TABLE character_feature DROP FOREIGN KEY FK_7672F55B60E4B879');
        $this->addSql('ALTER TABLE character_item DROP FOREIGN KEY FK_8E731861136BE75');
        $this->addSql('ALTER TABLE character_item DROP FOREIGN KEY FK_8E73186517FE9FE');
        $this->addSql('ALTER TABLE character_proficiency DROP FOREIGN KEY FK_DBF98A461136BE75');
        $this->addSql('ALTER TABLE character_spell DROP FOREIGN KEY FK_2EFC2AEF1136BE75');
        $this->addSql('ALTER TABLE character_spell DROP FOREIGN KEY FK_2EFC2AEF479EC90D');
        $this->addSql('ALTER TABLE class_level DROP FOREIGN KEY FK_7C3DED253B64E297');
        $this->addSql('ALTER TABLE external_reference DROP FOREIGN KEY FK_8AF8E6076F972CB7');
        $this->addSql('ALTER TABLE feature DROP FOREIGN KEY FK_1FD775666F972CB7');
        $this->addSql('ALTER TABLE import_run DROP FOREIGN KEY FK_C41B04406F972CB7');
        $this->addSql('ALTER TABLE import_run_seen DROP FOREIGN KEY FK_9103EBC6F8D244DC');
        $this->addSql('ALTER TABLE skill DROP FOREIGN KEY FK_5E3DE4778016D8B2');
        $this->addSql('ALTER TABLE spell_class_def DROP FOREIGN KEY FK_9A8C1CBA479EC90D');
        $this->addSql('ALTER TABLE spell_class_def DROP FOREIGN KEY FK_9A8C1CBA3B64E297');
        $this->addSql('ALTER TABLE subclass_def DROP FOREIGN KEY FK_EE2232A46F972CB7');
        $this->addSql('ALTER TABLE subclass_def DROP FOREIGN KEY FK_EE2232A43B64E297');
        $this->addSql('ALTER TABLE trinket DROP FOREIGN KEY FK_871A1C866F972CB7');
        $this->addSql('DROP TABLE ability');
        $this->addSql('DROP TABLE `character`');
        $this->addSql('DROP TABLE character_ability_score');
        $this->addSql('DROP TABLE character_choice');
        $this->addSql('DROP TABLE character_feature');
        $this->addSql('DROP TABLE character_item');
        $this->addSql('DROP TABLE character_proficiency');
        $this->addSql('DROP TABLE character_spell');
        $this->addSql('DROP TABLE class_level');
        $this->addSql('DROP TABLE external_reference');
        $this->addSql('DROP TABLE feature');
        $this->addSql('DROP TABLE import_run');
        $this->addSql('DROP TABLE import_run_seen');
        $this->addSql('DROP TABLE skill');
        $this->addSql('DROP TABLE spell_class_def');
        $this->addSql('DROP TABLE subclass_def');
        $this->addSql('DROP TABLE trinket');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE background RENAME INDEX idx_bc68b4506f972cb7 TO IDX_BACKGROUND_SOURCE');
        $this->addSql('ALTER TABLE background RENAME INDEX uniq_source_key TO UNIQ_SOURCE_BACKGROUND');
        $this->addSql('ALTER TABLE class_def RENAME INDEX idx_7a4d999d6f972cb7 TO IDX_CLASSDEF_SOURCE');
        $this->addSql('ALTER TABLE class_def RENAME INDEX uniq_source_key TO UNIQ_SOURCE_CLASSDEF');
        $this->addSql('ALTER TABLE equipment RENAME INDEX idx_d338d5836f972cb7 TO IDX_EQUIPMENT_SOURCE');
        $this->addSql('ALTER TABLE equipment RENAME INDEX uniq_source_key TO UNIQ_SOURCE_EQUIPMENT');
        $this->addSql('ALTER TABLE language RENAME INDEX idx_d4db71b56f972cb7 TO IDX_LANGUAGE_SOURCE');
        $this->addSql('ALTER TABLE monster RENAME INDEX idx_245ec6f46f972cb7 TO IDX_MONSTER_SOURCE');
        $this->addSql('ALTER TABLE rules_source RENAME INDEX uniq_f8f4d65d989d9b62 TO UNIQ_RULES_SOURCE_SLUG');
        $this->addSql('ALTER TABLE species RENAME INDEX uniq_source_key TO UNIQ_SOURCE_SPECIES');
        $this->addSql('ALTER TABLE species RENAME INDEX idx_a50ff7126f972cb7 TO IDX_SPECIES_SOURCE');
        $this->addSql('ALTER TABLE spell RENAME INDEX uniq_source_key TO UNIQ_SOURCE_SPELL');
        $this->addSql('ALTER TABLE spell RENAME INDEX idx_d03fcd8d6f972cb7 TO IDX_SPELL_SOURCE');
    }
}
