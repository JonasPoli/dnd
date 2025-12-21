1) Command overview
Command name

app:rules:import

Goal

Import content from one or more external sources (e.g., Open5e repo dumps/API, SRD-derived JSON) into your Doctrine entities, with:

Idempotency: running again does not duplicate data.

Incremental updates: only inserts missing, updates changed via content hash.

Optional full sync: detect deletions and mark as inactive/deleted without breaking characters.

Safe batching: chunk flushes for memory/performance.

Detailed report: inserted/updated/skipped/deleted + errors.

2) CLI interface
Basic usage
php bin/console app:rules:import --source=open5e --dataset=repo --entity=all

Options
Required/primary

--source= string
Values: open5e, srd-5-2, custom-json, etc.

--dataset= string
Values:

repo (read local JSON dumps)

api (fetch from REST API)

file (single file import)

--entity= string
Values:

all

classes

subclasses

class-levels

features

species

backgrounds

spells

equipment

languages

skills

trinkets

Input location

--path= string (for dataset=repo or dataset=file)

Example: --path=/var/data/open5e/data/

Example: --path=/var/data/srd-5-2/normalized.json

Incremental behavior

--mode= string (default: incremental)
Values:

incremental (upsert only new/changed)

full (upsert + mark missing as deleted/inactive)

dry-run (no DB writes, only report)

Deletion strategy (only for --mode=full)

--deletions= string (default: soft)
Values:

soft (set ExternalReference.status=deleted and/or entity.isActive=false)

none (never mark missing as deleted)

Performance / safety

--chunk= int (default: 200)

--memory-limit= string (default: keep php.ini) (optional)

--stop-on-error (bool)

--only-changed (bool, default true)

--clear-em (bool, default true) (Doctrine clear between chunks)

Filtering

--since= datetime (optional)
Only meaningful for API sources supporting “updated since”. For dumps, ignored.

--limit= int (optional)

--offset= int (optional)

Logging

--report= string (optional path to JSON report)

-v/-vv/-vvv Symfony verbosity

3) Output requirements (what the command must print)

At the end, print a summary like:

Source, dataset, mode, entities imported

Per entity type:

seen, inserted, updated, skipped, deleted, errors

Total time + peak memory

If --report=... write a JSON report file with the same stats plus error details.

4) Import architecture inside Symfony
4.1 Components
A) ImportRunner

Orchestrates:

reads CLI options

resolves source adapter

resolves entity importers (registry)

handles chunk flush/clear

aggregates report

B) SourceAdapterInterface

Provides raw records iterator:

Open5eRepoAdapter reads local JSON files

Open5eApiAdapter fetches paginated endpoints

JsonFileAdapter reads one normalized file

Contract:

supports(string $source, string $dataset): bool

iterate(string $entityType, ImportContext $ctx): iterable<array>

C) ImporterInterface

One per entity type (SpellImporter, ClassImporter, etc.)

Contract:

getEntityType(): string

normalize(array $raw, ImportContext $ctx): NormalizedRecord

upsert(NormalizedRecord $record, ImportContext $ctx): UpsertResult

D) ExternalReferenceRepository

Central idempotency lookups:

findOne($sourceId, $entityType, $externalId)

upsertReference(...)

E) Hasher

hashNormalized(NormalizedRecord $r): string (sha256)

5) Data identity rules (critical)

Every imported record MUST yield:

entityType (e.g. spell)

externalId (stable key from source)

normalizedPayload (canonical array)

contentHash = sha256(canonical json)

ExternalId rules

Open5e:

Spells: slug

Monsters: slug

Classes: slug or name normalized

Equipment: slug or name normalized

Backgrounds: slug or name normalized

SRD-derived:

Always generate: srd:{type}:{key}

If a source has no stable id, you MUST define a deterministic “slugify(name)” strategy + keep it forever.

6) Incremental re-import algorithm

This is the exact logic the command must implement.

6.1 For each raw record

Normalize → get externalId + payload

Compute hash

Look up ExternalReference by (source, entityType, externalId)

If not found → INSERT path

Create local entity from payload

Persist entity

Persist new ExternalReference:

contentHash

firstSeenAt=now

lastImportedAt=now

status=active

If found → UPDATE/SKIP path

If --only-changed=true:

If ref.contentHash === hash → SKIP

else UPDATE entity fields + update ref hash + lastImportedAt=now

If --only-changed=false:

Always UPDATE (rarely useful)

Always update “seen” markers

You must also store import-run tracking for full sync deletions (next section).

7) Full sync mode (detect deletions)

Full sync means: after importing all records of an entity type, anything that existed before but was not present in this run is considered removed.

7.1 How to track “seen in this run”

Create a table:

Entity: ImportRun

id

source

mode

startedAt, finishedAt

optionsJson

status (success/failed)

Entity: ImportRunSeen

id

importRun: ManyToOne ImportRun

entityType

externalId

Unique: (import_run_id, entityType, externalId)

During import

For each processed record:

insert into ImportRunSeen (batch insert)

or store seen IDs in memory if dataset is small (not recommended)

7.2 After finishing an entityType in full mode

Query:

all ExternalReference for (source, entityType, status=active)

left join ImportRunSeen for the current run

anything missing → mark deleted

Deletion actions (--deletions=soft)

set ExternalReference.status = deleted

set local entity isActive=false (recommended field on each imported entity)

do NOT hard delete if characters might reference it

8) Import order (must be enforced)

Default when --entity=all:

abilities (static seed, not from open5e usually)

skills

languages

classes

subclasses

species

backgrounds

equipment

spells

features

class-levels (+ join to features)

join tables (spell-classes, etc.)

Reason: foreign keys and owner relations.

9) Chunking, transactions, and Doctrine memory control
9.1 Chunk flush

Buffer records processed count

Every --chunk records:

$em->flush()

if --clear-em: $em->clear() but keep references you need (RulesSource cached IDs)

9.2 Transaction strategy

One transaction per entity type import:

begin

process all records

flush

if full mode: apply deletions + flush

commit

If --stop-on-error:

rollback immediately and fail exit code != 0

10) Error handling requirements

For each record:

catch normalization or DB exceptions

append to report:

entityType

externalId (if available)

error message

optional excerpt of raw record (sanitized)

continue unless --stop-on-error

Exit code:

0 success (even with some errors if not stop-on-error)

1 failed run (stop-on-error or fatal)

11) Example runs
First import (repo dumps)
php bin/console app:rules:import \
  --source=open5e --dataset=repo --path=/srv/open5e/data \
  --entity=all --mode=incremental --chunk=200 -vv

Re-import later (only changed)
php bin/console app:rules:import \
  --source=open5e --dataset=repo --path=/srv/open5e/data \
  --entity=spells --mode=incremental --only-changed=1 -v

Full sync (mark missing as deleted)
php bin/console app:rules:import \
  --source=open5e --dataset=api \
  --entity=spells --mode=full --deletions=soft --chunk=100 -vv

Dry run
php bin/console app:rules:import \
  --source=open5e --dataset=api --entity=classes --mode=dry-run -vvv

12) Minimum “done” checklist (acceptance criteria)

Your command is correct when:

Running twice does not duplicate records.

If a record changes externally, your run:

detects hash change,

updates entity,

updates ExternalReference.contentHash.

In full mode, missing records are marked deleted (soft).

A JSON report can be generated with correct counts.

Import doesn’t blow memory (chunk flush + clear works).