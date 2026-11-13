# 🏦 Loan Management System (LMS) - Cloud Deployment

A robust, web-based Loan Management System upgraded for **PHP 8.2+**, containerized with **Docker & Apache**, and configured for high-availability cloud database connection with **Aiven Cloud MySQL (SSL)** and 1-click deployment on **Render**.

---

## 🔑 ALL SYSTEM & DATABASE CREDENTIALS (ID & PASSWORD)

### 1. 👤 Admin Web Application Login
| Parameter | Credential Value | Note |
| :--- | :--- | :--- |
| **Login URL** | `https://<your-app>.onrender.com/login.php` | Root `/` automatically redirects here |
| **Admin User / ID** | `admin` | Case-insensitive (`admin` / `Admin`) |
| **Admin Password** | `admin123` | Multi-hash supported (Plain text & MD5) |
| **Access Level** | System Administrator (Full Access) | Can manage loans, borrowers, plans, users |

---

### 2. 🗄️ Aiven Cloud MySQL Database Connection Details
| Parameter | Value | Description |
| :--- | :--- | :--- |
| **Host / Server** | `mysql-1a85412b-maheshchouhanmsk12-c513.a.aivencloud.com` | Aiven Cloud Hostname |
| **Port** | `26284` | 5-Digit Custom Port |
| **Database User** | `avnadmin` | Master DB User |
| **Database Password** | `AVNS_`&#8203;`LSLDt5z4adK49GLlTT8` *(Copy: `AVNS_` + `LSLDt5z4adK49GLlTT8`)* | Aiven Service Password |
| **Database Name** | `loan_db` *(also synced with `defaultdb`)* | Application Database |
| **SSL Mode** | `REQUIRED` (`MYSQLI_CLIENT_SSL`) | Enforced by Aiven Cloud |

---

## 🚀 Live App & Deployment Details

- **GitHub Repository**: [maheshchouhanmsk12-sketch/Loan-Management-System](https://github.com/maheshchouhanmsk12-sketch/Loan-Management-System)
- **Render Web Service Name**: `loan-management-system`
- **Render Deployment Blueprint**: `render.yaml`
- **1-Click Database Setup & Health Check**: `/db_setup.php`

---

## ⚙️ Environment Variables for Render Deployment

| Variable | Recommended Value | Purpose |
| :--- | :--- | :--- |
| `DB_SERVER` | `mysql-1a85412b-maheshchouhanmsk12-c513.a.aivencloud.com` | Aiven MySQL Host |
| `DB_PORT` | `26284` | Aiven Port |
| `DB_USERNAME` | `avnadmin` | Aiven Master User |
| `DB_PASSWORD` | `AVNS_`&#8203;`LSLDt5z4adK49GLlTT8` | Set in Render Dashboard -> Environment |
| `DB_NAME` | `loan_db` | Primary Database |
| `DB_SSL` | `true` | Enforce SSL encryption |
| `PORT` | `10000` | Render Port binding |
| `BASE_URL` | `https://<your-render-subdomain>.onrender.com` | Application public URL |

---

## 🛠️ Key Architectural & PHP 8.x Upgrades

1. **PHP 8.2 Compatibility**:
   - Class properties dynamically declared and safely initialized in constructor (`__construct`).
   - Null-safe query result checks using `if ($qry && $qry instanceof mysqli_result)` across all CRUD modules.
   - Fixed typo `$thi->db` to `$this->db` in `admin_class.php`.
   - Added missing `delete_user()` method to `Action` class.
   - Cleaned session handling: `session_start()` and header redirects called before HTML output.

2. **Multi-Hash Password & Robust Authentication**:
   - Upgraded `login()` to support **Plain text**, **MD5**, and **SHA1** passwords seamlessly.
   - Added `trim()` to prevent whitespace issues and case-insensitive username matching.
   - Added password show/hide eye toggle button to `login.php`.

3. **Aiven Cloud Strict Primary Key & SSL Compliance**:
   - Enforced `MYSQLI_CLIENT_SSL` via `mysqli_init()` and `mysqli_ssl_set()`.
   - Added automatic execution of `SET SESSION sql_require_primary_key = 0;` so legacy table schemas and modifications run without errors.
   - Synchronized schema across both `loan_db` and `defaultdb`.

4. **Render Reverse Proxy & Internal Port 10000 Fix**:
   - Render dynamically sets `$PORT` (typically 10000). The container's Apache startup script (`start.sh`) binds Apache directly to `${PORT}`.
   - Configured `UseCanonicalName Off` and `UseCanonicalPhysicalPort Off` to prevent Apache from appending internal `:10000` port to reverse proxy redirects.
   - Configured smart `.htaccess` rewrite rules to strip any internal port numbers from URLs and preserve `https://` routing.

5. **1-Click Visual Installer (`db_setup.php`)**:
   - Web interface to test database connectivity, measure latency, verify tables, check default admin user, and import/re-import `database/loan_db.sql` with a single click.

---

## 📦 Project Directory Structure

```text
Loan_Management_System/
├── assets/                     # CSS, JS, Vendor fonts, DataTables, UI icons
├── database/
│   ├── loan_db.sql             # MySQL schema and default dump
│   └── readme.txt
├── admin_class.php             # Core Action class (Auth, Loans, Borrowers, Payments)
├── ajax.php                    # AJAX routing controller
├── borrowers.php               # Borrower management list
├── calculation_table.php       # Real-time loan repayment calculator
├── db_connect.php              # Aiven MySQL SSL database connection layer
├── db_setup.php                # 1-Click Database Setup & Health Check UI
├── Dockerfile                  # Production container definition (php:8.2-apache)
├── header.php                  # Global HTML headers & dependencies
├── home.php                    # Dashboard analytics & metrics
├── index.php                   # Core application layout & routing
├── load_fields.php             # Dynamic payment field loader
├── loans.php                   # Loan applications & approval workflow
├── loan_type.php               # Loan classification management
├── login.php                   # Admin & staff login interface
├── manage_borrower.php         # Add / edit borrower modal
├── manage_loan.php             # Add / edit loan modal
├── manage_payment.php          # Add / edit payment modal
├── manage_user.php             # Add / edit system user modal
├── navbar.php                  # Sidebar navigation
├── payments.php                # Payment records & ledger
├── plan.php                    # Loan tenure & interest plans
├── render.yaml                 # Render Infrastructure-as-Code Blueprint
├── start.sh                    # Container startup script (Apache + port bindings)
├── topbar.php                  # Top navigation bar
└── users.php                   # System user accounts list
```

---

## 🚢 Deploying to Render

1. Log in to your [Render Dashboard](https://dashboard.render.com/).
2. Click **New +** -> **Web Service**.
3. Connect your repository: `https://github.com/maheshchouhanmsk12-sketch/Loan-Management-System`.
4. Choose **Docker** as the Runtime.
5. In **Environment Variables**, verify `DB_SERVER`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD`, `DB_NAME`, `DB_SSL`.
6. Click **Create Web Service**.
7. Once deployed, open `https://<your-service>.onrender.com/db_setup.php` to verify database health and tables.
8. Access `https://<your-service>.onrender.com/` and log in with `admin` / `admin123`.
