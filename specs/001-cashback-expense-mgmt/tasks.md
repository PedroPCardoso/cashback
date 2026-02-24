# Tasks: Cashback Expense Management System

**Input**: Design documents from `/specs/001-cashback-expense-mgmt/`  
**Branch**: `001-cashback-expense-mgmt`  
**Plan**: [plan.md](./plan.md) · **Spec**: [spec.md](./spec.md)

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.  
**Tests**: TDD — write tests first (red), then implement (green). Requested explicitly in plan.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies on incomplete tasks)
- **[Story]**: Which user story ([US1]–[US4])
- Exact file paths included in every task description

---

## Phase 1: Setup — Project Bootstrap

**Purpose**: Create Laravel 12 project inside Docker, install all tooling, establish the DDD directory skeleton.

- [ ] T001 Create Laravel 12 project via Composer: `composer create-project laravel/laravel cashback-app` under `cashback/` (or confirm root target)
- [ ] T002 Write `Dockerfile` (php:8.3-fpm-alpine, install `pdo_pgsql`, `pcntl`, `bcmath`, `redis` ext, Composer install) at project root
- [ ] T003 Write `docker-compose.yml` with services: `app` (PHP-FPM), `nginx` (port 8080), `postgres:16-alpine`, `redis:7-alpine` with named volume `pgdata` at project root
- [ ] T004 [P] Write `docker-compose.override.yml` for local dev (bind-mount source, install dev deps) at project root
- [ ] T005 [P] Write `nginx.conf` with `fastcgi_pass app:9000` and `root /var/www/public` at project root
- [ ] T006 Verify `docker compose up -d` starts all services; confirm `http://localhost:8080` returns Laravel welcome
- [ ] T007 [P] Configure `.env` and `.env.example`: set `DB_CONNECTION=pgsql`, `DB_HOST=postgres`, `DB_DATABASE=cashback`, `DB_USERNAME=cashback`, `DB_PASSWORD=secret`, `CACHE_DRIVER=redis`
- [ ] T008 [P] Install dev dependencies inside container: `composer require --dev phpunit/phpunit mockery/mockery larastan/larastan laravel/pint`
- [ ] T009 [P] Create `phpstan.neon` at project root: `level: 8`, paths `[app, tests]`, baseline empty
- [ ] T010 [P] Create `pint.json` at project root: preset `laravel` (PSR-12)
- [ ] T011 Create DDD directory skeleton: `app/Domain/`, `app/Application/`, `app/Infrastructure/`, `app/Providers/` with `.gitkeep` files
- [ ] T012 Register `App\Providers\DomainServiceProvider` in `bootstrap/providers.php`; create empty `DomainServiceProvider.php` in `app/Providers/`
- [ ] T013 Verify `php artisan test` runs (zero tests, zero failures); verify `./vendor/bin/pint --test` passes; verify `./vendor/bin/phpstan analyse` passes on empty skeleton

**Checkpoint ✅**: Docker stack running, tooling green, DDD skeleton in place.

---

## Phase 2: Foundational — Domain Layer + Database

**Purpose**: All domain entities, value objects, domain services, repository interfaces, migrations, Eloquent models, and infrastructure bindings that every user story depends on.

⚠️ **CRITICAL**: No user story work begins until this phase is complete.

### Value Objects (parallelizable)

