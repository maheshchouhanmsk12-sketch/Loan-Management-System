# Loan Management System (LMS) - Cloud Deployment

A robust, web-based Loan Management System upgraded for **PHP 8.2+**, containerized with **Docker & Apache**, and configured for high-availability cloud database connection with **Aiven Cloud MySQL (SSL)** and 1-click deployment on **Render**.

---

## 🚀 Live App & Deployment Details

- **GitHub Repository**: [maheshchouhanmsk12-sketch/Loan-Management-System](https://github.com/maheshchouhanmsk12-sketch/Loan-Management-System)
- **Render Web Service Name**: `loan-management-system`
- **Render Deployment Blueprint**: `render.yaml`
- **1-Click Database Setup & Health Check**: `/db_setup.php`

---

## 🔐 Default Admin Credentials

| Role | Username | Password | Access Level |
| :--- | :--- | :--- | :--- |
| **System Administrator** | `admin` | `admin123` | Full Admin Access |

---

## 🗄️ Aiven Cloud MySQL Database Configuration

The application is pre-configured to communicate with Aiven Cloud MySQL using mandatory TLS/SSL encryption (`MYSQLI_CLIENT_SSL`).

### Environment Variables

| Variable | Description | Value / Recommended Setting |
| :--- | :--- | :--- |
| `DB_SERVER` | Aiven MySQL Hostname | `mysql-1a85412b-maheshchouhanmsk12-c513.a.aivencloud.com` |
| `DB_PORT` | Custom 5-digit Port | `26284` |
| `DB_USERNAME` | Master Database User | `avnadmin` |
| `DB_PASSWORD` | Database User Password | Set in Render Dashboard / Secret Manager |
| `DB_NAME` | Project Database Name | `loan_db` |
| `DB_SSL` | Enforce SSL Encryption | `true` |
| `PORT` | Render Web Port | `10000` |
| `BASE_URL` | Application Public URL | `https://<your-render-subdomain>.onrender.com` |

---

## 🛠️ Key Architectural & PHP 8.x Upgrades

1. **PHP 8.2 Compatibility**:
   - Class properties dynamically declared and safely initialized in constructor (`__construct`).
   - Null-safe query result checks using `if ($qry && $qry instanceof mysqli_result)` across all CRUD modules.
   - Fixed typo `$thi->db` to `$this->db` in `admin_class.php`.
   - Added missing `delete_user()` method to `Action` class.
   - Cleaned session handling: `session_start()` and header redirects called before HTML output.

2. **Aiven Cloud Strict Primary Key & SSL Compliance**:
   - Enforced `MYSQLI_CLIENT_SSL` via `mysqli_init()` and `mysqli_ssl_set()`.
   - Added automatic execution of `SET SESSION sql_require_primary_key = 0;` so legacy table schemas and modifications run without errors.

3. **Render Reverse Proxy & Internal Port 10000 Fix**:
   - Render dynamically sets `$PORT` (typically 10000). The container's Apache startup script (`start.sh`) binds Apache directly to `${PORT}`.
   - Configured `UseCanonicalName Off` and `UseCanonicalPhysicalPort Off` to prevent Apache from appending internal `:10000` port to reverse proxy redirects.
   - Configured smart `.htaccess` rewrite rules to strip any internal port numbers from URLs and preserve `https://` routing.

4. **1-Click Visual Installer (`db_setup.php`)**:
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
