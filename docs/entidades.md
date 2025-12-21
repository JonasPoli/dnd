1) Core idea: separate “Rules Content” from “Characters”

You’ll have two big domains:

A) Rules Content (imported, versioned)

Classes, features, species, backgrounds, spells, equipment, etc.

B) Characters (user-created instances)

A character references imported content, plus stores choices and derived stats.

This separation makes imports safe: you can refresh rules content without breaking characters.

2) Entity model (Doctrine Entities) and relations
2.1 RulesSource (where data came from)

Entity: RulesSource

id: uuid/int

slug: string (unique) — e.g. open5e, srd-5-2

name: string

license: string (e.g. CC-BY-4.0)

versionLabel: string|null (e.g. 5.2)

originUrl: text|null

createdAt, updatedAt

Relations

One RulesSource → Many imported records (ClassDef, Spell, etc.)

Why: lets you keep multiple sources cleanly + audit licensing.

2.2 ExternalReference (idempotency backbone)

Entity: ExternalReference (the “import control index”)

id

source: ManyToOne RulesSource

entityType: string (e.g. spell, class_def, feature)

externalId: string (stable key from dataset; for Open5e: slug/url)

localEntityId: int (or a polymorphic mapping via target tables)

contentHash: string (sha256 of normalized JSON)

lastImportedAt: datetime

firstSeenAt: datetime

status: string (active, deleted, ignored)

Unique constraint: (source_id, entityType, externalId)

This is what makes re-import “only what’s missing/changed” trivial.

Alternative: you can embed externalId/source fields inside every entity, but the ExternalReference approach is cleaner and centralizes logic.

2.3 Class definitions and level progressions

Entity: ClassDef

id

source: ManyToOne RulesSource

key: string (unique per source) — barbarian

name: string

hitDie: int

descriptionMd: text|null

primaryAbilities: json

savingThrowProficiencies: json

createdAt, updatedAt

Relations

One ClassDef → Many ClassLevel

One ClassDef → Many Feature (owner)

One ClassDef → Many SubclassDef

Entity: SubclassDef

id

source

classDef: ManyToOne ClassDef

key: string

name: string

availableFromLevel: int

descriptionMd: text|null

Relations

One SubclassDef → Many Feature

Entity: ClassLevel

id

classDef: ManyToOne ClassDef

level: int (1..20)

proficiencyBonus: int

spellSlotsJson: json|null

notesMd: text|null

Relations

Many-to-many ClassLevel ↔ Feature (features gained at that level)

Use join entity ClassLevelFeature (recommended, extensible)

Entity: ClassLevelFeature

id

classLevel: ManyToOne ClassLevel

feature: ManyToOne Feature

2.4 Feature (unified “thing that grants rules”)

Entity: Feature

id

source

ownerType: string (class, subclass, species, background, feat, item)

ownerId: int (or use real relations via nullable ManyToOne fields)

key: string

name: string

levelRequired: int|null

descriptionMd: text

grantsJson: json|null (very important — structured “what this feature grants”)

Why grantsJson matters
It lets your app apply proficiencies/bonuses without hardcoding text parsing.

Example grants:

{
  "proficiencies": [{"type":"skill","ref":"athletics"}],
  "bonus": [{"target":"ac","value":1,"condition":"not_wearing_armor"}],
  "choices": [{"choose":2,"fromSkills":["athletics","perception"]}]
}

2.5 Species, background, languages, skills, spells, equipment

Entity: Species

id, source, key, name

size: string

speedWalk: int

descriptionMd: text|null

Relations

One Species → Many Feature (traits)

(Optional) Many-to-many Species ↔ Language (base languages)

Entity: Background

id, source, key, name

descriptionMd

grantsJson (skills/tools/languages/equipment/feat)

Entity: Language

id, source, key, name

script, notes

Entity: Ability

id, key (STR/DEX/CON/INT/WIS/CHA), name

Entity: Skill

id, key, name

abilityKey (or ManyToOne Ability)

Entity: Spell

id, source, key, name

level, school, castingTime, range

componentsJson, duration

descriptionMd, higherLevelsMd

Relations

Many-to-many Spell ↔ ClassDef (available spells)

Join: SpellClass

Entity: Equipment

id, source, key, name

type (weapon/armor/gear/tool)

costGp: decimal, weightLb: decimal

propertiesJson, descriptionMd

Entity: Trinket