- [ ] T014 [P] Create `app/Domain/Transaction/ValueObjects/Money.php` — immutable VO: `amountCents` (positive int), `currency` (CHAR 3, default BRL); methods: `add()`, `subtract()`, `equals()`, strict types
- [ ] T015 [P] Create `app/Domain/Transaction/ValueObjects/TransactionDate.php` — immutable VO wrapping `DateTimeImmutable`; exposes `yearMonth(): string` (format `YYYY-MM`), `isSameMonth(TransactionDate): bool`
- [ ] T016 [P] Create `app/Domain/Category/ValueObjects/CategoryType.php` — PHP 8.3 Enum: `DEFAULT`, `CUSTOM`
- [ ] T017 [P] Create `app/Domain/Category/ValueObjects/CategoryStatus.php` — PHP 8.3 Enum: `WITHIN_LIMIT`, `EXCEEDED`
- [ ] T018 [P] Create `app/Domain/Category/ValueObjects/CashbackRate.php` — immutable VO: `rate` (float 0.00–100.00); throws `InvalidArgumentException` on out-of-range
- [ ] T019 [P] Create `app/Domain/Category/ValueObjects/MonthlyLimit.php` — immutable nullable VO wrapping `Money`; `isUnlimited(): bool`

### Domain Entities

- [ ] T020 Create `app/Domain/Category/Entities/Category.php` — Aggregate Root: `id` (string UUID), `name` (string), `type` (CategoryType), `monthlyLimit` (?MonthlyLimit), `cashbackRate` (CashbackRate), `keywords` (array of `['keyword'=>string, 'priority'=>int]`); methods: `isDefault()`, `canBeDeleted()`, `addKeyword()`, `removeKeyword()`, `updateSettings()`
- [ ] T021 Create `app/Domain/Transaction/Entities/Transaction.php` — Aggregate Root: `id` (string UUID), `description` (string), `value` (Money), `date` (TransactionDate), `categoryId` (string); methods: `changeCategory()`, `changeValue()`, `changeDate()`
- [ ] T022 Create `app/Domain/Cashback/Entities/MonthlySummary.php` — `categoryId` (string), `year` (int), `month` (int), `totalSpent` (Money), `cashbackEarned` (Money), `status` (CategoryStatus); methods: `applyTransaction()`, `reverseTransaction()`, `recalculate()`

### Repository Interfaces

- [ ] T023 [P] Create `app/Domain/Category/Repositories/CategoryRepositoryInterface.php`: `findById(string): ?Category`, `findAll(): array`, `save(Category): void`, `delete(string): void`
- [ ] T024 [P] Create `app/Domain/Transaction/Repositories/TransactionRepositoryInterface.php`: `findById(string): ?Transaction`, `findByMonth(string $categoryId, int $year, int $month): array`, `save(Transaction): void`, `delete(string): void`, `findAll(?string $categoryId, ?int $year, ?int $month): array`
- [ ] T025 [P] Create `app/Domain/Cashback/Repositories/MonthlySummaryRepositoryInterface.php`: `findOrCreate(string $categoryId, int $year, int $month): MonthlySummary`, `save(MonthlySummary): void`, `findByMonth(int $year, int $month): array`

### Domain Services

- [ ] T026 Create `app/Domain/Category/Services/KeywordMatchingService.php` — pure PHP: `match(string $description, array $categories): Category`; case-insensitive substr match, ordered by keyword priority ASC; falls back to "Outros" category
- [ ] T027 Create `app/Domain/Cashback/Services/CashbackCalculationService.php` — pure PHP: `calculate(Money $transactionAmount, MonthlySummary $summary, ?MonthlyLimit $limit, CashbackRate $rate): CashbackResult`; handles unlimited, within-limit, partial (boundary), and exceeded states; stores result as int cents

### Domain Unit Tests (TDD — write FIRST, let them fail)

