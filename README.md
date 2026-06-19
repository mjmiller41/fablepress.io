# FablePress.io: A Simplified, Content-First CMS

FablePress.io is a simplified, elegant, and distraction-free Content Management System (CMS) designed specifically to eliminate the bloat, complexity, and security headaches of WordPress. It is tailored for independent writers, creators, and small brands who care about typography, clean presentation, and seamless content workflows.

The system is built on a **PHP 8.x + SQLite/MySQL (Option A)** architecture, optimized for immediate, high-performance deployment on a **Hostinger Business Shared Hosting** plan.

---

## 🚀 Getting Started (Local Development)

FablePress.io requires **zero initial database configuration** when using SQLite mode. The database is initialized and seeded automatically on your first visit.

### 1. Launch the PHP Server
Navigate to the FablePress directory in your terminal and start PHP's built-in web server:
```bash
cd /home/michael/Code/Projects/FablePress
php -S localhost:8000 router.php
```

### 2. Access the Application
*   **Public Landing Page**: [http://localhost:8000](http://localhost:8000)
*   **Admin Dashboard**: [http://localhost:8000/admin](http://localhost:8000/admin)

---

## 🔑 Default Credentials & Role Matrix

To test the Role-Based Access Control (RBAC) system (Screen C), log in using one of the pre-seeded users:

| Username | Password | Access Level | Description |
| :--- | :--- | :--- | :--- |
| **`developer`** | `developer123` | **High** (Developer) | Full system access. Can modify themes, toggle permissions, manage users, and deploy updates. |
| **`manager`** | `manager123` | **Medium** (Content Manager) | Full content access. Can create, edit, delete, and publish any page/story, and manage the navigation menu. |
| **`writer`** | `writer123` | **Limited** (Contributor) | Restricted access. Can draft articles and upload media, but cannot publish directly (must save as draft). |

---

## 🛠️ Project Structure

The project has been organized according to standard modular practices:

```
FablePress/
├── admin/                 # Admin Dashboard Pages
│   ├── admin.css          # Admin Premium CSS Stylesheet
│   ├── footer.php         # Admin Shared Footer layout
│   ├── header.php         # Admin Shared Sidebar Layout & RBAC enforcement
│   ├── index.php          # Admin Main Dashboard Stats
│   ├── login.php          # Secure Admin Session Authentication
│   ├── logout.php         # Session Destroyer
│   ├── media.php          # Drag-and-drop Media Uploader & Library
│   ├── navigation.php     # Interactive Navigation Menu Hierarchy Editor
│   ├── roles.php          # RBAC Roles Configuration Dashboard
│   ├── stories.php        # Stories List & Metadata View
│   └── story-edit.php     # Centered, Distraction-Free Page/Story Editor
├── uploads/               # Directory where uploaded images/files are stored
├── config.php             # Core Database connection, schema migrations, and helpers
├── index.php              # Public site router and dynamic templates renderer
├── style.css              # Premium Public stylesheet (Serif typography & Cream tone)
└── README.md              # Project documentation
```

---

## 💾 Switching to MySQL (Hostinger Deployment)

By default, the application runs on **SQLite** to make local testing effortless. To host this on a production database (e.g. Hostinger hPanel MySQL):

1.  Create a MySQL Database in your Hostinger Control Panel.
2.  Open [config.php](file:///home/michael/Code/Projects/FablePress/config.php).
3.  Change `DB_MODE` to `mysql` (Line 5):
    ```php
    define('DB_MODE', 'mysql');
    ```
4.  Enter your database details (Lines 8-11):
    ```php
    define('DB_HOST', 'your-hostinger-mysql-host');
    define('DB_NAME', 'your-database-name');
    define('DB_USER', 'your-database-user');
    define('DB_PASS', 'your-database-password');
    ```
5.  On the first page refresh, FablePress will automatically create the tables and seed the database.
