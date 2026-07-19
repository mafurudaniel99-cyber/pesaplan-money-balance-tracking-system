# SmartExpense Tracker & Balance Manager

An enterprise-grade financial ledger web application designed for rigorous daily expense tracking, liquid balance calculation, and financial data security.

##  Core Capabilities
A production-ready solution that empowers users with real-time balance calculations, granular expenditure categorizations, and automated threshold alerts. Built with a clean architecture to handle high-fidelity numerical data transactions seamlessly.

---

##  Tech Stack & Technologies
- **Backend:** PHP (Modular, Object-Oriented Architecture)
- **Frontend:** HTML5, CSS3 (Custom properties, Flexbox/Grid Layouts), JavaScript (ES6+ Asynchronous Fetch API)
- **Database:** MySQL (Relational transactional storage with index optimization)

---

##  Enterprise System Modules

| Module Name | Architectural Scope & Professional Features |
| :--- | :--- |
| **1. Auth & Identity** | Secure authentication using `password_hash()`, strict session verification tokens, stateless protected routing, and user preference initialization. |
| **2. Ledger & Core Wallet** | An immutable transaction database ledger tracking core deposits, real-time balance state snapshots, and precise mathematical operations. |
| **3. Expense Tracking Engine** | Dynamic entry logs with structured payload schemas (amount, timestamp, category). Includes active threshold monitoring for automated daily spending ceiling alerts. |
| **4. Analytics & BI Reports** | Comprehensive visual data reporting using optimized rendering. Provides dynamic balance forecasting, category spending breakdown, and month-over-month trendlines. |
| **5. Data Portability Layer** | Export utilities allowing users to cleanly download cryptographic data records, structured financial ledgers, and analytics reports in standard **PDF** or tabular **CSV/Excel** formats. |

---
##<?php
// Core System Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'secure_root');
define('DB_PASS', 'your_secure_password_hash');
define('DB_NAME', 'expense_tracker');

// Global Application Constants
define('APP_CURRENCY', 'TZS');
define('DAILY_SPENDING_CAP', 50000.00); 

------
├── config/             # Database connection configurations and system constants
├── modules/            # Isolated app components (Auth, Wallet, Expenses, Export)
├── public/             # Assets (Custom CSS styles, Vanilla JS scripts, Images)
│   ├── css/
│   ├── js/
│   └── index.php       # Application main entry routing point
└── database.sql        # Optimized production database schema dump

## System Configuration & Local Setup

Deploy the application locally by executing the following environment configuration sequence:

```bash
# Clone the repository and navigate to the root directory
git clone https://github.com/mafurudaniel99-cyber/safespend-digital-wallet-and-balance-tracking-system.git && cd safespend-digital-wallet-and-balance-tracking-system

# Import the standard database schema export file into your local DBMS
mysql -u root -p database_name < safespend.sql