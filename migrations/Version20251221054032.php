<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221054032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE class_def ADD hp_at1st_level VARCHAR(255) DEFAULT NULL, ADD hp_at_higher_levels VARCHAR(255) DEFAULT NULL, ADD prof_armor LONGTEXT DEFAULT NULL, ADD prof_weapons LONGTEXT DEFAULT NULL, ADD prof_tools LONGTEXT DEFAULT NULL, ADD prof_skills LONGTEXT DEFAULT NULL, ADD equipment LONGTEXT DEFAULT NULL, ADD class_table_md LONGTEXT DEFAULT NULL, ADD spellcasting_ability VARCHAR(50) DEFAULT NULL, ADD subtypes_name VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE class_def DROP hp_at1st_level, DROP hp_at_higher_levels, DROP prof_armor, DROP prof_weapons, DROP prof_tools, DROP prof_skills, DROP equipment, DROP class_table_md, DROP spellcasting_ability, DROP subtypes_name');
    }
}
