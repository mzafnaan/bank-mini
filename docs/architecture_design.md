# Architecture Design

## 1. Overview

MiniBank is designed using a layered architecture to separate presentation, business logic, and data access. This approach improves maintainability, readability, and scalability while keeping the implementation suitable for the UKK project.

The system consists of two clients:

- Web Application (Laravel Blade)
- Mobile Application (Flutter)

Both clients communicate with the same Laravel application. Business logic is centralized in the Service Layer to ensure consistent behavior across all platforms.

---

# 2. System Architecture

```text
                    +----------------------+
                    |     Web Browser      |
                    |    (Laravel Blade)   |
                    +----------+-----------+
                               |
                               |
                    +----------v-----------+
                    |      Controllers     |
                    +----------+-----------+
                               |
                    +----------v-----------+
                    |      Services        |
                    | (Business Logic)     |
                    +----------+-----------+
                               |
                    +----------v-----------+
                    |   Eloquent Models    |
                    +----------+-----------+
                               |
                    +----------v-----------+
                    |       MySQL          |
                    +----------------------+

                    +----------------------+
                    | Flutter Mobile App   |
                    +----------+-----------+
                               |
                           REST API
                               |
                    +----------v-----------+
                    |   API Controllers    |
                    +----------+-----------+
                               |
                               |
                    (Uses the same Service Layer)
```

---

# 3. Technology Stack

| Layer | Technology |
|--------|------------|
| Frontend Web | Laravel Blade |
| Frontend Mobile | Flutter |
| Backend | Laravel 12 |
| API | REST API |
| Database | MySQL |
| ORM | Eloquent ORM |
| Authentication (Web) | Laravel Session |
| Authentication (Mobile) | Laravel Sanctum |
| Authorization | Middleware |
| QR Code | Generated Dynamically |
| Logging | Laravel Log |

---

# 4. Layered Architecture

## Presentation Layer

Responsible for displaying information and receiving user input.

Components:

- Laravel Blade
- Flutter Mobile

Responsibilities:

- Display data
- Receive user input
- Call Controller
- Display validation messages
- Display transaction results

---

## Controller Layer

Controllers receive requests from users and coordinate application flow.

Responsibilities:

- Receive Request
- Call Service
- Return Response
- Redirect User

Controllers should not contain business logic.

Example:

- AuthController
- CustomerController
- BankAccountController
- TransactionController
- DailyReportController

---

## Service Layer

The Service Layer contains all business logic.

Responsibilities:

- Deposit Processing
- Withdrawal Processing
- Daily Report Processing
- Journal Entry Creation
- Balance Calculation
- PIN Verification

Services can access multiple models within a single business process.

Example:

```
TransactionService

├── BankAccount
├── Transaction
└── JournalEntry
```

---

## Data Access Layer

Uses Laravel Eloquent ORM.

Responsibilities:

- CRUD Operation
- Relationship Management
- Query Builder
- Database Transaction

Models:

- User
- Customer
- CustomerAccount
- BankAccount
- Transaction
- JournalEntry
- DailyReport

---

# 5. Request Flow

```text
Client
   │
   ▼
Middleware
   │
   ▼
Form Request Validation
   │
   ▼
Controller
   │
   ▼
Service
   │
   ▼
Eloquent Model
   │
   ▼
MySQL
```

---

# 6. Middleware

The system uses middleware for authentication and authorization.

Authentication Middleware

- Verify login session
- Prevent unauthorized access

Role Middleware

- Administrator
- Teller
- Supervisor
- Customer

Middleware ensures that each user can only access features based on their assigned role.

---

# 7. Form Request Validation

Laravel Form Request is used to validate all incoming data before reaching the Controller.

Examples:

- Required Field
- Numeric Validation
- Positive Amount
- Existing Account Validation
- Password Validation

Invalid requests will automatically return validation errors without executing business logic.

---

# 8. Database Transaction

Financial transactions must be executed using database transactions.

Example:

```text
BEGIN TRANSACTION

Create Transaction

Update Balance

Create Debit Journal

Create Credit Journal

COMMIT
```

If any process fails:

```text
ROLLBACK
```