- [ ] T028 [P] Create `tests/Unit/Domain/Transaction/ValueObjects/MoneyTest.php` — tests: creation, addition, subtraction, rejection of negative values, equality
- [ ] T029 [P] Create `tests/Unit/Domain/Transaction/ValueObjects/TransactionDateTest.php` — tests: `yearMonth()` output, `isSameMonth()` true/false cases
- [ ] T030 [P] Create `tests/Unit/Domain/Category/ValueObjects/CashbackRateTest.php` — tests: valid range 0–100, rejection of negatives and >100
- [ ] T031 [P] Create `tests/Unit/Domain/Category/ValueObjects/MonthlyLimitTest.php` — tests: nullable (unlimited), wraps Money, `isUnlimited()` returns correct result
- [ ] T032 [P] Create `tests/Unit/Domain/Category/Entities/CategoryTest.php` — tests: `isDefault()`, `canBeDeleted()` refuses DEFAULT type, `addKeyword()`/`removeKeyword()` ordering
- [ ] T033 [P] Create `tests/Unit/Domain/Transaction/Entities/TransactionTest.php` — tests: construction, `changeCategory()`, `changeValue()`, `changeDate()`
- [ ] T034 [P] Create `tests/Unit/Domain/Cashback/Entities/MonthlySummaryTest.php` — tests: `applyTransaction()` accumulation, status flip at limit, `reverseTransaction()` restores values
- [ ] T035 Create `tests/Unit/Domain/Category/Services/KeywordMatchingServiceTest.php` — tests: exact match, case-insensitive, priority order (lower int wins), multi-match, no-match returns "Outros"
- [ ] T036 Create `tests/Unit/Domain/Cashback/Services/CashbackCalculationServiceTest.php` — tests: no limit (unlimited), within limit full amount, partial boundary crossing, fully exceeded (zero cashback), zero cashback rate
- [ ] T037 Run `php artisan test tests/Unit` — verify all 28+ unit tests FAIL (red); then implement domain layer until all PASS (green)

### Migrations & Eloquent Infrastructure

- [ ] T038 [P] Create migration `xxxx_create_categories_table.php` in `database/migrations/`: uuid PK, `name`, `type` ENUM, `monthly_limit_cents` BIGINT NULL, `cashback_rate` DECIMAL(5,2), timestamps
- [ ] T039 [P] Create migration `xxxx_create_category_keywords_table.php`: uuid PK, `category_id` UUID FK CASCADES, `keyword` VARCHAR(100), `priority` SMALLINT
- [ ] T040 [P] Create migration `xxxx_create_transactions_table.php`: uuid PK, `description`, `amount_cents` BIGINT, `currency` CHAR(3) DEFAULT 'BRL', `transacted_at` DATE, `category_id` UUID FK, timestamps
- [ ] T041 [P] Create migration `xxxx_create_monthly_summaries_table.php`: uuid PK, `category_id` UUID FK, `year` SMALLINT, `month` TINYINT, `total_spent_cents`, `cashback_earned_cents` BIGINT, `status` ENUM, UNIQUE(`category_id`, `year`, `month`)
- [ ] T042 Run `docker compose exec app php artisan migrate` — verify all 4 tables created in PostgreSQL
- [ ] T043 [P] Create `app/Infrastructure/Models/CategoryModel.php` — Eloquent model: `$table='categories'`, `$keyType='string'`, `$fillable=[...]`, `$casts=[type=>CategoryType::class, ...]`, `hasMany(CategoryKeywordModel)`
- [ ] T044 [P] Create `app/Infrastructure/Models/CategoryKeywordModel.php` — Eloquent model: `$table='category_keywords'`, `belongsTo(CategoryModel)`, `$fillable`
- [ ] T045 [P] Create `app/Infrastructure/Models/TransactionModel.php` — Eloquent model: `$table='transactions'`, UUID key, `$fillable`, `belongsTo(CategoryModel)`, cast `amount_cents` to int
- [ ] T046 [P] Create `app/Infrastructure/Mappers/CategoryMapper.php` — `toEntity(CategoryModel): Category` and `toModel(Category): CategoryModel`
- [ ] T047 [P] Create `app/Infrastructure/Mappers/TransactionMapper.php` — `toEntity(TransactionModel): Transaction` and `toModel(Transaction): TransactionModel`
- [ ] T048 [P] Create `app/Infrastructure/Mappers/MonthlySummaryMapper.php`
- [ ] T049 Create `app/Infrastructure/Repositories/EloquentCategoryRepository.php` — implements `CategoryRepositoryInterface`; uses `CategoryModel` + `CategoryMapper`
- [ ] T050 Create `app/Infrastructure/Repositories/EloquentTransactionRepository.php` — implements `TransactionRepositoryInterface`
- [ ] T051 Create `app/Infrastructure/Repositories/EloquentMonthlySummaryRepository.php` — implements `MonthlySummaryRepositoryInterface`; `findOrCreate` uses `firstOrNew`
- [ ] T052 Bind all interfaces to implementations in `app/Providers/DomainServiceProvider.php`

