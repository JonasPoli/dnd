<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260117174037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE character_feat (character_id INT NOT NULL, feat_id INT NOT NULL, INDEX IDX_4D6785531136BE75 (character_id), INDEX IDX_4D678553F43C4D5C (feat_id), PRIMARY KEY(character_id, feat_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE character_feat ADD CONSTRAINT FK_4D6785531136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE character_feat ADD CONSTRAINT FK_4D678553F43C4D5C FOREIGN KEY (feat_id) REFERENCES feat (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE character_feat DROP FOREIGN KEY FK_4D6785531136BE75');
        $this->addSql('ALTER TABLE character_feat DROP FOREIGN KEY FK_4D678553F43C4D5C');
        $this->addSql('DROP TABLE character_feat');
    }
}
