# Project Structure & Development Guidelines

## 1. Purpose

This document defines the project structure, coding standards, security practices, and development guidelines for MiniBank.

The objectives are:

- Maintain clean and readable code.
- Prevent duplicated logic.
- Improve maintainability.
- Increase scalability.
- Enforce consistent coding standards.
- Improve application security.

---

# 2. Project Structure

```
app
├── Http
│   ├── Controllers
│   ├── Middleware
│   └── Requests
│
├── Models
│
├── Services
│
├── Providers
│
└── Helpers (Optional)
```

```
resources
└── views
    ├── auth
    ├── dashboard
    ├── customers
    ├── bank_accounts
    ├── transactions
    ├── reports
    ├── journals
    ├── layouts
    └── components
```

---

# 3. Layer Responsibilities

## Controller

Controller is responsible for:

- Receiving HTTP Request.
- Calling Service.
- Returning Response.
- Redirecting User.

Controller must NOT:

- Calculate balance.
- Create journal entries.
- Execute business rules.
- Write complex database queries.

Controllers should remain thin.

---

## Service

The Service Layer contains all business logic.

Examples:

- Deposit
- Withdrawal
- PIN Verification
- Daily Report
- Journal Entry
- Balance Calculation

Services may use multiple models within a single business process.

---

## Model

Models represent database entities.

Responsibilities:

- Relationships
- Scopes
- Simple Accessors
- Simple Mutators

Models should NOT contain business logic.

---

## Request

All incoming data must be validated using Laravel Form Request.

Validation must never be written directly inside Controllers.

Example:

- StoreCustomerRequest
- UpdateCustomerRequest
- DepositRequest
- WithdrawalRequest

---

## Middleware

Middleware is responsible for:

- Authentication
- Authorization
- Session Validation
- Role Verification

Business logic must never be placed inside Middleware.

---

# 4. Naming Convention

## Models

Use Singular PascalCase.

Example:

- User
- Customer
- BankAccount
- Transaction
- JournalEntry

---

## Controllers

Use PascalCase ending with Controller.

Example:

- CustomerController
- TransactionController

---

## Services

Use PascalCase ending with Service.

Example:

- CustomerService
- TransactionService

---

## Requests

Use PascalCase.

Example:

- StoreCustomerRequest
- UpdateCustomerRequest
- DepositRequest

---

## Database Tables

Use snake_case plural.

Examples:

- users
- customers
- bank_accounts
- transactions
- journal_entries

---

## Columns

Use snake_case.

Example:

```
account_number

current_balance

created_at
```

---

# 5. Blade Folder Structure

```
views

auth/

dashboard/

customers/

bank_accounts/

transactions/

reports/

journals/

layouts/

components/
```

Each module must only contain views related to that feature.

---

# 6. Reusable Blade Components

Frequently used UI elements must be implemented as Blade Components.

Examples:

```
Button

Card

Input

Select

Table

Pagination

Badge

Modal

Search

Summary Card

QR Card
```

Avoid duplicating HTML across multiple pages.

---

# 7. Layout Components

Shared layouts should be reused.

Examples:

- App Layout
- Guest Layout
- Authentication Layout

Shared UI:

- Sidebar
- Navbar
- Footer

---

# 8. Validation Rules

Every request must be validated before reaching business logic.

Use Laravel Form Request.

Validation examples:

- Required
- Numeric
- Exists
- Unique
- Min
- Max

Controllers should never perform manual validation.

---

# 9. Security Guidelines

## Password

Passwords must always be hashed.

```
Hash::make()
```

Passwords must never be stored as plain text.

---

## PIN

Customer PIN must also be hashed.

PIN must never be returned through API responses.

---

## SQL Injection

Always use:

- Eloquent ORM
- Query Builder

Never concatenate SQL manually.

Incorrect:

```
SELECT * FROM users WHERE username = '$username'
```

Correct:

```
User::where('username', $username)->first();
```

---

## CSRF Protection

All web forms must include CSRF protection.

```
@csrf
```

---

## Authorization

Every protected route must use middleware.

Example:

```
auth

role:administrator

role:teller

role:customer
```

Users must never access unauthorized pages.

---

## Mass Assignment

All models must define:

```
$fillable
```

Avoid:

```
$guarded = [];
```

unless absolutely necessary.

---

## Environment Variables

Sensitive configuration must be stored in:

```
.env
```

Never hardcode:

- Database Password
- API Keys
- SMTP Password
- Secret Keys

---

# 10. Database Transaction

Financial operations must always use:

```
DB::transaction()
```

Example:

Deposit

↓

Update Balance

↓

Create Transaction

↓

Create Journal Entry

↓

Commit

If any step fails:

Rollback

---

# 11. Error Handling

Services should use Try-Catch for critical operations.

```
try

↓

Business Logic

↓

Commit

catch

↓

Rollback

↓

Return Error
```

---

# 12. Logging

Unexpected errors should be logged using Laravel Log.

Examples:

```
Log::info()

Log::warning()

Log::error()
```

Business history must be stored in database tables, not only in log files.

---

# 13. Coding Principles

Follow these principles:

- Single Responsibility Principle (SRP)
- Don't Repeat Yourself (DRY)
- Separation of Concerns (SoC)
- Keep It Simple (KISS)
- Readable Code
- Reusable Components

---

# 14. Code Formatting

Use consistent formatting.

Indentation:

- 4 spaces

Braces:

```
if (...) {

}
```

Method names:

camelCase

Class names:

PascalCase

Constants:

UPPER_CASE

---

# 15. Performance Guidelines

Use eager loading when retrieving related data.

Example:

```
Customer::with('bankAccount')->get();
```

Avoid N+1 Query problems.

Always paginate large datasets.

```
paginate(10)
```

Never retrieve thousands of records using:

```
get()
```

unless necessary.

---

# 16. UI Consistency

The application should use reusable components.

Examples:

```
<x-button>

<x-card>

<x-table>

<x-modal>

<x-input>

<x-search>

<x-summary-card>
```

Maintain consistent spacing, colors, typography, and component behavior across all pages.

---

# 17. Git Commit Convention

Use meaningful commit messages.

Examples:

```
feat: add customer management

feat: implement deposit service

fix: prevent negative balance

refactor: extract transaction service

docs: update architecture design

style: improve dashboard layout
```

---

# 18. Development Principles

Every new feature should:

- Follow the Layered Architecture.
- Reuse existing components whenever possible.
- Keep Controllers thin.
- Place business logic inside Services.
- Validate all user input.
- Protect routes with Middleware.
- Use reusable Blade Components.
- Maintain clean, readable, and secure code.

The project should prioritize maintainability, consistency, security, and scalability while remaining simple enough for educational purposes.