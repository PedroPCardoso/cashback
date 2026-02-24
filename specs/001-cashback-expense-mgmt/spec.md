# Feature Specification: Cashback Expense Management System

**Feature Branch**: `001-cashback-expense-mgmt`  
**Created**: 2026-02-24  
**Status**: Draft  
**Input**: User description: "Sistema de gestão de gastos com cashback. O sistema deve: 1. Receber transações contendo: descrição, valor e data. 2. Categorizar automaticamente com base em palavras-chave. 3. Permitir que o usuário defina um teto mensal por categoria. 4. Calcular cashback por categoria com percentuais configuráveis. 5. O cashback só é acumulado se a categoria ainda estiver dentro do limite mensal. 6. Quando o limite for ultrapassado, o status da categoria muda para 'Excedido' e o cashback deixa de acumular."

---

## Clarifications

### Session 2026-02-24

- Q: Are categories pre-defined by the system or can the user create/edit custom ones? → A: Hybrid — system provides default categories; user can also create new custom categories.
- Q: Can the user manually re-assign a transaction to a different category after auto-categorization? → A: Yes — user can re-categorize a transaction; system recalculates cashback and monthly totals for both the original and new category retroactively.
- Q: Is accumulated cashback informational only, or can users redeem/withdraw it? → A: Informational only — cashback is displayed as an accrued balance but cannot be redeemed or withdrawn within this system.
- Q: Can users edit or delete already-registered transactions? → A: Yes — users can edit description, value, or date of any transaction and can delete transactions; the system recalculates cashback and monthly totals for the affected categories retroactively.
- Q: Are data (transactions, categories, settings) persisted between sessions? → A: Yes — all data is permanently stored and survives closing and reopening the system.

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Register and Auto-Categorize a Transaction (Priority: P1)

A user registers a new expense transaction by providing a description, a monetary value, and a date. The system automatically assigns the transaction to the most appropriate category by matching keywords in the description against pre-configured category rules. The user can then see the categorized transaction in the transaction list.

**Why this priority**: Auto-categorization is the backbone of the system — without it, no cashback calculation or limit tracking is possible. It represents the entry point for all data.

**Independent Test**: Can be fully tested by submitting a single transaction and verifying the category assigned matches the keyword rules, delivering the core intake flow independently.

**Acceptance Scenarios**:

1. **Given** keyword rules are configured (e.g., "Supermercado" → Grocery), **When** a transaction with description "Compra no Supermercado Extra" and value R$150.00 is submitted, **Then** the transaction is saved and categorized as "Grocery".
2. **Given** no keyword rule matches the description, **When** a transaction is submitted with description "Presente de aniversário", **Then** the transaction is categorized as "Other" (default fallback category).
3. **Given** the user submits a transaction, **When** required fields (description, value, date) are missing or invalid, **Then** the system rejects the submission with a clear error message identifying the missing fields.

---

### User Story 2 - Configure Monthly Limits and Cashback Rates per Category (Priority: P2)

A user opens the category settings and sets a monthly spending ceiling and a cashback percentage for each category. These settings are persisted and applied to all subsequent transactions in the current and future months.

**Why this priority**: Limits and cashback rates are the rules engine. Without them, the system cannot enforce the "Excedido" status or compute cashback, but basic transaction intake (P1) still works independently.

**Independent Test**: Can be tested by setting a limit and cashback rate, then verifying the saved values are returned correctly from the settings without submitting any transactions.

**Acceptance Scenarios**:

1. **Given** the user is on the category settings screen, **When** they set a monthly limit of R$500.00 and a cashback rate of 3% for "Grocery", **Then** the settings are saved and confirmed to the user.
2. **Given** the user has previously saved settings, **When** they open the settings again, **Then** the previously saved limit and cashback rate are displayed for editing.
3. **Given** the user enters an invalid value (e.g., negative limit or cashback rate above 100%), **When** they attempt to save, **Then** the system rejects the input with a descriptive validation message.

---