id, source, rollKey (d100 int), textMd

2.6 Characters (the “game state” your user creates)

Entity: Character

id

name

level (default 1)

classDef: ManyToOne ClassDef

subclassDef: ManyToOne SubclassDef|null

species: ManyToOne Species

background: ManyToOne Background

alignment: string|null

createdAt, updatedAt

Entity: CharacterAbilityScore

composite key: character + abilityKey

score: int

Entity: CharacterProficiency

id

character

type (skill/save/weapon/armor/tool/language)

refKey (e.g. athletics, STR, common)

sourceText (“class”, “background”, “feature:rage”)

Entity: CharacterFeature

id

character

feature

gainedAtLevel

Entity: CharacterSpell

id

character

spell

learnedAtLevel

prepared: bool

Entity: CharacterItem

id

character

equipment

qty

notes

Entity: CharacterChoice (audit log of wizard decisions)

id

character

stepKey

choiceKey

valueJson

createdAt

This structure lets you generate your “rulebook of the character” by merging:

class/species/background/features + rules_text explanations + the character’s choices.

3) Import architecture in Symfony
3.1 Use a “staging → normalize → upsert” approach

Avoid importing “raw JSON” directly into entities.

Pipeline

Fetch external data (Open5e API or repo dumps)

Normalize into internal DTOs (your canonical shape)

Upsert entities using (source, key) plus ExternalReference

This prevents schema drift and keeps you in control.

3.2 ImportCommand + ImportService

Create a Symfony Console command:

app:rules:import --source=open5e --entity=spells

app:rules:import --source=srd-5-2 --all

app:rules:import --source=open5e --since=2025-12-01 (optional)

Internally:

ImporterRegistry picks the correct importer for each entity type.

Each importer implements:

fetch(): iterable<array>

normalize(array $raw): NormalizedRecord

upsert(NormalizedRecord $r): void

4) Incremental import (import only missing/changed)
4.1 Stable identity

For each imported record you must define a stable external identity:

externalId = Open5e slug (or url) for spells/monsters/etc.

For SRD you may generate: srd:{type}:{key}

4.2 Content hash

Compute a hash of the normalized record, not the raw input.
Example:

sort keys

remove irrelevant fields (timestamps, links)

then sha256(json_encode($normalized, JSON_UNESCAPED_UNICODE))

4.3 Upsert algorithm (idempotent)

For each normalized record:

Look up ExternalReference by (source, entityType, externalId)

If not found:

create entity + create ExternalReference

If found:

compare contentHash

if same → skip

if different → update entity + update hash/time

Pseudocode
$ref = $extRefRepo->findOne($source, $type, $externalId);

if (!$ref) {
   $entity = $this->createEntity($record);
   $em->persist($entity);

   $ref = new ExternalReference($source, $type, $externalId, $entity->getId(), $hash);
   $em->persist($ref);
} else {
   if ($ref->getContentHash() === $hash) {
      return; // unchanged
   }
   $entity = $this->loadEntityByRef($ref);
   $this->updateEntity($entity, $record);
   $ref->setContentHash($hash);
   $ref->setLastImportedAt(new \DateTimeImmutable());
}

4.4 Handling deletions

Some sources remove items. You have two choices:

Soft delete in ExternalReference.status = deleted when an entity no longer appears in a full import.

Keep local entity but mark as inactive.

This is safer because your characters might reference old content.

5) Practical details you’ll want in Doctrine
5.1 Unique indexes (critical)

RulesSource.slug unique

ClassDef (source_id, key) unique

Spell (source_id, key) unique

same for Species/Background/Feature/Equipment

ExternalReference (source_id, entityType, externalId) unique

5.2 Batch imports safely

Wrap each “entity type import” in a transaction

Flush in chunks (ex: 200 records) to control memory

Optionally use Doctrine clear() carefully (don’t clear RulesSource)

6) What “system existente” usually means (and how to connect)

If your “existing system” is Open5e:

You can import from API (latest) or from repo dumps (stable snapshot).

Your importers should support both “fetch adapters”.

Recommended:

Use repo dumps for reproducible builds,

API for periodic updates.

7) Suggested import order (prevents FK issues)

RulesSource

Ability, Skill, Language (static)

ClassDef

SubclassDef

Species

Background

Spell

Equipment, Trinket

Feature (because it references owners)

ClassLevel + ClassLevelFeature

SpellClass (join tables)