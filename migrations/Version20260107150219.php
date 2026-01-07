<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260107150219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE subclass_spell (id INT AUTO_INCREMENT NOT NULL, subclass_def_id INT NOT NULL, spell_id INT NOT NULL, level_acquired INT NOT NULL, INDEX IDX_79688F0E802025F7 (subclass_def_id), INDEX IDX_79688F0E479EC90D (spell_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE subclass_spell ADD CONSTRAINT FK_79688F0E802025F7 FOREIGN KEY (subclass_def_id) REFERENCES subclass_def (id)');
        $this->addSql('ALTER TABLE subclass_spell ADD CONSTRAINT FK_79688F0E479EC90D FOREIGN KEY (spell_id) REFERENCES spell (id)');
        $this->addSql('ALTER TABLE class_level ADD cantrips_known INT DEFAULT NULL, ADD spells_prepared INT DEFAULT NULL, ADD features_config JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE spell CHANGE higher_levels_md_pt scho LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subclass_spell DROP FOREIGN KEY FK_79688F0E802025F7');
        $this->addSql('ALTER TABLE subclass_spell DROP FOREIGN KEY FK_79688F0E479EC90D');
        $this->addSql('DROP TABLE subclass_spell');
        $this->addSql('ALTER TABLE class_level DROP cantrips_known, DROP spells_prepared, DROP features_config');
        $this->addSql('ALTER TABLE spell CHANGE scho higher_levels_md_pt LONGTEXT DEFAULT NULL');
    }
}