### User Story 3 - View Cashback Earned per Category Within Monthly Limit (Priority: P3)

After one or more transactions are recorded, the user can view a monthly summary showing the total spent, the cashback accumulated, and the status (within limit or "Excedido") for each category.

**Why this priority**: This is the primary output that motivates usage of the system. It depends on P1 (transactions) and P2 (rules) to produce meaningful data.

**Independent Test**: Can be tested using pre-seeded transactions and category settings to verify that the cashback calculation and status flags are correct in the summary view.

**Acceptance Scenarios**:

1. **Given** the grocery category has a R$500.00 limit, a 3% cashback rate, and R$300.00 of spending has been recorded, **When** the user views the monthly summary, **Then** they see: spent = R$300.00, cashback = R$9.00, status = "Within Limit".
2. **Given** the grocery category has been spending totalling R$520.00 (exceeding the R$500.00 limit), **When** the user views the monthly summary, **Then** they see: status = "Excedido" and cashback accumulated only for the R$500.00 within the limit (i.e., R$15.00, not beyond).
3. **Given** a new calendar month begins, **When** the user views the monthly summary, **Then** all category spending resets to zero while limits and cashback rates remain unchanged.

---

### User Story 4 - Cashback Stops Accumulating After Limit Is Exceeded (Priority: P4)

When a transaction causes a category's cumulative monthly spend to exceed its configured ceiling, the system immediately marks that category as "Excedido" and stops computing cashback for any further spending in that category for the rest of the month.

**Why this priority**: This is the core business rule differentiating this product. It depends on P1-P3 and enforces the cashback accumulation boundary.

**Independent Test**: Can be tested by submitting transactions that progressively exceed a category limit and verifying that cashback stops accruing at the exact moment the limit is crossed and the status changes.

**Acceptance Scenarios**:

1. **Given** grocery limit is R$200.00 at 5% cashback and R$180.00 has been spent, **When** a R$50.00 transaction is added, **Then** cashback is earned only on R$20.00 (the portion within the limit), totalling R$1.00 additional cashback, and category status becomes "Excedido".
2. **Given** a category status is "Excedido", **When** another transaction in that category is added, **Then** no cashback is computed for that transaction.
3. **Given** a category status is "Excedido", **When** the next calendar month starts, **Then** category status resets to "Within Limit" and cashback begins accumulating again.

---

### Edge Cases

- What happens when a transaction description matches keywords from multiple categories? The system should assign the first matching rule (by order of priority) or the most specific match.
- What happens if a category has no limit configured? The system should treat spending in that category as unlimited, accumulating cashback without restriction.
- What happens if a category has no cashback rate configured? No cashback is computed for transactions in that category.
- How does the system handle a transaction submitted with a past date from a different calendar month? The transaction should be evaluated against that month's accumulated spending, not the current month's.
- What happens when the user re-categorizes a transaction and the new category is already in "Excedido" status? The transaction value is added to the new category's total but no cashback is earned from it; cashback previously computed (if any) in the original category is reversed.
- What happens when the user edits a transaction's date and moves it to a different calendar month? The system removes it from the original month's totals and adds it to the new month's totals, recalculating cashback status in both months.
- What happens when a user deletes a transaction that was the one that caused a category to reach "Excedido"? The category status is recalculated: if revised total falls below the limit, status reverts to "Within Limit" and cashback is recomputed.