### Contract Tests

- [ ] T053 [P] Create `tests/Feature/Contract/CategoryRepositoryContractTest.php` — uses `RefreshDatabase`; tests all methods of `CategoryRepositoryInterface` against Eloquent impl
- [ ] T054 [P] Create `tests/Feature/Contract/TransactionRepositoryContractTest.php` — tests `findByMonth`, `save`, `delete` against real PostgreSQL
- [ ] T055 [P] Create `tests/Feature/Contract/MonthlySummaryRepositoryContractTest.php` — tests `findOrCreate` idempotency, `save`, `findByMonth`
- [ ] T056 Run `php artisan test tests/Feature/Contract` — verify all contract tests PASS

### Seeder & Sanctum

- [ ] T057 Create `database/seeders/DefaultCategoriesSeeder.php` — seeds 5 categories (Alimentação, Transporte, Saúde, Lazer, Outros) with keywords and `type=DEFAULT`; wire into `DatabaseSeeder`
- [ ] T058 Run `php artisan db:seed` — verify 5 default categories in `categories` table
- [ ] T059 Install Laravel Sanctum: `composer require laravel/sanctum`; publish config; run `php artisan migrate`; create `personal_access_tokens` table; add `HasApiTokens` to User model; configure Sanctum middleware in `bootstrap/app.php`
- [ ] T060 Add `POST /api/v1/auth/token` route that accepts `email`+`password` and returns a Sanctum token (for local dev testing convenience)

**Checkpoint ✅**: Domain fully tested, DB migrated, 5 default categories seeded, repositories contracted. User story work can begin.

---

## Phase 3: User Story 1 — Register and Auto-Categorize a Transaction (Priority: P1) 🎯 MVP

**Goal**: User POSTs transaction (description, amount, date) → system auto-assigns category → saves transaction → updates `monthly_summaries` → returns categorized result.

**Independent Test**: `POST /api/v1/transactions` with `{"description":"Almoço restaurante","amount":4500,"currency":"BRL","transacted_at":"2026-02-24"}` → response contains `category.name = "Alimentação"`.

### Tests for US1

- [ ] T061 [P] [US1] Create `tests/Unit/Application/Transaction/RegisterTransactionUseCaseTest.php` — unit test using in-memory/mock repositories; tests: correct category assigned, cashback computed, summary updated, unknown keyword → "Outros"
- [ ] T062 [P] [US1] Create `tests/Feature/Api/V1/TransactionApiTest.php` — feature tests: `POST /api/v1/transactions` happy path, missing fields → 422, bad amount (negative) → 422, auto-categorization by keyword

### Implementation for US1

