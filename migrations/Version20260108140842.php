<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260108140842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE character_language (character_id INT NOT NULL, language_id INT NOT NULL, INDEX IDX_8CDA98241136BE75 (character_id), INDEX IDX_8CDA982482F1BAF4 (language_id), PRIMARY KEY(character_id, language_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE character_language ADD CONSTRAINT FK_8CDA98241136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE character_language ADD CONSTRAINT FK_8CDA982482F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE character_language DROP FOREIGN KEY FK_8CDA98241136BE75');
        $this->addSql('ALTER TABLE character_language DROP FOREIGN KEY FK_8CDA982482F1BAF4');
        $this->addSql('DROP TABLE character_language');
    }
}
