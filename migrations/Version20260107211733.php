<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260107211733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE background_equipment (background_id INT NOT NULL, equipment_id INT NOT NULL, INDEX IDX_D6E3FD9DC93D69EA (background_id), INDEX IDX_D6E3FD9D517FE9FE (equipment_id), PRIMARY KEY(background_id, equipment_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE background_equipment ADD CONSTRAINT FK_D6E3FD9DC93D69EA FOREIGN KEY (background_id) REFERENCES background (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE background_equipment ADD CONSTRAINT FK_D6E3FD9D517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE background ADD attribute1_id INT DEFAULT NULL, ADD attribute2_id INT DEFAULT NULL, ADD attribute3_id INT DEFAULT NULL, ADD feat_id INT DEFAULT NULL, ADD skill1_id INT DEFAULT NULL, ADD skill2_id INT DEFAULT NULL, ADD tool_proficiency_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE background ADD CONSTRAINT FK_BC68B4508BB2D0BF FOREIGN KEY (attribute1_id) REFERENCES attribute (id)');
        $this->addSql('ALTER TABLE background ADD CONSTRAINT FK_BC68B45099077F51 FOREIGN KEY (attribute2_id) REFERENCES attribute (id)');
        $this->addSql('ALTER TABLE background ADD CONSTRAINT FK_BC68B45021BB1834 FOREIGN KEY (attribute3_id) REFERENCES attribute (id)');
        $this->addSql('ALTER TABLE background ADD CONSTRAINT FK_BC68B450F43C4D5C FOREIGN KEY (feat_id) REFERENCES feat (id)');
        $this->addSql('ALTER TABLE background ADD CONSTRAINT FK_BC68B4504EEB88EE FOREIGN KEY (skill1_id) REFERENCES skill (id)');
        $this->addSql('ALTER TABLE background ADD CONSTRAINT FK_BC68B4505C5E2700 FOREIGN KEY (skill2_id) REFERENCES skill (id)');
        $this->addSql('ALTER TABLE background ADD CONSTRAINT FK_BC68B45044021BBA FOREIGN KEY (tool_proficiency_id) REFERENCES equipment (id)');
        $this->addSql('CREATE INDEX IDX_BC68B4508BB2D0BF ON background (attribute1_id)');
        $this->addSql('CREATE INDEX IDX_BC68B45099077F51 ON background (attribute2_id)');
        $this->addSql('CREATE INDEX IDX_BC68B45021BB1834 ON background (attribute3_id)');
        $this->addSql('CREATE INDEX IDX_BC68B450F43C4D5C ON background (feat_id)');
        $this->addSql('CREATE INDEX IDX_BC68B4504EEB88EE ON background (skill1_id)');
        $this->addSql('CREATE INDEX IDX_BC68B4505C5E2700 ON background (skill2_id)');
        $this->addSql('CREATE INDEX IDX_BC68B45044021BBA ON background (tool_proficiency_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE background_equipment DROP FOREIGN KEY FK_D6E3FD9DC93D69EA');
        $this->addSql('ALTER TABLE background_equipment DROP FOREIGN KEY FK_D6E3FD9D517FE9FE');
        $this->addSql('DROP TABLE background_equipment');
        $this->addSql('ALTER TABLE background DROP FOREIGN KEY FK_BC68B4508BB2D0BF');
        $this->addSql('ALTER TABLE background DROP FOREIGN KEY FK_BC68B45099077F51');
        $this->addSql('ALTER TABLE background DROP FOREIGN KEY FK_BC68B45021BB1834');
        $this->addSql('ALTER TABLE background DROP FOREIGN KEY FK_BC68B450F43C4D5C');
        $this->addSql('ALTER TABLE background DROP FOREIGN KEY FK_BC68B4504EEB88EE');
        $this->addSql('ALTER TABLE background DROP FOREIGN KEY FK_BC68B4505C5E2700');
        $this->addSql('ALTER TABLE background DROP FOREIGN KEY FK_BC68B45044021BBA');
        $this->addSql('DROP INDEX IDX_BC68B4508BB2D0BF ON background');
        $this->addSql('DROP INDEX IDX_BC68B45099077F51 ON background');
        $this->addSql('DROP INDEX IDX_BC68B45021BB1834 ON background');
        $this->addSql('DROP INDEX IDX_BC68B450F43C4D5C ON background');
        $this->addSql('DROP INDEX IDX_BC68B4504EEB88EE ON background');
        $this->addSql('DROP INDEX IDX_BC68B4505C5E2700 ON background');
        $this->addSql('DROP INDEX IDX_BC68B45044021BBA ON background');
        $this->addSql('ALTER TABLE background DROP attribute1_id, DROP attribute2_id, DROP attribute3_id, DROP feat_id, DROP skill1_id, DROP skill2_id, DROP tool_proficiency_id');
    }
}
