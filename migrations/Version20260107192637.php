<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260107192637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE class_def ADD saving_throw1_id INT DEFAULT NULL, ADD saving_throw2_id INT DEFAULT NULL, ADD primary_ability1_id INT DEFAULT NULL, ADD primary_ability2_id INT DEFAULT NULL, ADD tool_proficiency1_id INT DEFAULT NULL, ADD tool_proficiency2_id INT DEFAULT NULL, ADD initial_skills_count INT DEFAULT NULL, ADD initial_tools_count INT DEFAULT NULL, ADD weapon_proficiencies VARCHAR(50) DEFAULT NULL, ADD armor_training VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE class_def ADD CONSTRAINT FK_7A4D999D3CF9F1FB FOREIGN KEY (saving_throw1_id) REFERENCES attribute (id)');
        $this->addSql('ALTER TABLE class_def ADD CONSTRAINT FK_7A4D999D2E4C5E15 FOREIGN KEY (saving_throw2_id) REFERENCES attribute (id)');
        $this->addSql('ALTER TABLE class_def ADD CONSTRAINT FK_7A4D999D576C896A FOREIGN KEY (primary_ability1_id) REFERENCES attribute (id)');
        $this->addSql('ALTER TABLE class_def ADD CONSTRAINT FK_7A4D999D45D92684 FOREIGN KEY (primary_ability2_id) REFERENCES attribute (id)');
        $this->addSql('ALTER TABLE class_def ADD CONSTRAINT FK_7A4D999DFD9C751A FOREIGN KEY (tool_proficiency1_id) REFERENCES equipment (id)');
        $this->addSql('ALTER TABLE class_def ADD CONSTRAINT FK_7A4D999DEF29DAF4 FOREIGN KEY (tool_proficiency2_id) REFERENCES equipment (id)');
        $this->addSql('CREATE INDEX IDX_7A4D999D3CF9F1FB ON class_def (saving_throw1_id)');
        $this->addSql('CREATE INDEX IDX_7A4D999D2E4C5E15 ON class_def (saving_throw2_id)');
        $this->addSql('CREATE INDEX IDX_7A4D999D576C896A ON class_def (primary_ability1_id)');
        $this->addSql('CREATE INDEX IDX_7A4D999D45D92684 ON class_def (primary_ability2_id)');
        $this->addSql('CREATE INDEX IDX_7A4D999DFD9C751A ON class_def (tool_proficiency1_id)');
        $this->addSql('CREATE INDEX IDX_7A4D999DEF29DAF4 ON class_def (tool_proficiency2_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE class_def DROP FOREIGN KEY FK_7A4D999D3CF9F1FB');
        $this->addSql('ALTER TABLE class_def DROP FOREIGN KEY FK_7A4D999D2E4C5E15');
        $this->addSql('ALTER TABLE class_def DROP FOREIGN KEY FK_7A4D999D576C896A');
        $this->addSql('ALTER TABLE class_def DROP FOREIGN KEY FK_7A4D999D45D92684');
        $this->addSql('ALTER TABLE class_def DROP FOREIGN KEY FK_7A4D999DFD9C751A');
        $this->addSql('ALTER TABLE class_def DROP FOREIGN KEY FK_7A4D999DEF29DAF4');
        $this->addSql('DROP INDEX IDX_7A4D999D3CF9F1FB ON class_def');
        $this->addSql('DROP INDEX IDX_7A4D999D2E4C5E15 ON class_def');
        $this->addSql('DROP INDEX IDX_7A4D999D576C896A ON class_def');
        $this->addSql('DROP INDEX IDX_7A4D999D45D92684 ON class_def');
        $this->addSql('DROP INDEX IDX_7A4D999DFD9C751A ON class_def');
        $this->addSql('DROP INDEX IDX_7A4D999DEF29DAF4 ON class_def');
        $this->addSql('ALTER TABLE class_def DROP saving_throw1_id, DROP saving_throw2_id, DROP primary_ability1_id, DROP primary_ability2_id, DROP tool_proficiency1_id, DROP tool_proficiency2_id, DROP initial_skills_count, DROP initial_tools_count, DROP weapon_proficiencies, DROP armor_training');
    }
}
