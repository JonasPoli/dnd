<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229061213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Translate Monster Types
        $this->addSql("UPDATE monster SET type_pt = CASE type
            WHEN 'Angel' THEN 'Anjo'
            WHEN 'Animal' THEN 'Animal'
            WHEN 'Any Lineage' THEN 'Qualquer Linhagem'
            WHEN 'any race' THEN 'qualquer raça'
            WHEN 'bearfolk' THEN 'povo-urso'
            WHEN 'burrowling' THEN 'escavador'
            WHEN 'dakini' THEN 'dakini'
            WHEN 'dark folk' THEN 'povo das sombras'
            WHEN 'demon' THEN 'demônio'
            WHEN 'demon, shapechanger' THEN 'demônio, metamorfo'
            WHEN 'derro' THEN 'derro'
            WHEN 'devil' THEN 'diabo'
            WHEN 'dhampir' THEN 'dhampir'
            WHEN 'dragonborn' THEN 'draconato'
            WHEN 'dwarf' THEN 'anão'
            WHEN 'elf' THEN 'elfo'
            WHEN 'elf, shapechanger' THEN 'elfo, metamorfo'
            WHEN 'erina' THEN 'erina'
            WHEN 'fey' THEN 'fada'
            WHEN 'gearforged' THEN 'forjado'
            WHEN 'gnoll' THEN 'gnoll'
            WHEN 'gnoll, shapechanger' THEN 'gnoll, metamorfo'
            WHEN 'gnome' THEN 'gnomo'
            WHEN 'goblinoid' THEN 'goblinoide'
            WHEN 'grimlock' THEN 'grimlock'
            WHEN 'harefolk' THEN 'povo-lebre'
            WHEN 'human' THEN 'humano'
            WHEN 'human, shapechanger' THEN 'humano, metamorfo'
            WHEN 'kami' THEN 'kami'
            WHEN 'kenku' THEN 'kenku'
            WHEN 'kobold' THEN 'kobold'
            WHEN 'kryt' THEN 'kryt'
            WHEN 'lemurfolk' THEN 'povo-lêmure'
            WHEN 'lizardfolk' THEN 'povo-lagarto'
            WHEN 'Lycanthrope' THEN 'Licantropo'
            WHEN 'merfolk' THEN 'tritão'
            WHEN 'noctiny' THEN 'notívago'
            WHEN 'orc' THEN 'orc'
            WHEN 'otterfolk' THEN 'povo-lontra'
            WHEN 'Outsider' THEN 'Extraplanar'
            WHEN 'ramag' THEN 'ramag'
            WHEN 'ratfolk' THEN 'povo-rato'
            WHEN 'roachling' THEN 'povo-barata'
            WHEN 'sahuagin' THEN 'sahuagin'
            WHEN 'satarre' THEN 'satarre'
            WHEN 'shadow fey' THEN 'fada das sombras'
            WHEN 'shapechanger' THEN 'metamorfo'
            WHEN 'shapechanger, nkosi' THEN 'metamorfo, nkosi'
            WHEN 'shoth' THEN 'shoth'
            WHEN 'simian' THEN 'símio'
            WHEN 'subek' THEN 'subek'
            WHEN 'Swarm' THEN 'Enxame'
            WHEN 'Swarm of Devils' THEN 'Enxame de Diabos'
            WHEN 'titan' THEN 'titã'
            WHEN 'tosculi' THEN 'tosculi'
            WHEN 'trollkin' THEN 'trollkin'
            WHEN 'yakirian' THEN 'yakirian'
            ELSE type_pt END"); // Use ELSE type_pt to keep existing translations if any, or change to type if strictly resetting. 
             // Logic: "você já deve atualizar... com os valores traduzidos". Safe to overwrite. 
             // Using ELSE type_pt preserves manual changes if key doesn't match list.
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
