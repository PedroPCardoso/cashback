# Implementation Plan: Cashback Expense Management System

**Branch**: `001-cashback-expense-mgmt` | **Date**: 2026-02-24 | **Spec**: [spec.md](./spec.md)  
**Input**: Feature specification from `/specs/001-cashback-expense-mgmt/spec.md`

---

## Summary

Build a Laravel 12 RESTful JSON API for a single-user cashback expense management system. The system accepts transactions (description, value, date), auto-categorizes them via keyword rules, enforces configurable per-category monthly spending limits, and computes cashback only within those limits. When a limit is exceeded the category status flips to **Excedido** and cashback stops accruing. All data is permanently persisted in PostgreSQL. The application ships fully Dockerized (app + PostgreSQL) and adheres to the project Constitution (DDD, strict layering, TDD, PSR-12, PHPStan level 8).

---

## Technical Context

| Concern | Choice |
|---|---|
| Language / Version | PHP 8.3 |
| Framework | Laravel 12 (docker) |
| Database | PostgreSQL 16 (Docker) |
| Cache / Sessions | Redis 7 (Docker, optional but included for future use) |
| Queue | Sync (local); Laravel Queue with Redis for future async |
| API Style | RESTful JSON · `/api/v1/` prefix |
| Auth | Laravel Sanctum (single API token per instance; single-user scope) |
| Testing | PHPUnit 11 + Mockery |
| Static Analysis | PHPStan level 8 |
| Code Style | Laravel Pint (PSR-12 preset) |
| Containerization | Docker + Docker Compose (app, postgres, redis) |
| Architecture | DDD — Domain / Application / Infrastructure / Presentation |

---

## Constitution Check

| Principle | Status | Notes |
|---|---|---|
| DDD bounded context (`app/Domain/`) | ✅ Required | Contexts: `Cashback`, `Category`, `Transaction` |
| Layered architecture (strict inward deps) | ✅ Required | Domain → Application → Infrastructure → Presentation |
| No Eloquent in Domain / Application | ✅ Required | Repository pattern enforced |
| Repository interfaces in Domain | ✅ Required | Concrete impls in Infrastructure |
| `declare(strict_types=1)` everywhere | ✅ Required | |
| PHPStan level 8 | ✅ Required | CI gate |
| TDD — unit + feature tests | ✅ Required | 100% Domain layer; ≥80% overall |
| Conventional Commits | ✅ Required | |
| Pint style check | ✅ Required | |
| Value Objects for money | ✅ Required | `Money` VO (amount + currency) |

---

## Project Structure

### Documentation (this feature)

```text
specs/001-cashback-expense-mgmt/
├── spec.md
├── plan.md              ← this file
├── data-model.md        ← Phase 1 output
└── tasks.md             ← /speckit.tasks output
```

### Application Source Tree

