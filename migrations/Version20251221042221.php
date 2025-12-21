<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221042221 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE monster ADD damage_immunities VARCHAR(500) DEFAULT NULL, ADD damage_resistances VARCHAR(255) DEFAULT NULL, ADD damage_vulnerabilities VARCHAR(255) DEFAULT NULL, ADD condition_immunities VARCHAR(255) DEFAULT NULL, ADD legendary_desc VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE monster DROP damage_immunities, DROP damage_resistances, DROP damage_vulnerabilities, DROP condition_immunities, DROP legendary_desc');
    }
}
