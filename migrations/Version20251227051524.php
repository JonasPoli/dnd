<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251227051524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE skill DROP FOREIGN KEY FK_5E3DE4778016D8B2');
        $this->addSql('DROP INDEX IDX_5E3DE4778016D8B2 ON skill');
        $this->addSql('ALTER TABLE skill ADD description LONGTEXT DEFAULT NULL, CHANGE ability_id attribute_id INT NOT NULL');
        $this->addSql('ALTER TABLE skill ADD CONSTRAINT FK_5E3DE477B6E62EFA FOREIGN KEY (attribute_id) REFERENCES attribute (id)');
        $this->addSql('CREATE INDEX IDX_5E3DE477B6E62EFA ON skill (attribute_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE skill DROP FOREIGN KEY FK_5E3DE477B6E62EFA');
        $this->addSql('DROP INDEX IDX_5E3DE477B6E62EFA ON skill');
        $this->addSql('ALTER TABLE skill DROP description, CHANGE attribute_id ability_id INT NOT NULL');
        $this->addSql('ALTER TABLE skill ADD CONSTRAINT FK_5E3DE4778016D8B2 FOREIGN KEY (ability_id) REFERENCES ability (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_5E3DE4778016D8B2 ON skill (ability_id)');
    }
}