---

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST accept transactions containing a description (text), a value (monetary amount, positive), and a date.
- **FR-002**: The system MUST automatically assign each transaction to a category based on keyword matching rules configured for each category.
- **FR-003**: When no keyword rule matches a transaction description, the system MUST assign the transaction to a default "Other" fallback category.
- **FR-004**: Users MUST be able to configure a monthly spending ceiling (limit) per category.
- **FR-005**: Users MUST be able to configure a cashback percentage per category.
- **FR-006**: The system MUST calculate cashback for each transaction based on the category's configured cashback rate, applied only to the portion of spending that falls within the monthly limit.
- **FR-007**: The system MUST track cumulative monthly spending per category, resetting totals at the start of each calendar month.
- **FR-008**: When cumulative monthly spending in a category reaches or exceeds its configured limit, the system MUST change that category's status to "Excedido".
- **FR-009**: Once a category's status is "Excedido", the system MUST stop accumulating cashback for any further transactions in that category for the rest of the month.
- **FR-010**: The system MUST display a monthly summary per category showing: total spent, cashback earned, and current status (within limit or "Excedido").
- **FR-011**: The system MUST reset category status and monthly spending totals at the start of each new calendar month, preserving limit and cashback rate configurations.
- **FR-012**: Transactions submitted with a past date MUST be evaluated against the spending accumulated in the month of that past date's calendar month.
- **FR-013**: The system MUST ship with a set of pre-defined default categories (e.g., Alimentação, Transporte, Saúde, Lazer, Outros).
- **FR-014**: Users MUST be able to create new custom categories, each with a name and at least one keyword rule.
- **FR-015**: Users MUST be able to edit and delete custom categories they have created; pre-defined default categories may not be deleted.
- **FR-016**: Users MUST be able to manually re-assign any transaction to a different category; upon re-assignment, the system MUST retroactively recalculate the monthly cashback earned and total spending for both the original category and the new category.
- **FR-017**: Users MUST be able to edit any transaction's description, value, or date; upon saving edits, the system MUST retroactively recalculate the monthly cashback earned and total spending for the affected category (and for both months if the date change spans a calendar month boundary).
- **FR-018**: Users MUST be able to delete any transaction; upon deletion, the system MUST retroactively recalculate the monthly cashback earned and total spending for the category that transaction belonged to.

### Key Entities

- **Transaction**: Represents an individual expense. Key attributes: description (text), value (positive monetary amount), date. Belongs to exactly one category.
- **Category**: Groups transactions by type. Key attributes: name, type (default / custom), list of matching keywords, monthly spending limit (optional), cashback percentage (optional), current-month status (within limit / Excedido). Default categories cannot be deleted; custom categories can be fully managed by the user.
- **CategoryKeywordRule**: Associates keywords to a category to enable auto-categorization. Key attributes: keyword (text), category (reference), priority/order.
- **MonthlySpendingSummary**: Aggregated view per category per calendar month. Key attributes: category, month/year, total spent, total cashback earned, status.

---

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users can register a transaction and see it correctly categorized in under 3 seconds.
- **SC-002**: 90% of transactions with descriptions matching configured keywords are automatically categorized without manual intervention.
- **SC-003**: Users can view an accurate monthly summary per category — total spend, cashback earned, and status — reflecting all transactions for the current month.
- **SC-004**: Cashback stops accruing in a category at the exact moment the monthly limit is exceeded, with no under- or over-computation.
- **SC-005**: Users can successfully configure monthly limits and cashback rates for all categories without requiring assistance.
- **SC-006**: Monthly totals reset correctly at the turn of each calendar month without data loss of historical transactions or settings.

---

## Assumptions

- Each transaction is assigned to exactly **one** category; multiple-category attribution is out of scope.
- Keyword matching is **case-insensitive** and based on substring matching within the transaction description.
- When multiple keyword rules match a description, the **highest-priority rule** (by configured order) wins.
- Categories without a configured limit are treated as **unlimited** — cashback accumulates freely.
- Categories without a configured cashback rate earn **0% cashback** (no cashback computed).
- "Month" is defined as a **calendar month** (January, February, etc.), not a rolling 30-day window.
- The system is designed for a **single user** per instance; multi-user access control is out of scope.
- Historical transactions are preserved across month resets; only **monthly aggregation** is reset.
- Cashback redemption, withdrawal, or transfer to external systems is **out of scope**; the cashback balance is a read-only informational metric.
- All data (transactions, categories, keyword rules, and monthly settings) MUST be **permanently persisted** and survive closing and reopening the system; no data is lost between sessions.
