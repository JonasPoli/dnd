<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229025313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_SOURCE_KEY ON species');
        $this->addSql('ALTER TABLE species DROP rule_slug, DROP asi_description, DROP alignment, DROP vision, DROP traits');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE species ADD rule_slug VARCHAR(100) NOT NULL, ADD asi_description LONGTEXT DEFAULT NULL, ADD alignment LONGTEXT DEFAULT NULL, ADD vision LONGTEXT DEFAULT NULL, ADD traits LONGTEXT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_SOURCE_KEY ON species (rules_source_id, rule_slug)');
    }
}