- [ ] T063 [P] [US1] Create `app/Application/Transaction/DTOs/RegisterTransactionDTO.php` — `description: string`, `amountCents: int`, `currency: string`, `transactedAt: DateTimeImmutable`
- [ ] T064 [US1] Create `app/Application/Transaction/UseCases/RegisterTransactionUseCase.php` — orchestrates: `KeywordMatchingService::match()` → create `Transaction` → compute cashback via `CashbackCalculationService` → `TransactionRepository::save()` → `MonthlySummaryRepository::save()` → return `Transaction`
- [ ] T065 [US1] Create `app/Http/Requests/StoreTransactionRequest.php` — validates: `description` required string max 255, `amount` required int min 1, `currency` optional string size 3, `transacted_at` required date
- [ ] T066 [US1] Create `app/Http/Resources/TransactionResource.php` — JSON: `id`, `description`, `amount_cents`, `currency`, `transacted_at`, `category` (id, name), `cashback_earned_cents`, timestamps
- [ ] T067 [US1] Create `app/Http/Controllers/Api/V1/TransactionController.php` — thin: `store()` calls `RegisterTransactionUseCase->execute(DTO)`, returns `TransactionResource` with HTTP 201
- [ ] T068 [US1] Register route in `routes/api.php`: `Route::apiResource('transactions', TransactionController::class)` under `prefix('v1')->middleware('auth:sanctum')`
- [ ] T069 [US1] Run `php artisan test tests/Unit/Application tests/Feature/Api/V1/TransactionApiTest.php` — verify all US1 tests PASS

**Checkpoint ✅**: US1 fully functional — transaction registered, categorized, cashback computed, summary updated.

---

## Phase 4: User Story 2 — Configure Monthly Limits and Cashback Rates (Priority: P2)

**Goal**: User can list categories and CRUD their monthly limit and cashback rate. Default categories cannot be deleted.

**Independent Test**: `PUT /api/v1/categories/{id}` sets limit=50000 and cashback_rate=3.00 → `GET /api/v1/categories/{id}` returns updated values. `DELETE /api/v1/categories/{default-id}` → HTTP 422.

### Tests for US2

- [ ] T070 [P] [US2] Create `tests/Unit/Application/Category/CreateCategoryUseCaseTest.php` — tests: creates with name + keywords, rejects duplicate name
- [ ] T071 [P] [US2] Add category API tests to `tests/Feature/Api/V1/CategoryApiTest.php`: `GET /api/v1/categories`, `POST` create custom, `PUT` update limit+rate, `DELETE` custom → 200, `DELETE` default → 422

### Implementation for US2

- [ ] T072 [P] [US2] Create `app/Application/Category/DTOs/CreateCategoryDTO.php` — `name: string`, `keywords: array`, `monthlyLimitCents: ?int`, `cashbackRate: float`
- [ ] T073 [P] [US2] Create `app/Application/Category/DTOs/UpdateCategoryDTO.php` — same fields all optional
- [ ] T074 [US2] Create `app/Application/Category/UseCases/ListCategoriesUseCase.php` — returns all `Category` entities ordered by type (DEFAULT first) then name
- [ ] T075 [US2] Create `app/Application/Category/UseCases/CreateCategoryUseCase.php` — validates uniqueness, creates `Category` with `type=CUSTOM`, persists via `CategoryRepository::save()`
- [ ] T076 [US2] Create `app/Application/Category/UseCases/UpdateCategoryUseCase.php` — finds category, calls `updateSettings()`, saves; updates `category_keywords` records
- [ ] T077 [US2] Create `app/Application/Category/UseCases/DeleteCategoryUseCase.php` — throws `\DomainException` if `category->isDefault()`; calls `CategoryRepository::delete()`
- [ ] T078 [US2] Create `app/Http/Requests/StoreCategoryRequest.php` — `name` required unique, `keywords` required array min 1, `cashback_rate` numeric 0–100, `monthly_limit_cents` nullable int min 1
- [ ] T079 [US2] Create `app/Http/Requests/UpdateCategoryRequest.php` — all optional with same validation rules
- [ ] T080 [US2] Create `app/Http/Resources/CategoryResource.php` — JSON: `id`, `name`, `type`, `monthly_limit_cents`, `cashback_rate`, `keywords` (array of `{keyword, priority}`), timestamps
- [ ] T081 [US2] Create `app/Http/Controllers/Api/V1/CategoryController.php` — `index()`, `show()`, `store()`, `update()`, `destroy()`; each delegates to corresponding use case; returns `CategoryResource`
- [ ] T082 [US2] Register routes in `routes/api.php`: `Route::apiResource('categories', CategoryController::class)` under `v1` + Sanctum
- [ ] T083 [US2] Run `php artisan test tests/Unit/Application/Category tests/Feature/Api/V1/CategoryApiTest.php` — all PASS