```text
app/
├── Domain/
│   ├── Category/
│   │   ├── Entities/
│   │   │   └── Category.php               # Aggregate root (id, name, type, limit, cashbackRate, keywords, status)
│   │   ├── ValueObjects/
│   │   │   ├── CategoryType.php           # Enum: DEFAULT | CUSTOM
│   │   │   ├── CategoryStatus.php         # Enum: WITHIN_LIMIT | EXCEEDED
│   │   │   ├── CashbackRate.php           # VO: 0–100 percentage
│   │   │   └── MonthlyLimit.php           # VO: nullable Money
│   │   ├── Events/
│   │   │   ├── CategoryCreated.php
│   │   │   ├── CategoryUpdated.php
│   │   │   └── CategoryLimitExceeded.php
│   │   ├── Services/
│   │   │   └── KeywordMatchingService.php # Pure PHP: finds best category for a description
│   │   └── Repositories/
│   │       └── CategoryRepositoryInterface.php
│   │
│   ├── Transaction/
│   │   ├── Entities/
│   │   │   └── Transaction.php            # Aggregate root (id, description, value, date, categoryId)
│   │   ├── ValueObjects/
│   │   │   ├── Money.php                  # VO: amount (int cents) + currency
│   │   │   └── TransactionDate.php        # VO: wraps DateTimeImmutable
│   │   ├── Events/
│   │   │   ├── TransactionRegistered.php
│   │   │   ├── TransactionUpdated.php
│   │   │   └── TransactionDeleted.php
│   │   └── Repositories/
│   │       └── TransactionRepositoryInterface.php
│   │
│   └── Cashback/
│       ├── Entities/
│       │   └── MonthlySummary.php         # (categoryId, month, totalSpent, cashbackEarned, status)
│       ├── Services/
│       │   └── CashbackCalculationService.php  # Core rule engine (pure PHP)
│       └── Repositories/
│           └── MonthlySummaryRepositoryInterface.php
│
├── Application/
│   ├── Category/
│   │   ├── DTOs/
│   │   │   ├── CreateCategoryDTO.php
│   │   │   └── UpdateCategoryDTO.php
│   │   └── UseCases/
│   │       ├── CreateCategoryUseCase.php
│   │       ├── UpdateCategoryUseCase.php
│   │       ├── DeleteCategoryUseCase.php
│   │       └── ListCategoriesUseCase.php
│   │
│   └── Transaction/
│       ├── DTOs/
│       │   ├── RegisterTransactionDTO.php
│       │   ├── UpdateTransactionDTO.php
│       │   └── RecategorizationDTO.php
│       └── UseCases/
│           ├── RegisterTransactionUseCase.php   # Auto-categorize + cashback calc
│           ├── UpdateTransactionUseCase.php     # Edit + retroactive recalc
│           ├── DeleteTransactionUseCase.php     # Delete + retroactive recalc
│           ├── RecategorizeTransactionUseCase.php
│           └── ListTransactionsUseCase.php
│
├── Http/
│   ├── Controllers/Api/V1/
│   │   ├── CategoryController.php
│   │   └── TransactionController.php
│   ├── Requests/
│   │   ├── StoreCategoryRequest.php
│   │   ├── UpdateCategoryRequest.php
│   │   ├── StoreTransactionRequest.php
│   │   ├── UpdateTransactionRequest.php
│   │   └── RecategorizeTransactionRequest.php
│   └── Resources/
│       ├── CategoryResource.php
│       ├── TransactionResource.php
│       └── MonthlySummaryResource.php
│
├── Infrastructure/
│   ├── Models/
│   │   ├── CategoryModel.php
│   │   ├── CategoryKeywordModel.php
│   │   └── TransactionModel.php
│   ├── Repositories/
│   │   ├── EloquentCategoryRepository.php
│   │   ├── EloquentTransactionRepository.php
│   │   └── EloquentMonthlySummaryRepository.php
│   └── Mappers/
│       ├── CategoryMapper.php
│       ├── TransactionMapper.php
│       └── MonthlySummaryMapper.php
│
├── Console/
│   └── Commands/
│       └── RecalculateMonthlySummariesCommand.php  # Manual recalc trigger
│
└── Providers/
    └── DomainServiceProvider.php   # All interface→implementation bindings

tests/
├── Unit/
│   ├── Domain/
│   │   ├── Category/
│   │   │   ├── CategoryTest.php
│   │   │   ├── KeywordMatchingServiceTest.php
│   │   │   └── ValueObjects/
│   │   │       ├── MoneyTest.php
│   │   │       ├── CashbackRateTest.php
│   │   │       └── MonthlyLimitTest.php
│   │   ├── Transaction/
│   │   │   └── TransactionTest.php
│   │   └── Cashback/
│   │       └── CashbackCalculationServiceTest.php
│   └── Application/
│       └── Transaction/
│           └── RegisterTransactionUseCaseTest.php
│
└── Feature/
    ├── Api/V1/
    │   ├── CategoryApiTest.php
    │   └── TransactionApiTest.php
    └── Contract/
        ├── CategoryRepositoryContractTest.php
        ├── TransactionRepositoryContractTest.php
        └── MonthlySummaryRepositoryContractTest.php

database/
├── migrations/
│   ├── xxxx_create_categories_table.php
│   ├── xxxx_create_category_keywords_table.php
│   ├── xxxx_create_transactions_table.php
│   └── xxxx_create_monthly_summaries_table.php
└── seeders/
    └── DefaultCategoriesSeeder.php   # Seeds: Alimentação, Transporte, Saúde, Lazer, Outros
```

---

## Data Model

### `categories`

