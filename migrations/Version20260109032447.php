<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260109032447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE character_inventory (character_id INT NOT NULL, equipment_id INT NOT NULL, INDEX IDX_367DE80D1136BE75 (character_id), INDEX IDX_367DE80D517FE9FE (equipment_id), PRIMARY KEY(character_id, equipment_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE character_inventory ADD CONSTRAINT FK_367DE80D1136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE character_inventory ADD CONSTRAINT FK_367DE80D517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `character` ADD coin_cp INT DEFAULT 0 NOT NULL, ADD coin_sp INT DEFAULT 0 NOT NULL, ADD coin_ep INT DEFAULT 0 NOT NULL, ADD coin_gp INT DEFAULT 0 NOT NULL, ADD coin_pp INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE character_inventory DROP FOREIGN KEY FK_367DE80D1136BE75');
        $this->addSql('ALTER TABLE character_inventory DROP FOREIGN KEY FK_367DE80D517FE9FE');
        $this->addSql('DROP TABLE character_inventory');
        $this->addSql('ALTER TABLE `character` DROP coin_cp, DROP coin_sp, DROP coin_ep, DROP coin_gp, DROP coin_pp');
    }
}