**Checkpoint ✅**: US2 — categories fully manageable, limits and rates configurable.

---

## Phase 5: User Story 3 — View Monthly Cashback Summary (Priority: P3)

**Goal**: User can view aggregated monthly per-category totals: `total_spent`, `cashback_earned`, `status`.

**Independent Test**: After seeding transactions, `GET /api/v1/summary?year=2026&month=2` returns one row per category with correct totals and status.

### Tests for US3

- [ ] T084 [P] [US3] Add summary endpoint tests to `tests/Feature/Api/V1/CategoryApiTest.php` (or new `SummaryApiTest.php`): within-limit totals correct, exceeded status present, empty month returns zeroed rows, month reset scenario

### Implementation for US3

- [ ] T085 [US3] Create `app/Application/Transaction/DTOs/MonthlySummaryDTO.php` — `categoryId`, `categoryName`, `year`, `month`, `totalSpentCents`, `cashbackEarnedCents`, `status`
- [ ] T086 [US3] Create `app/Application/Transaction/UseCases/GetMonthlySummaryUseCase.php` — calls `MonthlySummaryRepository::findByMonth(year, month)`; for categories with no summary row returns zero-filled DTO; returns collection sorted by category name
- [ ] T087 [US3] Create `app/Http/Resources/MonthlySummaryResource.php` — JSON: `category_id`, `category_name`, `year`, `month`, `total_spent_cents`, `cashback_earned_cents`, `status`
- [ ] T088 [US3] Add `GET /api/v1/summary` route in `routes/api.php` (not a resource; single `__invoke` controller or closure) — accepts `?year` and `?month` query params (default: current month); protected by Sanctum
- [ ] T089 [US3] Create `app/Http/Controllers/Api/V1/SummaryController.php` — `__invoke()` validates query params, calls `GetMonthlySummaryUseCase`, returns `MonthlySummaryResource::collection()`
- [ ] T090 [US3] Run `php artisan test tests/Feature/Api/V1/SummaryApiTest.php` — all PASS

**Checkpoint ✅**: US3 — monthly summary readable with correct cashback totals and status per category.

---

## Phase 6: User Story 4 — Edit, Delete and Re-Categorize Transactions with Retroactive Recalculation (Priority: P4)

**Goal**: User can edit or delete any transaction, or manually re-assign its category; all affected monthly summaries are retroactively recomputed.

**Independent Test**: Change a transaction that caused "Excedido" → delete it → `GET /api/v1/summary` shows status reverts to `within_limit` with recalculated cashback.

### Tests for US4

- [ ] T091 [P] [US4] Create `tests/Unit/Application/Transaction/UpdateTransactionUseCaseTest.php` — tests: value change recalculates summary, date change moves transaction between months (both affected), recategorize updates both categories
- [ ] T092 [P] [US4] Add tests to `tests/Feature/Api/V1/TransactionApiTest.php`: `PUT /api/v1/transactions/{id}` happy path, `DELETE` recalculates summary, `PATCH /recategorize` re-assigns and recalculates

### Implementation for US4