| Column | Type | Notes |
|---|---|---|
| `id` | UUID (PK) | |
| `name` | VARCHAR(100) | Unique |
| `type` | ENUM (`default`, `custom`) | |
| `monthly_limit_cents` | BIGINT NULL | NULL = unlimited; stored in cents |
| `cashback_rate` | DECIMAL(5,2) | 0.00–100.00; default 0 |
| `created_at` / `updated_at` | TIMESTAMP | |

### `category_keywords`

| Column | Type | Notes |
|---|---|---|
| `id` | UUID (PK) | |
| `category_id` | UUID (FK → categories) | Cascade delete |
| `keyword` | VARCHAR(100) | Stored lowercase |
| `priority` | SMALLINT | Lower = higher priority |

### `transactions`

| Column | Type | Notes |
|---|---|---|
| `id` | UUID (PK) | |
| `description` | VARCHAR(255) | |
| `amount_cents` | BIGINT | Positive integer; stored in cents |
| `currency` | CHAR(3) | Default `BRL` |
| `transacted_at` | DATE | User-supplied date |
| `category_id` | UUID (FK → categories) | |
| `created_at` / `updated_at` | TIMESTAMP | |

### `monthly_summaries`

| Column | Type | Notes |
|---|---|---|
| `id` | UUID (PK) | |
| `category_id` | UUID (FK → categories) | |
| `year` | SMALLINT | |
| `month` | TINYINT | 1–12 |
| `total_spent_cents` | BIGINT | |
| `cashback_earned_cents` | BIGINT | |
| `status` | ENUM (`within_limit`, `exceeded`) | |
| Unique | `(category_id, year, month)` | |

---

## API Endpoints

All routes prefixed `/api/v1/`. Protected by Sanctum token middleware (except token generation).

### Categories

| Method | Path | Action |
|---|---|---|
| `GET` | `/categories` | List all (default + custom) |
| `POST` | `/categories` | Create custom category |
| `GET` | `/categories/{id}` | Show category + keywords |
| `PUT` | `/categories/{id}` | Update name/limit/cashbackRate/keywords |
| `DELETE` | `/categories/{id}` | Delete custom category only |

### Transactions

| Method | Path | Action |
|---|---|---|
| `GET` | `/transactions` | List with filters (month, year, category) |
| `POST` | `/transactions` | Register + auto-categorize |
| `GET` | `/transactions/{id}` | Show single |
| `PUT` | `/transactions/{id}` | Edit (triggers retroactive recalc) |
| `DELETE` | `/transactions/{id}` | Delete (triggers retroactive recalc) |
| `PATCH` | `/transactions/{id}/recategorize` | Manual re-assign category |

### Summary

| Method | Path | Action |
|---|---|---|
| `GET` | `/summary?year={y}&month={m}` | Monthly summary per category |

---

## Core Business Logic

### Auto-Categorization (`KeywordMatchingService`)

```
For each category keyword rule (ordered by priority ASC):
  If toLower(description) contains toLower(keyword):
    return category
Return "Outros" (fallback)
```

### Cashback Calculation (`CashbackCalculationService`)

```
Input: transaction amount, category limit, category cashback rate, current month total
If limit is NULL:
    cashback = amount × rate
    status = WITHIN_LIMIT
Else:
    remaining = limit − current_month_total
    If remaining <= 0:
        cashback = 0 (already exceeded)
        status = EXCEEDED
    Else if amount <= remaining:
        cashback = amount × rate
        status = WITHIN_LIMIT (or EXCEEDED if total now == limit)
    Else:
        cashback = remaining × rate   # partial cashback only
        status = EXCEEDED
Update monthly summary: total_spent += amount, cashback_earned += cashback
```

### Retroactive Recalculation

On any mutation (edit, delete, recategorize), the use case:
1. Reverses the original transaction's effect on its original month/category summary.
2. Replays all transactions for the affected month(s)/category(ies) in `transacted_at` order to recompute correct `total_spent`, `cashback_earned`, and `status`.

---

## Docker Setup

### `docker-compose.yml` (root of Laravel project)

Services:
- **`app`** — PHP 8.3-FPM + Laravel 12, mounts `./` at `/var/www`
- **`nginx`** — Nginx (or Laravel Octane Franken PHP as alternative) on port 8080
- **`postgres`** — PostgreSQL 16, data volume persisted, port 5432
- **`redis`** — Redis 7, port 6379

