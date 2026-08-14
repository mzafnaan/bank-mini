# UI/UX Design

## 1. Design Overview

MiniBank uses a modern dashboard interface with a clean, simple, and responsive layout. The design prioritizes readability, ease of navigation, and efficient access to banking features.

The interface is inspired by a modern admin dashboard with minimal visual distractions and focuses on functionality suitable for banking operations.

---

# 2. Design Principles

The user interface follows these principles:

- Clean and Minimalist
- Easy Navigation
- Consistent Components
- Responsive Layout
- Fast User Interaction
- Clear Information Hierarchy

---

# 3. Color Palette

| Element | Color |
|----------|--------|
| Primary | Blue (#2563EB) |
| Secondary | White (#FFFFFF) |
| Background | Gray 50 (#F9FAFB) |
| Border | Gray 200 (#E5E7EB) |
| Success | Green |
| Warning | Orange |
| Danger | Red |

---

# 4. Typography

Font Family

- Inter
- Poppins

Font Weight

- Heading : Bold
- Content : Regular
- Button : Medium

---

# 5. Layout Structure

```text
--------------------------------------------------------
 Sidebar |               Top Navbar                    |
         |---------------------------------------------|
         |                                             |
         |                                             |
         |                Main Content                 |
         |                                             |
         |                                             |
         |---------------------------------------------|
```

The application consists of three main sections:

- Sidebar Navigation
- Top Navigation Bar
- Content Area

---

# 6. Sidebar Navigation

Sidebar appears on every authenticated page.

Administrator

- Dashboard
- Internal Users
- Customers
- Bank Accounts
- Daily Reports
- Audit Journal
- Profile
- Logout

---

Teller

- Dashboard
- Customers
- Deposit
- Withdrawal
- Transactions
- Profile
- Logout

---

Supervisor

- Dashboard
- Daily Reports
- Approve Reports
- Audit Journal
- Profile
- Logout

---

Customer

- Dashboard
- My Account
- QR Code
- Transaction History
- Change Password
- Change PIN
- Profile
- Logout

---

# 7. Top Navigation

The top navigation contains:

- Search Box
- Notification Icon
- User Profile
- Dropdown Menu

---

# 8. Dashboard

Each role has a different dashboard.

---

Administrator Dashboard

Summary Cards

- Total Customers
- Total Bank Accounts
- Total Internal Users
- Today's Transactions

Recent Activities

Latest Transactions Table

Quick Actions

- Create Customer
- Create Internal User
- View Reports

---

Teller Dashboard

Summary Cards

- Today's Deposits
- Today's Withdrawals
- Total Transactions
- Current Cash Balance

Recent Transactions

Quick Actions

- Deposit
- Withdrawal
- Search Customer

---

Supervisor Dashboard

Summary Cards

- Pending Reports
- Approved Reports
- Today's Transactions

Report Approval Table

Audit Journal Preview

---

Customer Dashboard

Summary Cards

- Current Balance
- Account Number

QR Code Card

Recent Transactions

Quick Actions

- View QR Code
- Change Password
- Change PIN

---

# 9. Table Design

All data management pages use the same table style.

Features:

- Search
- Pagination
- Sorting
- Status Badge
- Action Button

Example

| Customer | Account | Balance | Status | Action |

Action Menu

- View
- Edit
- Block
- Activate

(Delete is intentionally restricted to maintain ledger integrity.)

---

# 10. Form Design

Every form uses the same layout.

Example

Customer Information

- Student ID (NIS)
- Full Name
- Class

Account Information

- Account Number
- Initial Balance

Login Account

- Username
- Temporary Password

Buttons

- Save
- Cancel

---

# 11. Transaction Page

Deposit Form

Fields

- Account Number
- Customer Name (Auto)
- Amount

Buttons

- Process Deposit

---

Withdrawal Form

Fields

- Account Number
- Customer Name (Auto)
- Current Balance
- Amount

Button

- Request PIN Verification

Status

- Waiting PIN
- Approved
- Rejected

---

# 12. Daily Report

Summary Information

- Opening Cash
- Total Deposit
- Total Withdrawal
- Closing Cash

Report Status

- Draft
- Approved

Buttons

- Submit Report
- Approve Report

---

# 13. Journal Page

Table

| Transaction ID | Account Code | Debit | Credit | Date |

Search

Pagination

Filter by Date

---

# 14. Customer Mobile Screen

Dashboard

- Balance Card
- QR Code
- Recent Transactions

Menu

- Transaction History
- Change Password
- Change PIN
- Profile

---

# 15. Status Indicators

Green

- Active
- Approved
- Success

Orange

- Waiting
- Pending

Red

- Blocked
- Rejected

Gray

- Inactive

---

# 16. Responsive Design

Desktop

Sidebar always visible.

Tablet

Sidebar can be collapsed.

Mobile

Sidebar changes into a drawer menu.

Cards automatically stack vertically.

Tables support horizontal scrolling.

---

# 17. Component Consistency

The application uses reusable UI components.

Components

- Card
- Button
- Badge
- Modal
- Table
- Pagination
- Form Input
- Search Box
- Dropdown
- Alert
- Confirmation Dialog

---

# 18. User Experience

The interface is designed to minimize user interaction steps.

Examples

Deposit

Search Customer

↓

Input Amount

↓

Confirm

↓

Success

Withdrawal

Search Customer

↓

Input Amount

↓

Customer PIN Verification

↓

Transaction Success

Customer

Open Dashboard

↓

View Balance

↓

Show QR Code

↓

Done

---

# 19. Design Goals

The MiniBank interface is designed to:

- Provide a clean and modern banking experience.
- Minimize user interaction steps.
- Maintain consistent navigation across all modules.
- Ensure responsive usability on desktop and mobile devices.
- Present financial information clearly and securely.