- [ ] T093 [P] [US4] Create `app/Application/Transaction/DTOs/UpdateTransactionDTO.php` — all optional: `description: ?string`, `amountCents: ?int`, `currency: ?string`, `transactedAt: ?DateTimeImmutable`
- [ ] T094 [P] [US4] Create `app/Application/Transaction/DTOs/RecategorizationDTO.php` — `transactionId: string`, `newCategoryId: string`
- [ ] T095 [US4] Create `app/Application/Transaction/UseCases/UpdateTransactionUseCase.php` — fetch original; compute affected months; update `Transaction` entity; replay all transactions for affected category+month pairs via `CashbackCalculationService`; persist summary and transaction
- [ ] T096 [US4] Create `app/Application/Transaction/UseCases/DeleteTransactionUseCase.php` — fetch transaction; delete from repository; replay remaining transactions for affected category+month; persist updated summary
- [ ] T097 [US4] Create `app/Application/Transaction/UseCases/RecategorizeTransactionUseCase.php` — move transaction to new category; replay summaries for both original and new category in the transaction's month
- [ ] T098 [US4] Create `app/Http/Requests/UpdateTransactionRequest.php` — all optional, same rules as `StoreTransactionRequest`
- [ ] T099 [US4] Create `app/Http/Requests/RecategorizeTransactionRequest.php` — `category_id` required UUID exists in `categories` table
- [ ] T100 [US4] Add `update()` and `destroy()` to `app/Http/Controllers/Api/V1/TransactionController.php` — delegate to use cases; return `TransactionResource` or HTTP 204
- [ ] T101 [US4] Add `PATCH /api/v1/transactions/{id}/recategorize` route in `routes/api.php`; create `RecategorizeTransactionController.php` (single action) delegating to `RecategorizeTransactionUseCase`
- [ ] T102 [US4] Run `php artisan test tests/Unit/Application/Transaction tests/Feature/Api/V1/TransactionApiTest.php` — all PASS

**Checkpoint ✅**: US4 — full CRUD + re-categorization with retroactive recalculation working.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Quality gates, error handling, logging, final test-suite pass with coverage gate.

- [ ] T103 [P] Add structured exception handling in `bootstrap/app.php` — map `\DomainException` → HTTP 422, `\InvalidArgumentException` → HTTP 422, `ModelNotFoundException` → HTTP 404; return JSON error bodies
- [ ] T104 [P] Add rate limiting to all `/api/v1/` routes via `RateLimiter::for('api', ...)` in `bootstrap/app.php` (60 req/min)
- [ ] T105 [P] Add structured JSON logging: configure `LOG_CHANNEL=stack` with Monolog JSON formatter in `config/logging.php` for production
- [ ] T106 [P] Create `app/Console/Commands/RecalculateMonthlySummariesCommand.php` — Artisan command `cashback:recalculate {year} {month}` that replays all transactions for a given month; call use cases via DI
- [ ] T107 [P] Update `database/seeders/DefaultCategoriesSeeder.php` — ensure keywords include common Portuguese terms per category (e.g., Alimentação: ["restaurante", "supermercado", "almoço", "padaria", "lanche"])
- [ ] T108 Run full static analysis: `./vendor/bin/phpstan analyse --memory-limit=512M` — fix all level-8 violations
- [ ] T109 Run code style: `./vendor/bin/pint` — auto-fix all PSR-12 violations; then `./vendor/bin/pint --test` must pass
- [ ] T110 Run `composer audit` — resolve any CVSS ≥ 7.0 vulnerabilities
- [ ] T111 Run full test suite with coverage gate: `php artisan test --coverage --min=80` — must pass; fix any coverage gaps
- [ ] T112 [P] Update `README.md` with: Docker quick-start (`docker compose up -d && docker compose exec app php artisan migrate --seed`), base URL, example curl commands for each endpoint
- [ ] T113 Run Docker end-to-end smoke test: `docker compose up -d` → `php artisan migrate --seed` → `curl http://localhost:8080/api/v1/categories` returns HTTP 200 with 5 categories → `php artisan test --coverage` inside container

**Checkpoint ✅**: All tests green, PHPStan level 8 clear, coverage ≥80%, Docker stack smoke tested.

---

## Dependencies & Execution Order

### Phase Dependencies

