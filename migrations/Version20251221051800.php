<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221051800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE background ADD skill_proficiencies VARCHAR(255) DEFAULT NULL, ADD tool_proficiencies VARCHAR(255) DEFAULT NULL, ADD languages VARCHAR(255) DEFAULT NULL, ADD equipment LONGTEXT DEFAULT NULL, ADD feature VARCHAR(255) DEFAULT NULL, ADD feature_desc LONGTEXT DEFAULT NULL, ADD suggested_characteristics LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE background DROP skill_proficiencies, DROP tool_proficiencies, DROP languages, DROP equipment, DROP feature, DROP feature_desc, DROP suggested_characteristics');
    }
}