```yaml
# docker-compose.yml (outline)
services:
  app:
    build: .
    volumes: [".:/var/www"]
    depends_on: [postgres, redis]
    environment:
      DB_CONNECTION: pgsql
      DB_HOST: postgres
      DB_PORT: 5432
      DB_DATABASE: cashback
      DB_USERNAME: cashback
      DB_PASSWORD: secret
  nginx:
    image: nginx:alpine
    ports: ["8080:80"]
    depends_on: [app]
  postgres:
    image: postgres:16-alpine
    environment:
      POSTGRES_DB: cashback
      POSTGRES_USER: cashback
      POSTGRES_PASSWORD: secret
    volumes: ["pgdata:/var/lib/postgresql/data"]
  redis:
    image: redis:7-alpine
volumes:
  pgdata:
```

### `Dockerfile`

- Base: `php:8.3-fpm-alpine`
- Install: `pdo_pgsql`, `pcntl`, `bcmath`, `redis` extension
- Install Composer, copy source, `composer install --no-dev` for production
- Dev override: `docker-compose.override.yml` mounts source and runs `composer install` with dev deps

---

## Implementation Phases

### Phase 0 — Project Bootstrap
1. Create Laravel 12 project (inside `cashback/` or as the main project root — to be confirmed)
2. Add Docker (`Dockerfile`, `docker-compose.yml`, `docker-compose.override.yml`, `nginx.conf`)
3. Configure `.env` for PostgreSQL
4. Install dev dependencies: PHPUnit 11, Mockery, PHPStan, Pint
5. Configure `phpstan.neon` (level 8), `pint.json` (PSR-12)
6. Establish base directory: `app/Domain/`, `app/Application/`, `app/Infrastructure/`, `app/Providers/`
7. Create `DomainServiceProvider.php` and register in `bootstrap/providers.php`

### Phase 1 — Domain Layer
1. Value Objects: `Money`, `TransactionDate`, `CategoryType`, `CategoryStatus`, `CashbackRate`, `MonthlyLimit`
2. Domain Entities: `Category`, `Transaction`, `MonthlySummary`
3. Repository Interfaces: `CategoryRepositoryInterface`, `TransactionRepositoryInterface`, `MonthlySummaryRepositoryInterface`
4. Domain Services: `KeywordMatchingService`, `CashbackCalculationService`
5. Domain Events: `CategoryCreated`, `CategoryLimitExceeded`, `TransactionRegistered`, etc.
6. **Unit tests for all of the above** (100% Domain coverage)

### Phase 2 — Infrastructure Layer
1. Migrations: `categories`, `category_keywords`, `transactions`, `monthly_summaries`
2. Eloquent Models: `CategoryModel`, `CategoryKeywordModel`, `TransactionModel`
3. Mappers: Domain Entity ↔ Eloquent Model (bidirectional)
4. Repository Implementations: `EloquentCategoryRepository`, `EloquentTransactionRepository`, `EloquentMonthlySummaryRepository`
5. `DefaultCategoriesSeeder` → seeds 5 default categories with common keywords
6. **Contract tests** for each repository implementation

### Phase 3 — Application Layer
1. DTOs for each use case input/output
2. Use Cases:
   - `RegisterTransactionUseCase` (auto-categorize + cashback compute + summary update)
   - `UpdateTransactionUseCase` (retroactive recalc)
   - `DeleteTransactionUseCase` (retroactive recalc)
   - `RecategorizeTransactionUseCase` (retroactive recalc on both categories)
   - `CreateCategoryUseCase`, `UpdateCategoryUseCase`, `DeleteCategoryUseCase`, `ListCategoriesUseCase`
   - `ListTransactionsUseCase`, `GetMonthlySummaryUseCase`
3. Bind interfaces to implementations in `DomainServiceProvider`

### Phase 4 — Presentation Layer
1. API Routes (`routes/api.php`): all `/api/v1/` endpoints
2. Form Requests (validation)
3. Controllers (thin — delegate to Use Cases)
4. JSON Resources (response formatting)
5. **Feature / HTTP tests** for all endpoints (happy path + validation + error scenarios)