This guarantees data consistency and prevents partial transactions.

---

# 9. Error Handling

Business processes use Try-Catch with Database Transaction.

```text
Try

↓

Database Transaction

↓

Commit

↓

Catch Exception

↓

Rollback

↓

Return Error Response
```

This mechanism protects financial data integrity.

---

# 10. Authentication

The system provides two client applications:

- Web Application (Laravel Blade)
- Mobile Application (Flutter)

Each client uses an authentication mechanism appropriate for its platform.

---

## Web Authentication

The web application uses **Laravel Session Authentication**.

Supported users:

- Administrator
- Teller
- Supervisor
- Customer

The web application provides complete system functionality, including administrative operations and customer services.

---

## Mobile Authentication

The mobile application uses **Laravel Sanctum**.

Supported users:

- Customer

The mobile application is provided as an additional client to improve customer accessibility and convenience.

Customer features available on mobile include:

- View Profile
- View Account Information
- View Account Balance
- View Transaction History
- Display QR Code
- Change Password
- Change PIN
- PIN Verification for Withdrawal Requests

---

## Password Security

All passwords are securely stored using Laravel's hashing mechanism (bcrypt).

Customer PINs are also hashed before being stored in the database to ensure confidentiality and prevent unauthorized access.

---

## Authentication Flow

### Web

```text
User

↓

Login Form

↓

Laravel Session Authentication

↓

Dashboard Based on Role
```

### Mobile

```text
Customer

↓

Login API

↓

Laravel Sanctum

↓

Access Mobile Features
```

# 11. Authorization

Authorization is implemented using Role Middleware.

Roles:

- Administrator
- Teller
- Supervisor
- Customer

Each role has different permissions based on business requirements.

---

# 12. PIN Verification Architecture

Withdrawal transactions require customer PIN verification.

Process:

```text
Teller

↓

Create Withdrawal Request

↓

Status = WAITING_PIN

↓

Customer Mobile

↓

Polling API

↓

Customer Input PIN

↓

Server Verification

↓

Status = APPROVED / REJECTED

↓

Teller Receives Result
```

The system uses Polling API instead of WebSocket to simplify implementation while maintaining real-time verification.

---

# 13. QR Code Architecture

Each bank account has a QR Code generated from its account number.

Example:

```
REK-20260001
```

QR Code is **not stored as an image file**.

Instead, it is generated dynamically whenever needed.

Benefits:

- No duplicate file storage
- Always synchronized with account number
- Easier maintenance

---

# 14. Logging

Application errors are automatically recorded using Laravel Logging.

Location:

```
storage/logs/laravel.log
```

Examples:

- Database Exception
- Validation Exception
- Unexpected Error
- System Failure

Business transaction history is stored separately in transaction records and journal entries.

---

# 15. Folder Structure

```text
app
│
├── Http
│   ├── Controllers
│   ├── Middleware
│   └── Requests
│
├── Models
│
├── Services
│
└── Providers
```

### Controllers

Handle incoming requests and responses.

### Middleware

Authentication and authorization.

### Requests

Input validation using Laravel Form Request.

### Models

Represent database entities and relationships.

### Services

Contain business logic.

### Providers

Laravel application service providers.

---

# 16. Deployment Architecture

```text
                +------------------+
                |   Web Browser    |
                +---------+--------+
                          |
                Laravel Blade
                          |
+-------------------------+------------------------+
|                     Laravel                      |
|--------------------------------------------------|
| Controllers                                     |
| Middleware                                      |
| Services                                        |
| Models                                          |
+-------------------------+------------------------+
                          |
                       MySQL
                          |
+-------------------------+------------------------+
|                Flutter Mobile                   |
|                 REST API                        |
+-------------------------------------------------+
```

---

# 17. Architecture Principles

The system follows these principles:

- Separation of Concerns
- Layered Architecture
- Single Responsibility Principle
- Centralized Business Logic
- Transaction Consistency
- Role-Based Access Control
- Secure Authentication
- Dynamic QR Code Generation
- Database Transaction & Rollback
- Input Validation Before Business Logic

These principles ensure the application remains maintainable, secure, and suitable for financial transaction processing.