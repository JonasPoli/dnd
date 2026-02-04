<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203170625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE class_def_feat (class_def_id INT NOT NULL, feat_id INT NOT NULL, INDEX IDX_43F223B03B64E297 (class_def_id), INDEX IDX_43F223B0F43C4D5C (feat_id), PRIMARY KEY(class_def_id, feat_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE class_def_feat ADD CONSTRAINT FK_43F223B03B64E297 FOREIGN KEY (class_def_id) REFERENCES class_def (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE class_def_feat ADD CONSTRAINT FK_43F223B0F43C4D5C FOREIGN KEY (feat_id) REFERENCES feat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE class_level ADD feats_known INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE class_def_feat DROP FOREIGN KEY FK_43F223B03B64E297');
        $this->addSql('ALTER TABLE class_def_feat DROP FOREIGN KEY FK_43F223B0F43C4D5C');
        $this->addSql('DROP TABLE class_def_feat');
        $this->addSql('ALTER TABLE class_level DROP feats_known');
    }
}
