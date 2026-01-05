<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229060622 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE monster ADD name_pt VARCHAR(255) DEFAULT NULL, ADD size_pt VARCHAR(50) DEFAULT NULL, ADD type_pt VARCHAR(50) DEFAULT NULL, ADD subtype_pt VARCHAR(50) DEFAULT NULL, ADD group_pt VARCHAR(50) DEFAULT NULL, ADD alignment_pt VARCHAR(255) DEFAULT NULL, ADD armor_desc_pt VARCHAR(255) DEFAULT NULL, ADD description_md_pt LONGTEXT DEFAULT NULL');

        // Translate Size
        $this->addSql("UPDATE monster SET size_pt = CASE size
            WHEN 'Gargantuan' THEN 'Imenso'
            WHEN 'Huge' THEN 'Enorme'
            WHEN 'Large' THEN 'Grande'
            WHEN 'Medium' THEN 'Médio'
            WHEN 'Small' THEN 'Pequeno'
            WHEN 'Tiny' THEN 'Minúsculo'
            WHEN 'Titanic' THEN 'Titânico'
            ELSE size END");

        // Translate Alignment
        $this->addSql("UPDATE monster SET alignment_pt = CASE alignment
            WHEN 'any' THEN 'qualquer'
            WHEN 'any alignment' THEN 'qualquer tendência'
            WHEN 'any alignment (as its creator deity)' THEN 'qualquer tendência (igual à de sua divindade criadora)'
            WHEN 'any alignment (as its deity)' THEN 'qualquer tendência (igual à de sua divindade)'
            WHEN 'any alignment (as its patron deity)' THEN 'qualquer tendência (igual à de sua divindade patrona)'
            WHEN 'any chaotic' THEN 'qualquer caótica'
            WHEN 'any chaotic alignment' THEN 'qualquer tendência caótica'
            WHEN 'any evil' THEN 'qualquer maligna'
            WHEN 'any evil alignment' THEN 'qualquer tendência maligna'
            WHEN 'any good' THEN 'qualquer bondosa'
            WHEN 'Any Lawful Alignment' THEN 'Qualquer Tendência Leal'
            WHEN 'any non-good' THEN 'qualquer não bondosa'
            WHEN 'any non-good alignment' THEN 'qualquer tendência não bondosa'
            WHEN 'any non-lawful' THEN 'qualquer não leal'
            WHEN 'any non-lawful alignment' THEN 'qualquer tendência não leal'
            WHEN 'chaotic' THEN 'caótico'
            WHEN 'chaotic evil' THEN 'caótico e maligno'
            WHEN 'chaotic good' THEN 'caótico e bondoso'
            WHEN 'chaotic good or chaotic neutral' THEN 'caótico e bondoso ou caótico e neutro'
            WHEN 'chaotic neutral' THEN 'caótico e neutro'
            WHEN 'chaotic neutral or chaotic evil' THEN 'caótico e neutro ou caótico e maligno'
            WHEN 'chaotic neutral or chaotic good' THEN 'caótico e neutro ou caótico e bondoso'
            WHEN 'good' THEN 'bondoso'
            WHEN 'lawful evil' THEN 'leal e maligno'
            WHEN 'lawful good' THEN 'leal e bondoso'
            WHEN 'Lawful Neutral' THEN 'Leal e Neutro'
            WHEN 'lawful neutral or evil' THEN 'leal e neutro ou maligno'
            WHEN 'Lawful Neutral or Lawful Evil' THEN 'Leal e Neutro ou Leal e Maligno'
            WHEN 'neutral' THEN 'neutro'
            WHEN 'neutral evil' THEN 'neutro e maligno'
            WHEN 'neutral evil (50%) lawful evil (50%)' THEN 'neutro e maligno (50%) ou leal e maligno (50%)'
            WHEN 'Neutral Evil (50%) or Lawful Evil (50%)' THEN 'Neutro e Maligno (50%) ou Leal e Maligno (50%)'
            WHEN 'neutral good' THEN 'neutro e bondoso'
            WHEN 'neutral good (50%) or neutral evil (50%)' THEN 'neutro e bondoso (50%) ou neutro e maligno (50%)'
            WHEN 'non-lawful' THEN 'não leal'
            WHEN 'Shapechanger)' THEN 'Metamorfo)'
            WHEN 'Titan)' THEN 'Titã)'
            WHEN 'unaligned' THEN 'imparcial'
            ELSE alignment END");

        // Translate SubType
        $this->addSql("UPDATE monster SET subtype_pt = CASE subtype
            WHEN 'Aberration' THEN 'Aberração'
            WHEN 'Beast' THEN 'Besta'
            WHEN 'Celestial' THEN 'Celestial'
            WHEN 'Construct' THEN 'Construto'
            WHEN 'Dragon' THEN 'Dragão'
            WHEN 'Elemental' THEN 'Elemental'
            WHEN 'Fey' THEN 'Fada'
            WHEN 'Fiend' THEN 'Corruptor'
            WHEN 'Giant' THEN 'Gigante'
            WHEN 'Humanoid' THEN 'Humanoide'
            WHEN 'Monstrosity' THEN 'Monstruosidade'
            WHEN 'Ooze' THEN 'Limo'
            WHEN 'Plant' THEN 'Planta'
            WHEN 'Swarm' THEN 'Enxame'
            WHEN 'Undead' THEN 'Morto-vivo'
            ELSE subtype END");

        // Translate Type
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
            ELSE type END");

        // Translate MonsterGroup
        $this->addSql("UPDATE monster SET group_pt = CASE monster_group
            WHEN 'Angels' THEN 'Anjos'
            WHEN 'Animals' THEN 'Animais'
            WHEN 'Animated Objects' THEN 'Objetos Animados'
            WHEN 'Black Dragon' THEN 'Dragão Negro'
            WHEN 'Blue Dragon' THEN 'Dragão Azul'
            WHEN 'Brass Dragon' THEN 'Dragão de Latão'
            WHEN 'Bronze Dragon' THEN 'Dragão de Bronze'
            WHEN 'Copper Dragon' THEN 'Dragão de Cobre'
            WHEN 'Demons' THEN 'Demônios'
            WHEN 'Devils' THEN 'Diabos'
            WHEN 'Dinosaurs' THEN 'Dinossauros'
            WHEN 'Dragons' THEN 'Dragões'
            WHEN 'Elemental Wardens' THEN 'Guardiões Elementais'
            WHEN 'Elementals' THEN 'Elementais'
            WHEN 'Elf' THEN 'Elfo'
            WHEN 'Fungi' THEN 'Fungos'
            WHEN 'Genies' THEN 'Gênios'
            WHEN 'Ghouls' THEN 'Carniçais'
            WHEN 'Giants' THEN 'Gigantes'
            WHEN 'Goblins' THEN 'Goblins'
            WHEN 'Gold Dragon' THEN 'Dragão de Ouro'
            WHEN 'Golems' THEN 'Golens'
            WHEN 'Green Dragon' THEN 'Dragão Verde'
            WHEN 'Hags' THEN 'Bruxas'
            WHEN 'Kobolds' THEN 'Kobolds'
            WHEN 'Liches' THEN 'Liches'
            WHEN 'Lizardfolk' THEN 'Povo-lagarto'
            WHEN 'Lycanthropes' THEN 'Licantropos'
            WHEN 'Mephits' THEN 'Mefites'
            WHEN 'Merfolk' THEN 'Tritões'
            WHEN 'Miscellaneous Creatures' THEN 'Criaturas Diversas'
            WHEN 'Mummies' THEN 'Múmias'
            WHEN 'Mycolids' THEN 'Micólidos'
            WHEN 'Nagas' THEN 'Nagas'
            WHEN 'NPCs' THEN 'NPCs'
            WHEN 'null' THEN NULL
            WHEN 'Oozes' THEN 'Limos'
            WHEN 'Orcs' THEN 'Orcs'
            WHEN 'Red Dragon' THEN 'Dragão Vermelho'
            WHEN 'Silver Dragon' THEN 'Dragão Prateado'
            WHEN 'Skeletons' THEN 'Esqueletos'
            WHEN 'Sphinxes' THEN 'Esfinges'
            WHEN 'Sporeborn' THEN 'Nascidos dos Esporos'
            WHEN 'Vampires' THEN 'Vampiros'
            WHEN 'White Dragon' THEN 'Dragão Branco'
            WHEN 'Zombies' THEN 'Zumbis'
            ELSE monster_group END");

        // Translate ArmorDesc (Simple Matches first)
        $this->addSql("UPDATE monster SET armor_desc_pt = CASE armor_desc
            WHEN '13 with mage armor' THEN '13 com armadura arcana'
            WHEN '15 with _mage armor_' THEN '15 com _armadura arcana_'
            WHEN '15 with junk armor' THEN '15 com armadura de sucata'
            WHEN '15 with mage armor' THEN '15 com armadura arcana'
            WHEN '16 mage armor' THEN '16 armadura arcana'
            WHEN '16 with _barkskin_' THEN '16 com _pele de árvore_'
            WHEN '16 with barkskin' THEN '16 com pele de árvore'
            WHEN '16 with mage armor' THEN '16 com armadura arcana'
            WHEN '18 mage armor' THEN '18 armadura arcana'
            WHEN '18 with mage armor' THEN '18 com armadura arcana'
            WHEN 'armor scraps' THEN 'restos de armadura'
            WHEN 'bone kilt' THEN 'saiote de ossos'
            WHEN 'breastplate' THEN 'peitoral'
            WHEN 'breastplate and shield' THEN 'peitoral e escudo'
            WHEN 'breastplate, shield' THEN 'peitoral, escudo'
            WHEN 'chain armor' THEN 'armadura de malha'
            WHEN 'chain mail' THEN 'cota de malha'
            WHEN 'chain mail and shield' THEN 'cota de malha e escudo'
            WHEN 'chain mail, shield' THEN 'cota de malha, escudo'
            WHEN 'chain shirt' THEN 'camisão de malha'
            WHEN 'chain shirt, shield' THEN 'camisão de malha, escudo'
            WHEN 'hide armor' THEN 'gibão de peles'
            WHEN 'hide armor, shield' THEN 'gibão de peles, escudo'
            WHEN 'leather' THEN 'couro'
            WHEN 'leather armor' THEN 'armadura de couro'
            WHEN 'leather armor, shield' THEN 'armadura de couro, escudo'
            WHEN 'leather, shield' THEN 'couro, escudo'
            WHEN 'natural' THEN 'natural'
            WHEN 'natural armor' THEN 'armadura natural'
            WHEN 'natural armor, shield' THEN 'armadura natural, escudo'
            WHEN 'padded armor' THEN 'armadura acolchoada'
            WHEN 'plate' THEN 'placa'
            WHEN 'plate and shield' THEN 'placa e escudo'
            WHEN 'plate armor' THEN 'armadura de placas'
            WHEN 'plate, shield' THEN 'placa, escudo'
            WHEN 'scale mail' THEN 'brunea'
            WHEN 'scale mail, shield' THEN 'brunea, escudo'
            WHEN 'shield' THEN 'escudo'
            WHEN 'splint' THEN 'tala'
            WHEN 'splint, shield' THEN 'tala, escudo'
            WHEN 'studded leather' THEN 'couro batido'
            WHEN 'studded leather and shield' THEN 'couro batido e escudo'
            WHEN 'studded leather Armor' THEN 'Armadura de couro batido'
            WHEN 'studded leather, shield' THEN 'couro batido, escudo'
            ELSE armor_desc END");

        // Translate ArmorDesc (Complex Replacements)
        // Note: Doing specific replaces for common patterns
        $this->addSql("UPDATE monster SET armor_desc_pt = REPLACE(armor_desc_pt, 'natural armor', 'armadura natural') WHERE armor_desc_pt IS NOT NULL");
        $this->addSql("UPDATE monster SET armor_desc_pt = REPLACE(armor_desc_pt, 'in humanoid form', 'em forma humanoide') WHERE armor_desc_pt IS NOT NULL");
        $this->addSql("UPDATE monster SET armor_desc_pt = REPLACE(armor_desc_pt, 'in bear and hybrid form', 'em forma de urso e híbrida') WHERE armor_desc_pt IS NOT NULL");
        $this->addSql("UPDATE monster SET armor_desc_pt = REPLACE(armor_desc_pt, 'in boar or hybrid form', 'em forma de javali ou híbrida') WHERE armor_desc_pt IS NOT NULL");
        $this->addSql("UPDATE monster SET armor_desc_pt = REPLACE(armor_desc_pt, 'in wolf or hybrid form', 'em forma de lobo ou híbrida') WHERE armor_desc_pt IS NOT NULL");
        $this->addSql("UPDATE monster SET armor_desc_pt = REPLACE(armor_desc_pt, 'while prone', 'enquanto caído') WHERE armor_desc_pt IS NOT NULL");
        $this->addSql("UPDATE monster SET armor_desc_pt = REPLACE(armor_desc_pt, 'with shield', 'com escudo') WHERE armor_desc_pt IS NOT NULL");
        $this->addSql("UPDATE monster SET armor_desc_pt = REPLACE(armor_desc_pt, 'plus', 'mais') WHERE armor_desc_pt IS NOT NULL");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE monster DROP name_pt, DROP size_pt, DROP type_pt, DROP subtype_pt, DROP group_pt, DROP alignment_pt, DROP armor_desc_pt, DROP description_md_pt');
    }
}
