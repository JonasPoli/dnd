<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221052708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE monster ADD subtype VARCHAR(100) DEFAULT NULL, ADD `group` VARCHAR(100) DEFAULT NULL, ADD cr NUMERIC(5, 2) DEFAULT NULL, ADD armor_desc VARCHAR(100) DEFAULT NULL, ADD strength_save INT DEFAULT NULL, ADD dexterity_save INT DEFAULT NULL, ADD constitution_save INT DEFAULT NULL, ADD intelligence_save INT DEFAULT NULL, ADD wisdom_save INT DEFAULT NULL, ADD charisma_save INT DEFAULT NULL, ADD perception INT DEFAULT NULL, ADD bonus_actions_json JSON DEFAULT NULL, ADD reactions_json JSON DEFAULT NULL, ADD spell_list JSON DEFAULT NULL, ADD page_no INT DEFAULT NULL, ADD environments JSON DEFAULT NULL, ADD img_main VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE monster DROP subtype, DROP `group`, DROP cr, DROP armor_desc, DROP strength_save, DROP dexterity_save, DROP constitution_save, DROP intelligence_save, DROP wisdom_save, DROP charisma_save, DROP perception, DROP bonus_actions_json, DROP reactions_json, DROP spell_list, DROP page_no, DROP environments, DROP img_main');
    }
}
