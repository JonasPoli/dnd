<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260108141357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE character_attribute (id INT AUTO_INCREMENT NOT NULL, character_id INT NOT NULL, attribute_id INT NOT NULL, value INT NOT NULL, INDEX IDX_7D2A4DC01136BE75 (character_id), INDEX IDX_7D2A4DC0B6E62EFA (attribute_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE character_attribute ADD CONSTRAINT FK_7D2A4DC01136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id)');
        $this->addSql('ALTER TABLE character_attribute ADD CONSTRAINT FK_7D2A4DC0B6E62EFA FOREIGN KEY (attribute_id) REFERENCES attribute (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE character_attribute DROP FOREIGN KEY FK_7D2A4DC01136BE75');
        $this->addSql('ALTER TABLE character_attribute DROP FOREIGN KEY FK_7D2A4DC0B6E62EFA');
        $this->addSql('DROP TABLE character_attribute');
    }
}