### Phase 5 — Quality Gates
1. Run `./vendor/bin/phpstan analyse`
2. Run `./vendor/bin/pint --test`
3. Run `php artisan test --coverage --min=80`
4. Run `composer audit`
5. Fix any findings

---

## Verification Plan

### Automated Tests

All tests run inside Docker or locally against a test PostgreSQL database (configured via `.env.testing`).

#### Unit Tests — Domain Layer
```bash
# From project root (or inside Docker: docker compose exec app ...)
php artisan test tests/Unit --testdox
```
Covers:
- `Money` VO: immutability, arithmetic, validation (no negative values)
- `CashbackRate` VO: bounds (0–100), rejection of invalid values
- `MonthlyLimit` VO: nullable, Money wrapping
- `Category` entity: invariants, keyword management, status transitions
- `Transaction` entity: field validation, date assignment
- `KeywordMatchingService`: priority ordering, case-insensitivity, no-match fallback
- `CashbackCalculationService`: within limit, partial (boundary crossing), fully exceeded, no limit, no rate

#### Contract Tests — Repository Layer
```bash
php artisan test tests/Feature/Contract --testdox
```
Verifies Eloquent implementations satisfy every method signature and behaviour defined in the repository interfaces.

#### Feature / HTTP Tests
```bash
php artisan test tests/Feature/Api --testdox
```
Covers all API endpoints:
- `POST /api/v1/transactions` — auto-categorization, successful registration
- `PUT /api/v1/transactions/{id}` — field update + cashback recalculation
- `DELETE /api/v1/transactions/{id}` — deletion + summary revert
- `PATCH /api/v1/transactions/{id}/recategorize` — re-assignment + dual-category recalc
- `POST /api/v1/categories` — custom category creation
- `PUT /api/v1/categories/{id}` — update limit/rate
- `DELETE /api/v1/categories/{id}` — custom only; 403 for defaults
- `GET /api/v1/summary?year=2026&month=2` — correct totals, statuses

#### Full Suite with Coverage Gate
```bash
php artisan test --coverage --min=80
```

#### Static Analysis
```bash
./vendor/bin/phpstan analyse --memory-limit=512M
```

#### Code Style
```bash
./vendor/bin/pint --test
```

#### Security Audit
```bash
composer audit
```

### Docker End-to-End Smoke Test

```bash
# Start the full stack
docker compose up -d

# Wait for postgres to be ready, then run migrations + seeder
docker compose exec app php artisan migrate --seed

# Verify HTTP 200 from the API
curl -f http://localhost:8080/api/v1/categories

# Run the full test suite inside the container
docker compose exec app php artisan test --coverage --min=80
```

### Manual Verification Scenarios

1. **Register a transaction and verify auto-categorization**
   - `POST /api/v1/transactions` with `{"description": "Almoço no restaurante", "amount": 4500, "currency": "BRL", "transacted_at": "2026-02-24"}`
   - Expected: response includes `category.name = "Alimentação"` (keyword "restaurante" or "almoço" must be in seed data)

2. **Exceed a monthly limit and verify status flip**
   - Set limit of R$100.00 (10000 cents) and cashback 5% on Alimentação via `PUT /api/v1/categories/{id}`
   - Register two transactions of R$60.00 each
   - `GET /api/v1/summary?year=2026&month=2` → `status = "exceeded"`, `cashback_earned` only reflects first R$100.00

3. **Delete a limit-crossing transaction and verify revert**
   - Continue from step 2; delete the second transaction
   - Summary should show `status = "within_limit"` and recalculated cashback

4. **Re-categorize a transaction**
   - `PATCH /api/v1/transactions/{id}/recategorize` with `{"category_id": "<other-id>"}`
   - Both categories' summaries must update accordingly

5. **Attempt to delete a default category**
   - `DELETE /api/v1/categories/{default-category-id}` → must return HTTP 422/403

---

## Complexity Tracking

| Decision | Justification |
|---|---|
| Monthly summaries stored as computed rows | Avoids full re-aggregation on every read; only recomputed on mutations |
| Retroactive recalculation via replay | Simplest correct approach for a single-user system; avoids event sourcing overhead |
| Money stored as integer cents | Avoids floating-point precision errors in cashback calculations |
| UUID primary keys | Avoids sequential ID leakage; compatible with distributed future |
