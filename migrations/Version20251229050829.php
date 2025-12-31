<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229050829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE magic_item ADD type_pt VARCHAR(100) DEFAULT NULL, ADD rarity_pt VARCHAR(100) DEFAULT NULL, ADD requires_attunement_pt VARCHAR(255) DEFAULT NULL');

        // Translations for Type
        $this->addSql("UPDATE magic_item SET type_pt = 'Munição' WHERE type = 'Ammunition'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Armadura' WHERE type = 'Armor'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Armadura (camisão de malha)' WHERE type = 'Armor (chain shirt)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Armadura (leve)' WHERE type = 'Armor (light)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Armadura (média ou pesada)' WHERE type = 'Armor (medium or heavy)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Armadura (placas)' WHERE type = 'Armor (plate)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Armadura (cota de malhas)' WHERE type = 'Armor (scale mail)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Armadura (escudo)' WHERE type = 'Armor (shield)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Armadura (couro batido)' WHERE type = 'Armor (studded leather)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Adaga' WHERE type = 'Dagger'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Outro' WHERE type = 'Other'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Poção' WHERE type = 'Potion'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Anel' WHERE type = 'Ring'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Bastão' WHERE type = 'Rod'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Pergaminho' WHERE type = 'Scroll'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Cajado' WHERE type = 'Staff'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Varinha' WHERE type = 'Wand'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Arma' WHERE type = 'Weapon'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Arma (qualquer munição)' WHERE type = 'Weapon (any ammunition)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Arma (qualquer machado ou espada)' WHERE type = 'Weapon (any axe or sword)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Arma (qualquer machado)' WHERE type = 'Weapon (any axe)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Arma (qualquer espada que cause dano cortante)' WHERE type = 'Weapon (any sword that deals slashing damage)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Arma (qualquer espada)' WHERE type = 'Weapon (any sword)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Arma (qualquer)' WHERE type = 'Weapon (any)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Arma (flecha)' WHERE type = 'Weapon (arrow)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Arma (adaga)' WHERE type = 'Weapon (dagger)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Arma (azagaia)' WHERE type = 'Weapon (javelin)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Arma (arco longo)' WHERE type = 'Weapon (longbow)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Arma (espada longa)' WHERE type = 'Weapon (longsword)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Arma (maça)' WHERE type = 'Weapon (mace)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Arma (cimitarra)' WHERE type = 'Weapon (scimitar)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Arma (arco curto ou longo)' WHERE type = 'Weapon (shortbow or longbow)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Arma (tridente)' WHERE type = 'Weapon (trident)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Arma (martelo de guerra)' WHERE type = 'Weapon (warhammer)'");
        $this->addSql("UPDATE magic_item SET type_pt = 'Item Maravilhoso' WHERE type = 'Wondrous Item'");

        // Translations for Rarity
        $this->addSql("UPDATE magic_item SET rarity_pt = 'Artefato' WHERE rarity = 'Artifact'");
        $this->addSql("UPDATE magic_item SET rarity_pt = 'Comum' WHERE rarity = 'common'");
        $this->addSql("UPDATE magic_item SET rarity_pt = 'Comum (+1), Incomum (+2), Raro (+3)' WHERE rarity = 'common (+1), uncommon (+2), rare (+3)'");
        $this->addSql("UPDATE magic_item SET rarity_pt = 'Lendário' WHERE rarity IN ('legendary', 'Legendary')");
        $this->addSql("UPDATE magic_item SET rarity_pt = 'Raro' WHERE rarity IN ('Rare', 'rare')");
        $this->addSql("UPDATE magic_item SET rarity_pt = 'Raro (prata ou latão), Muito Raro (bronze) ou Lendário (ferro)' WHERE rarity = 'rare (silver or brass), very rare (bronze) or legendary (iron)'");
        $this->addSql("UPDATE magic_item SET rarity_pt = 'Raridade por estatueta' WHERE rarity = 'rarity by figurine'");
        $this->addSql("UPDATE magic_item SET rarity_pt = 'Raridade varia' WHERE rarity = 'rarity varies'");
        $this->addSql("UPDATE magic_item SET rarity_pt = 'Incomum' WHERE rarity IN ('uncommon', 'Uncommon')");
        $this->addSql("UPDATE magic_item SET rarity_pt = 'Incomum (+1), Raro (+2) ou Muito Raro (+3)' WHERE rarity = 'uncommon (+1), rare (+2), or very rare (+3)'");
        $this->addSql("UPDATE magic_item SET rarity_pt = 'Incomum (+1), Raro (+2) ou Muito Raro (+3)' WHERE rarity = 'uncommon (+1), rare (+2), or very rare (+3)'"); // Duplicate in list, handled
        $this->addSql("UPDATE magic_item SET rarity_pt = 'Incomum (menor), Raro (inferior), Muito Raro (maior)' WHERE rarity = 'uncommon (least), rare (lesser), very rare (greater)'");
        $this->addSql("UPDATE magic_item SET rarity_pt = 'Incomum (prata), Muito Raro (ouro)' WHERE rarity = 'uncommon (silver), very rare (gold)'");
        $this->addSql("UPDATE magic_item SET rarity_pt = 'Incomum, Raro, Muito Raro' WHERE rarity = 'uncommon, rare, very rare'");
        $this->addSql("UPDATE magic_item SET rarity_pt = 'Varia' WHERE rarity = 'varies'");
        $this->addSql("UPDATE magic_item SET rarity_pt = 'Muito Raro' WHERE rarity IN ('Very Rare', 'very rare')");
        $this->addSql("UPDATE magic_item SET rarity_pt = 'Muito Raro ou Lendário' WHERE rarity = 'very rare or legendary'");

        // Translations for Requires Attunement
        $this->addSql("UPDATE magic_item SET requires_attunement_pt = 'Requer sintonização' WHERE requires_attunement = 'requires attunement'");
        $this->addSql("UPDATE magic_item SET requires_attunement_pt = 'Requer sintonização' WHERE requires_attunement = 'requires attunementrequires attunement'");
        $this->addSql("UPDATE magic_item SET requires_attunement_pt = 'Requer sintonização por um bardo, clérigo, druida, feiticeiro, bruxo ou mago' WHERE requires_attunement = 'requires attunement by a bard, cleric, druid, sorcerer, warlock, or wizard'");
        $this->addSql("UPDATE magic_item SET requires_attunement_pt = 'Requer sintonização por um clérigo, druida ou paladino' WHERE requires_attunement = 'requires attunement by a cleric, druid, or paladin'");
        $this->addSql("UPDATE magic_item SET requires_attunement_pt = 'Requer sintonização por uma criatura de tendência boa' WHERE requires_attunement = 'requires attunement by a creature of good alignment'");
        $this->addSql("UPDATE magic_item SET requires_attunement_pt = 'Requer sintonização por um druida, feiticeiro, bruxo ou mago' WHERE requires_attunement = 'requires attunement by a druid, sorcerer, warlock, or wizard'");
        $this->addSql("UPDATE magic_item SET requires_attunement_pt = 'Requer sintonização por um monge' WHERE requires_attunement = 'requires attunement by a monk'");
        $this->addSql("UPDATE magic_item SET requires_attunement_pt = 'Requer sintonização por um paladino' WHERE requires_attunement = 'requires attunement by a paladin'");
        $this->addSql("UPDATE magic_item SET requires_attunement_pt = 'Requer sintonização por um feiticeiro, bruxo ou mago' WHERE requires_attunement = 'requires attunement by a sorcerer, warlock, or wizard'");
        $this->addSql("UPDATE magic_item SET requires_attunement_pt = 'Requer sintonização por um conjurador' WHERE requires_attunement = 'requires attunement by a spellcaster'");
        $this->addSql("UPDATE magic_item SET requires_attunement_pt = 'Requer sintonização ao ar livre à noite' WHERE requires_attunement = 'requires attunement outdoors at night'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE magic_item DROP type_pt, DROP rarity_pt, DROP requires_attunement_pt');
    }
}