```
Phase 1: Setup           → No dependencies; start immediately
Phase 2: Foundational    → Requires Phase 1 complete; BLOCKS all user stories
Phase 3: US1             → Requires Phase 2 complete
Phase 4: US2             → Requires Phase 2 complete (can be parallel with US1)
Phase 5: US3             → Requires Phase 2 + US1 complete (needs transaction data model)
Phase 6: US4             → Requires Phase 3 (US1) complete (extends RegisterTransaction flow)
Phase 7: Polish          → Requires all phases complete
```

### User Story Dependencies

| Story | Depends On | Rationale |
|---|---|---|
| US1 (P1) | Phase 2 only | Core transaction + categorization flow |
| US2 (P2) | Phase 2 only | Category management is independent |
| US3 (P3) | US1 (transaction model must exist) | Summary reads from transactions |
| US4 (P4) | US1 (mutation of existing transactions) | Extends the transaction lifecycle |

### Within Each User Story

1. Write tests FIRST → confirm they FAIL (red)
2. Implement domain objects
3. Implement use cases
4. Implement HTTP layer (requests, controllers, resources)
5. Run tests → confirm all PASS (green)

### Parallel Opportunities

- All `[P]` tasks within a phase can execute concurrently
- Within Phase 2: Value Objects (T014–T019), Repository Interfaces (T023–T025), Migrations (T038–T041), Mappers (T046–T048), Contract Tests (T053–T055) are all fully parallelizable
- US1 and US2 (Phases 3 & 4) can be worked in parallel by separate developers once Phase 2 is complete

---

## Parallel Example: Phase 2 (Foundational)

```bash
# All these can run simultaneously (different files):
T014  Money.php
T015  TransactionDate.php
T016  CategoryType.php
T017  CategoryStatus.php
T018  CashbackRate.php
T019  MonthlyLimit.php
T023  CategoryRepositoryInterface.php
T024  TransactionRepositoryInterface.php
T025  MonthlySummaryRepositoryInterface.php
T038  create_categories_table migration
T039  create_category_keywords_table migration
T040  create_transactions_table migration
T041  create_monthly_summaries_table migration
```

## Parallel Example: User Story 1 (Phase 3)

```bash
# Run in parallel:
T061  Unit test for RegisterTransactionUseCase
T062  Feature test for Transaction API
T063  RegisterTransactionDTO

# Then sequentially:
T064  RegisterTransactionUseCase (depends on T063)
T065  StoreTransactionRequest
T066  TransactionResource
T067  TransactionController (depends on T064–T066)
T068  Route registration
T069  Full US1 test run
```

---

## Implementation Strategy

### MVP First (User Story 1 + 2 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL — blocks all stories)
3. Complete Phase 3: US1 — Register & Auto-Categorize
4. Complete Phase 4: US2 — Category Configuration
5. **STOP and VALIDATE**: `docker compose exec app php artisan test` + smoke test against `localhost:8080`
6. Demo: register a transaction, verify it appears in the correct category with cashback computed

### Incremental Delivery

1. Phase 1 + 2 → Foundation ready (Docker + DB + domain)
2. Phase 3 (US1) → Users can register transactions (MVP!)
3. Phase 4 (US2) → Users can configure limits and rates
4. Phase 5 (US3) → Users can view monthly summaries with cashback
5. Phase 6 (US4) → Users can edit/delete/recategorize with full consistency
6. Phase 7 → Production-ready (PHPStan, coverage, Docker smoke)

---

## Notes

- All amounts stored as integer cents (`amount_cents`, `monthly_limit_cents`, etc.) — no floats in DB
- `[P]` tasks = independent files, safe to parallelise
- `[US1]`–`[US4]` labels map to spec.md user stories by priority
- TDD: every test file must be created and RUN FAILING before writing implementation code
- Conventional Commits for every task group: `feat(transaction): register transaction with auto-categorization`
- Coverage gate is enforced in CI: `php artisan test --coverage --min=80`
- Total tasks: **113** · US1: 9 impl + 2 tests · US2: 12 impl + 2 tests · US3: 6 impl + 1 test · US4: 10 impl + 2 tests
