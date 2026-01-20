<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260120041802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `character` ADD trinket_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `character` ADD CONSTRAINT FK_937AB03498EC06A2 FOREIGN KEY (trinket_id) REFERENCES trinket (id)');
        $this->addSql('CREATE INDEX IDX_937AB03498EC06A2 ON `character` (trinket_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `character` DROP FOREIGN KEY FK_937AB03498EC06A2');
        $this->addSql('DROP INDEX IDX_937AB03498EC06A2 ON `character`');
        $this->addSql('ALTER TABLE `character` DROP trinket_id');
    }
}
