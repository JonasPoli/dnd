<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260106142514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE class_def_skill (class_def_id INT NOT NULL, skill_id INT NOT NULL, INDEX IDX_99F325713B64E297 (class_def_id), INDEX IDX_99F325715585C142 (skill_id), PRIMARY KEY(class_def_id, skill_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE class_def_skill ADD CONSTRAINT FK_99F325713B64E297 FOREIGN KEY (class_def_id) REFERENCES class_def (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE class_def_skill ADD CONSTRAINT FK_99F325715585C142 FOREIGN KEY (skill_id) REFERENCES skill (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE class_def_skill DROP FOREIGN KEY FK_99F325713B64E297');
        $this->addSql('ALTER TABLE class_def_skill DROP FOREIGN KEY FK_99F325715585C142');
        $this->addSql('DROP TABLE class_def_skill');
    }
}
