<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221053544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE spell ADD page VARCHAR(50) DEFAULT NULL, ADD target_range_sort INT DEFAULT NULL, ADD components VARCHAR(255) DEFAULT NULL, ADD material LONGTEXT DEFAULT NULL, ADD is_ritual TINYINT(1) DEFAULT NULL, ADD is_concentration TINYINT(1) DEFAULT NULL, ADD is_verbal TINYINT(1) DEFAULT NULL, ADD is_somatic TINYINT(1) DEFAULT NULL, ADD is_material TINYINT(1) DEFAULT NULL, ADD archetype VARCHAR(255) DEFAULT NULL, ADD circles VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE spell DROP page, DROP target_range_sort, DROP components, DROP material, DROP is_ritual, DROP is_concentration, DROP is_verbal, DROP is_somatic, DROP is_material, DROP archetype, DROP circles');
    }
}
